<?php

namespace App\Controller\Admin;

use App\Entity\ArticleAccueil;
use App\Entity\Media;
// (facultatif) pointer le CRUD Media pour bénéficier de l'autocomplete/selection EA :
use App\Controller\Admin\MediaCrudController;

use App\Security\ArticleHtmlSanitizer; // service interne pour nettoyer le HTML (XSS, balises autorisées)
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile; // type fort pour l'upload inline
use Symfony\Component\HttpFoundation\RequestStack;       // pour accéder au payload du form EA (mapped=false)
use Vich\UploaderBundle\Form\Type\VichImageType;        // (non utilisé ici mais utile si un jour tu mappes un champ Vich)

 /**
  * CRUD EasyAdmin pour les "Articles d’accueil".
  * - Texte riche (HTML) sécurisés côté serveur.
  * - Image : 2 modes complémentaires
  *     (1) Associer un Media déjà existant (AssociationField)
  *     (2) Uploader un nouveau fichier "inline" non mappé (FileType, mapped=false)
  * - Si un fichier inline est fourni, on crée un Media via Vich et on l’associe en priorité.
  *
  * Notes d’architecture :
  * - On ne touche pas au MediaCrudController ni au Carrousel ici.
  * - On garde l’upload "inline" strictement non mappé → on lit la Request et on persiste un Media à la main.
  * - On sanitize *toujours* le HTML avant persist/update (défense en profondeur).
  */
class ArticleAccueilCrudController extends AbstractCrudController
{
    /**
     * DI :
     *  - ArticleHtmlSanitizer : service maison pour whitelist/strip HTML dangereux.
     *  - RequestStack : nécessaire pour récupérer les champs non mappés (uploadedFileInline / uploadedAltInline).
     */
    public function __construct(
        private ArticleHtmlSanitizer $sanitizer,
        private RequestStack $requestStack,
    ) {}

    /** FQCN de l’entité gérée par ce CRUD. */
    public static function getEntityFqcn(): string
    {
        return ArticleAccueil::class;
    }

    /**
     * Réglages globaux EA : labels, titres, pagination.
     * [UX] Titres clairs pour l’admin ; pagination 15 par défaut.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Articles d’accueil')
            ->setEntityLabelInSingular('Article d’accueil')
            ->setPageTitle(Crud::PAGE_INDEX, 'Articles d’accueil')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvel article')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l’article')
            ->setPaginatorPageSize(15);
            // ->setEntityPermission('ROLE_ADMIN') // (option) restreindre par rôle
    }

    /**
     * Déclaration des champs (par page).
     * - Texte : titre + contenu (éditeur riche)
     * - Media : AssociationField (existant) + upload inline (non mappé)
     * - Index : vignette de l'image associée (lecture seule)
     */
    public function configureFields(string $pageName): iterable
    {
        // --- Titre ------------------------------------------------------------
        yield TextField::new('titre', 'Titre')
            ->setMaxLength(100)  // [UX] limite UI ; prévoir aussi Assert\Length côté entité
            ->setRequired(true);

        // --- Contenu HTML -----------------------------------------------------
        yield TextEditorField::new('contenu', 'Contenu')
            ->setNumOfRows(18)
            ->setHelp('Mise en forme : titres, gras, listes, liens… (le HTML est sécurisé côté serveur).');
            // [SECURITY] tu nettoies de toute façon côté persist/update via $sanitizer

        // --- MEDIA : 2 modes complémentaires ---------------------------------
        // (1) Sélection d’un Media existant (autocomplete + filename comme libellé)
        yield AssociationField::new('media', 'Visuel (existant)')
            ->setCrudController(MediaCrudController::class) // renvoie sur le CRUD Media si besoin
            ->setFormTypeOption('choice_label', 'filename') // ou __toString()
            ->setRequired(false);

        // (2) Upload inline "non mappé" : on lira le fichier depuis la Request
        // [UPLOAD] mapped=false => n’appartient PAS à l’entité ArticleAccueil
        //           donc on le récupère manuellement, on crée un Media Vich et on relie.
        yield Field::new('uploadedFileInline', 'Nouveau visuel (upload inline)')
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'required' => false,
                'mapped'   => false, // IMPORTANT : sinon EA/Symfony tenterait d’hydrater ArticleAccueil::$uploadedFileInline
                // (option) ajouter ici des contraintes File (mime/jpeg/png/webp, maxSize) si tu veux filtrer dès le form
            ])
            ->onlyOnForms();

        // Alt text pour l’upload inline (non mappé) → on lira la valeur et poussera sur Media::alt si présent
        yield Field::new('uploadedAltInline', 'Texte alternatif (inline)')
            ->setFormTypeOptions([
                'mapped'   => false, // non relié à ArticleAccueil
                'required' => false,
            ])
            ->onlyOnForms();

        // --- Index : aperçu (lecture seule) ----------------------------------
        yield ImageField::new('media.filename', 'Aperçu')
            ->setBasePath('/uploads/media') // [ASSET] doit correspondre à uri_prefix du mapping Vich "media"
            ->onlyOnIndex();
    }

    /**
     * PERSIST (création) :
     * - Sanitize le HTML
     * - Si un fichier inline est présent → créer un Media via Vich et l’associer en priorité
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleAccueil) {
            // [SECURITY] Nettoyage du HTML avant d’écrire en DB
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            // [UPLOAD] Traite l’upload inline si fourni (et écrase l’association existante)
            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        // Laisse EA faire le persist standard (incl. relations déjà set)
        parent::persistEntity($em, $entityInstance);
    }

    /**
     * UPDATE (édition) :
     * - Sanitize le HTML
     * - Si fichier inline → même logique que persist : création d’un Media prioritaire
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleAccueil) {
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        parent::updateEntity($em, $entityInstance);
    }

    /**
     * Applique l’upload inline si présent :
     * - Récupère UploadedFile (mapped=false) depuis la Request EA (le payload est "imbriqué")
     * - Crée un Media, setFile($uploaded) (Vich déplacera le fichier + renseignera filename)
     * - Persiste + flush le Media (pour figer l’id/filename si nécessaire)
     * - Associe le Media nouvellement créé à l’Article (prioritaire sur l’association existante)
     *
     * [IMPORTANT] On n’applique *que si* un fichier a été soumis via "uploadedFileInline".
     */
    private function applyInlineUploadIfAny(ArticleAccueil $article, EntityManagerInterface $em): void
    {
        $req = $this->requestStack->getCurrentRequest();
        if (!$req) {
            return; // [DX] aucun contexte de requête (rare en EA), on sort proprement
        }

        // [UPLOAD] Le champ étant "mapped=false", l’UploadedFile se trouve dans $req->files,
        // potentiellement *imbriqué* (EasyAdmin structure le form en tableaux).
        $uploaded = $this->deepFindUploadedFile($req->files->all(), 'uploadedFileInline');
        if (!$uploaded instanceof UploadedFile) {
            return; // pas d’upload inline => ne rien changer à l’éventuelle association existante
        }

        // Créer un Media et lui pousser le fichier.
        // [UPLOAD] Avec Vich : setFile(UploadedFile) déclenche le déplacement + set filename en postLoad
        $media = new Media();
        $media->setFile($uploaded);

        // Alt inline : si fourni, on le pousse dans Media (si propriété "alt" disponible)
        $alt = $this->deepFindScalar($req->request->all(), 'uploadedAltInline');
        if ($alt !== null && method_exists($media, 'setAlt')) {
            $media->setAlt((string) $alt);
        }

        // Persiste d’abord le Media : utile si des contraintes d’intégrité existent, et pour figer le filename
        $em->persist($media);
        $em->flush();

        // Associer le nouveau Media à l’article (prioritaire sur la sélection existante)
        $article->setMedia($media);
    }

    /**
     * Recherche récursive d’un UploadedFile par clé dans un tableau potentiellement imbriqué.
     * - EA structure le form en plusieurs niveaux ; cette fonction évite d’avoir à "connaître" la forme exacte.
     */
    private function deepFindUploadedFile(mixed $haystack, string $key): ?UploadedFile
    {
        if (is_array($haystack)) {
            foreach ($haystack as $k => $v) {
                if ($k === $key && $v instanceof UploadedFile) {
                    return $v;
                }
                $found = $this->deepFindUploadedFile($v, $key);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    }

    /**
     * Recherche récursive d’une valeur scalaire (string|int|bool|null) par clé.
     * - Utilisée pour récupérer "uploadedAltInline" depuis $req->request (données non-fichiers).
     */
    private function deepFindScalar(mixed $haystack, string $key): mixed
    {
        if (is_array($haystack)) {
            foreach ($haystack as $k => $v) {
                if ($k === $key && (is_scalar($v) || $v === null)) {
                    return $v;
                }
                $found = $this->deepFindScalar($v, $key);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        return null;
    }
}
