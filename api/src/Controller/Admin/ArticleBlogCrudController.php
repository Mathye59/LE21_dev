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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField; // ← ajouté
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class ArticleBlogCrudController extends AbstractCrudController
{
    public function __construct(
        private ArticleHtmlSanitizer $sanitizer,
        private RequestStack $requestStack,
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
        // --- Titre
        yield TextField::new('titre', 'Titre')
            ->setMaxLength(100)
            ->setRequired(true);

        // --- Contenu HTML (éditeur riche)
        yield TextEditorField::new('contenu', 'Contenu')
            ->setNumOfRows(18)
            ->setHelp('Mise en forme : titres, gras, listes, liens… (le HTML est sécurisé côté serveur).');

        // --- Résumé
        // Formulaire : zone de texte (laisser vide pour auto-génération)
        yield TextareaField::new('resume', 'Résumé')
            ->setNumOfRows(4)
            ->setHelp('Court extrait (≈ 220 caractères). Laisser vide pour auto-génération à partir du contenu.')
            ->onlyOnForms();

        // Index : aperçu tronqué
        yield TextField::new('resume', 'Résumé')
            ->onlyOnIndex()
            ->formatValue(function ($value) {
                $plain = strip_tags((string) $value);
                $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return mb_strlen($plain) > 80 ? mb_substr($plain, 0, 80) . '…' : $plain;
            });

        // --- Média existant
        yield AssociationField::new('media', 'Visuel (existant)')
            ->setCrudController(MediaCrudController::class)
            ->autocomplete()
            ->setRequired(false);

        // --- Upload inline
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

        yield Field::new('uploadedAltInline', 'Texte alternatif (inline)')
            ->setFormTypeOptions([
                'mapped'   => false,
                'required' => false,
            ])
            ->onlyOnForms();

        // --- Auteur
        yield AssociationField::new('auteur', 'Tatoueur')
            ->autocomplete();

        // --- Index : aperçu média
        yield ImageField::new('media.filename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->onlyOnIndex();
    }
     /**S'assure qu'il y a une date sinon la crée */
    private function ensureDate(ArticleBlog $article): void
    {
        if (!$article->getDate()) {
            $article->setDate(new \DateTime());
        }
    }
    /** Sanitize + upload inline éventuel + résumé */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleBlog) {
            // contenu
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            // résumé (sanitize + fallback auto)
            $this->sanitizeAndFillResume($entityInstance);
            // date
            $this->ensureDate($entityInstance);
            // upload inline
            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        parent::persistEntity($em, $entityInstance);
    }
   
    /** Sanitize + upload inline éventuel + résumé */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleBlog) {
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );

            $this->sanitizeAndFillResume($entityInstance);
            $this->ensureDate($entityInstance);
            $this->applyInlineUploadIfAny($entityInstance, $em);
        }

        parent::updateEntity($em, $entityInstance);
    }

    /** Nettoie le résumé et le génère depuis le contenu si vide */
    private function sanitizeAndFillResume(ArticleBlog $article): void
    {
        $resume = $article->getResume();

        if ($resume !== null && $resume !== '') {
            // On garde du texte simple
            $plain = strip_tags((string) $resume);
            $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plain = preg_replace('/\s+/u', ' ', trim($plain)) ?? '';
            $article->setResume($plain);
            return;
        }

        // Auto-génération depuis contenu si résumé vide
        $content = (string) $article->getContenu();
        $plain = strip_tags($content);
        $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/u', ' ', trim($plain)) ?? '';

        $max = 220;
        $auto = mb_strlen($plain) > $max ? mb_substr($plain, 0, $max) . '…' : $plain;
        $article->setResume($auto);
    }

    /**
     * Upload inline (inchangé)
     */
    private function applyInlineUploadIfAny(ArticleBlog $article, EntityManagerInterface $em): void
    {
        $req = $this->requestStack->getCurrentRequest();
        if (!$req) return;

        $uploaded = $this->deepFindUploadedFile($req->files->all(), 'uploadedFileInline');
        if (!$uploaded instanceof UploadedFile) return;

        $media = new Media();
        $media->setFile($uploaded);

        $alt = $this->deepFindScalar($req->request->all(), 'uploadedAltInline');
        if ($alt !== null && method_exists($media, 'setAlt')) {
            $media->setAlt((string) $alt);
        }

        $em->persist($media);
        $em->flush();

        $article->setMedia($media);
    }

    private function deepFindUploadedFile(mixed $haystack, string $key): ?UploadedFile
    {
        if (is_array($haystack)) {
            foreach ($haystack as $k => $v) {
                if ($k === $key && $v instanceof UploadedFile) {
                    return $v;
                }
                $found = $this->deepFindUploadedFile($v, $key);
                if ($found) return $found;
            }
        }
        return null;
    }

    private function deepFindScalar(mixed $haystack, string $key): mixed
    {
        if (is_array($haystack)) {
            foreach ($haystack as $k => $v) {
                if ($k === $key && (is_scalar($v) || $v === null)) {
                    return $v;
                }
                $found = $this->deepFindScalar($v, $key);
                if ($found !== null) return $found;
            }
        }
        return null;
    }
}
