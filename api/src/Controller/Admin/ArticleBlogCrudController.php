<?php

namespace App\Controller\Admin;

use App\Entity\ArticleBlog;
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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

/**
 * CRUD EasyAdmin pour les Articles de blog.
 * - Contenu en HTML (éditeur riche) + sanitization serveur.
 * - Média : au choix, sélectionner un existant OU téléverser un nouveau fichier inline.
 * - Auteur (tatoueur) en association, avec autocomplete.
 * - Pagination 15.
 *
 * IMPORTANT :
 * - On ne modifie pas MediaCrudController ni la logique Carrousel existante.
 * - Si un fichier inline est fourni, on crée un Media (Vich gère l’upload via setFile())
 *   puis on associe ce Media à l’article (écrase l’éventuelle sélection existante).
 */
class ArticleBlogCrudController extends AbstractCrudController
{
    public function __construct(
        private ArticleHtmlSanitizer $sanitizer,
        private RequestStack $requestStack, // pour récupérer le fichier non mappé depuis la requête
    ) {}

    public static function getEntityFqcn(): string
    {
        return ArticleBlog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Articles Blog')
            ->setEntityLabelInSingular('Article blog')
            ->setPageTitle(Crud::PAGE_INDEX, 'Articles Blog')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvel article')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l’article')
            ->setPaginatorPageSize(15);
    }

    public function configureFields(string $pageName): iterable
    {
        // --- Titre ------------------------------------------------------------
        yield TextField::new('titre', 'Titre')
            ->setMaxLength(100)
            ->setRequired(true);

        // --- Contenu HTML (éditeur riche) ------------------------------------
        yield TextEditorField::new('contenu', 'Contenu')
            ->setNumOfRows(18)
            ->setHelp('Mise en forme : titres, gras, listes, liens… (le HTML est sécurisé côté serveur).');

        // --- Média : 2 modes --------------------------------------------------
        // 1) Sélection d’un média existant via le CRUD Media (modal EA)
        yield AssociationField::new('media', 'Visuel (existant)')
            ->setCrudController(MediaCrudController::class)
            ->autocomplete()
            ->setRequired(false); // on ne force pas : l’upload inline peut fournir le média

        // 2) Upload inline (non mappé) : un simple FileType (surtout pas VichImageType ici)
        yield Field::new('uploadedFileInline', 'Nouveau visuel (upload inline)')
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'required' => false,
                'mapped'   => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize'   => '25M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Formats autorisés : JPG, PNG, WEBP.',
                    ]),
                ],
            ])
            ->onlyOnForms();

        // Optionnel : alt inline (non mappé) — utilisé seulement si Media expose setAlt()
        yield Field::new('uploadedAltInline', 'Texte alternatif (inline)')
            ->setFormTypeOptions([
                'mapped'   => false,
                'required' => false,
            ])
            ->onlyOnForms();

        // --- Auteur (tatoueur) -----------------------------------------------
        yield AssociationField::new('auteur', 'Tatoueur')
            ->autocomplete();

        // --- Index : aperçu du média -----------------------------------------
        yield ImageField::new('media.filename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->onlyOnIndex();
    }

    /**
     * Sanitize + gestion d’un éventuel upload inline.
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleBlog) {
            // 1) Nettoyage HTML
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            // 2) Si un fichier inline est présent, on crée un Media et on l’associe
            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        parent::persistEntity($em, $entityInstance);
    }

    /**
     * Sanitize + gestion d’un éventuel upload inline.
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleBlog) {
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        parent::updateEntity($em, $entityInstance);
    }

    /**
     * Si le formulaire a reçu un fichier via le champ non mappé "uploadedFileInline",
     * on crée un Media, on setFile() (Vich gère l’upload et le filename), on persiste le Media,
     * puis on remplace l’association media de l’article par ce nouveau Media.
     * Si aucun fichier inline n’est fourni, l’association existante reste inchangée.
     */
    private function applyInlineUploadIfAny(ArticleBlog $article, EntityManagerInterface $em): void
    {
        $req = $this->requestStack->getCurrentRequest();
        if (!$req) {
            return;
        }

        // Récupère l’UploadedFile non mappé (clé = 'uploadedFileInline')
        $uploaded = $this->deepFindUploadedFile($req->files->all(), 'uploadedFileInline');
        if (!$uploaded instanceof UploadedFile) {
            return; // pas d’upload inline → on ne touche pas à l’association existante
        }

        // Crée un nouveau Media et délègue à Vich via setFile()
        $media = new Media();
        $media->setFile($uploaded); // Vich déplacera le fichier et remplira filename

        // Alt inline optionnel (si l’entité Media expose setAlt())
        $alt = $this->deepFindScalar($req->request->all(), 'uploadedAltInline');
        if ($alt !== null && method_exists($media, 'setAlt')) {
            $media->setAlt((string) $alt);
        }

        // Persiste d’abord le Media (fixe l’id et le filename si besoin)
        $em->persist($media);
        $em->flush();

        // Associe ce nouveau Media à l’article (prioritaire sur l’AssociationField)
        $article->setMedia($media);
    }

    /** Recherche récursive d’un UploadedFile par clé (utile à cause de l’imbrication EA) */
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

    /** Recherche récursive d’une valeur scalaire (pour 'uploadedAltInline') */
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
