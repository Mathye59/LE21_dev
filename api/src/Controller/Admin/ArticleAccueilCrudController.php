<?php

namespace App\Controller\Admin;

use App\Entity\ArticleAccueil;
use App\Entity\Media;
use App\Controller\Admin\MediaCrudController;
use App\Security\ArticleHtmlSanitizer;
use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * CRUD EasyAdmin pour les Articles d’accueil.
 * - Contenu en HTML (éditeur riche) + sanitization serveur.
 * - Média : soit on choisit un Media existant (association), soit on téléverse un nouveau fichier inline (non mappé).
 * - Pagination 15.
 *
 * IMPORTANT :
 * - On NE MODIFIE PAS le MediaCrudController ni la logique Carrousel.
 * - Si un fichier inline est fourni, on crée un Media (Vich) et on l’associe à l’article en priorité.
 */
class ArticleAccueilCrudController extends AbstractCrudController
{
    public function __construct(
        private ArticleHtmlSanitizer $sanitizer,
        private RequestStack $requestStack, // pour récupérer le fichier non mappé
    ) {}

    public static function getEntityFqcn(): string
    {
        return ArticleAccueil::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Articles d’accueil')
            ->setEntityLabelInSingular('Article d’accueil')
            ->setPageTitle(Crud::PAGE_INDEX, 'Articles d’accueil')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvel article')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l’article')
            ->setPaginatorPageSize(15);
    }

    public function configureFields(string $pageName): iterable
    {
        // --- Contenu texte ----------------------------------------------------
        yield TextField::new('titre', 'Titre')
            ->setMaxLength(100)
            ->setRequired(true);

        yield TextEditorField::new('contenu', 'Contenu')
            ->setNumOfRows(18)
            ->setHelp('Mise en forme : titres, gras, listes, liens… (le HTML est sécurisé côté serveur).');

        // --- MEDIA : 2 modes complémentaires ---------------------------------
        // 1) Association existante : on permet de sélectionner un Media déjà présent
        yield AssociationField::new('media', 'Visuel (existant)')
            ->setCrudController(MediaCrudController::class)
            ->setFormTypeOption('choice_label', 'filename') // (ou __toString())
            ->setRequired(false);

        // 2) Upload inline : champ NON MAPPÉ Vich vers Media::file
        //    - S’il reçoit un fichier, on créera un Media dans persist/update.
        yield Field::new('uploadedFileInline', 'Nouveau visuel (upload inline)')
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'required'      => false, // non obligatoire (on peut choisir un existant)
                'mapped'        => false, // IMPORTANT : non mappé à l’entité ArticleAccueil
            ])
            ->onlyOnForms();

        // Alt (non mappé) pour le fichier inline
        yield Field::new('uploadedAltInline', 'Texte alternatif (inline)')
            ->setFormTypeOptions([
                'mapped'   => false, // non mappé : on lira la valeur manuellement
                'required' => false,
            ])
            ->onlyOnForms();

        // --- Index : aperçu ---------------------------------------------------
        yield ImageField::new('media.filename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->onlyOnIndex();
    }

    /**
     * Nettoie le HTML avant enregistrement + gère le cas "upload inline".
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleAccueil) {
            // 1) Sanitize le HTML
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            // 2) Si un fichier inline a été envoyé, on crée un Media et on l’associe
            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        parent::persistEntity($em, $entityInstance);
    }

    /**
     * Nettoie le HTML avant mise à jour + gère le cas "upload inline".
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleAccueil) {
            // 1) Sanitize le HTML
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            // 2) Si un fichier inline a été envoyé, on crée un Media et on l’associe
            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        parent::updateEntity($em, $entityInstance);
    }

    /**
     * Si le formulaire a reçu un fichier via le champ non mappé "uploadedFileInline",
     * on crée un Media, on setFile() (Vich), on persiste le Media et on le rattache à l’article.
     * Si un Media était déjà sélectionné via l’association, il est volontairement écrasé.
     */
    private function applyInlineUploadIfAny(ArticleAccueil $article, EntityManagerInterface $em): void
    {
        $req = $this->requestStack->getCurrentRequest();
        if (!$req) {
            return;
        }

        // On récupère un éventuel UploadedFile sous la clé "uploadedFileInline"
        // Comme le champ est "mapped=false", il apparaît dans $req->files au niveau racine du form EA.
        $uploaded = $this->deepFindUploadedFile($req->files->all(), 'uploadedFileInline');
        if (!$uploaded instanceof UploadedFile) {
            return; // pas d’upload inline => on ne touche pas au média existant/associé
        }

        // Crée un Media Vich et set le fichier
        $media = new Media();
        $media->setFile($uploaded); // Vich déclenchera le déplacement et renseignera filename
        // Si un alt inline a été donné, on l’applique
        $alt = $this->deepFindScalar($req->request->all(), 'uploadedAltInline');
        if ($alt !== null && method_exists($media, 'setAlt')) {
            $media->setAlt((string) $alt);
        }

        // Persiste d'abord le Media (pour qu’il ait un id si tu as des contraintes d’intégrité)
        $em->persist($media);
        $em->flush(); // on flush tôt pour figer le filename si besoin

        // Associe le nouveau média à l’article (prioritaire sur l’AssociationField)
        $article->setMedia($media);
    }

    /** Recherche récursive d’un UploadedFile par clé (utile avec EA qui nest l’array) */
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

    /** Recherche récursive d’une valeur scalaire par clé (pour alt inline) */
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
