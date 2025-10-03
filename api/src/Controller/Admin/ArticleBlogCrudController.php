<?php

namespace App\Controller\Admin;

use App\Entity\ArticleBlog;
use App\Controller\Admin\MediaCrudController;
use App\Controller\Admin\TatoueurCrudController;
use App\Security\ArticleHtmlSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArticleBlogCrudController extends AbstractCrudController
{
   public function __construct(private ArticleHtmlSanitizer $sanitizer)
    {
    }

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
        // Titre
        yield TextField::new('titre', 'Titre')
            ->setMaxLength(100)
            ->setRequired(true);

        // Contenu : éditeur riche (stocke du HTML)
        yield TextEditorField::new('contenu', 'Contenu')
            ->setNumOfRows(18)
            ->setHelp('Mise en forme : titres, gras, listes, liens… (le HTML est sécurisé côté serveur).');

        // Média : choisir un existant OU en créer un depuis un sous-formulaire
        // 👉 Remplace "MediaCrudController::class" par le namespace réel si différent.
        yield AssociationField::new('media', 'Visuel')
            ->setCrudController(MediaCrudController::class)
            ->autocomplete()
            ->setRequired(true);

        // (Index) Aperçu du média (basé sur la propriété nested "media.filename")
        yield ImageField::new('media.filename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->onlyOnIndex();
        
            // Auteur : choisir un existant 
       yield AssociationField::new('auteur', 'Tatoueur')
            ->autocomplete() ;                              // champ de sélection avec recherche
            
    }

    /**
     * Nettoie le HTML avant enregistrement.
     */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleBlog) {
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );
        }
        parent::persistEntity($em, $entityInstance);
    }

    /**
     * Nettoie le HTML avant mise à jour.
     */
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof ArticleBlog) {
            $entityInstance->setContenu(
                $this->sanitizer->sanitize((string) $entityInstance->getContenu())
            );
        }
        parent::updateEntity($em, $entityInstance);
    }
}
