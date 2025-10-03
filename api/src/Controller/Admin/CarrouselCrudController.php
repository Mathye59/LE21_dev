<?php

// src/Controller/Admin/CarrouselCrudController.php
namespace App\Controller\Admin;

use App\Entity\Carrousel;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;

class CarrouselCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carrousel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Carrousel')
            ->setEntityLabelInSingular('Image du carrousel')
            ->setDefaultSort(['media.filename' => 'ASC']) // tri par nom
            ->setPageTitle(Crud::PAGE_INDEX, 'Carrousel')
            ->setDefaultSort(['position' => 'ASC'])
            ->setPaginatorPageSize(15)  ;        // <= 15 par page
            
    }

    public function configureFields(string $pageName): iterable
    {
        // Miniature (INDEX uniquement)
        yield ImageField::new('mediaFilename', 'Aperçu')
            ->setBasePath('/uploads/media')
            ->onlyOnIndex()
            ->setSortable(false);

        // Nom de fichier (INDEX uniquement)
        yield TextField::new('mediaFilename', 'Média')
            ->onlyOnIndex()
            ->setSortable(true);

        // (si tu veux éditer le titre)
        yield TextField::new('title', 'Titre')->hideOnIndex();

        // Actif/inactif
        yield BooleanField::new('isActive', 'Actif');

        // Position visible uniquement en index (tu as déjà les boutons Monter/Descendre)
        yield IntegerField::new('position', 'Position')->onlyOnIndex();

       
    }

    public function configureActions(Actions $actions): Actions
    {
        $toggle = Action::new('toggle', 'Activer/Désactiver', 'fa fa-toggle-on')
            ->linkToCrudAction('toggle');

        return $actions
            ->add(Crud::PAGE_INDEX, $toggle)
            ->disable(Action::NEW)     // on crée auto à partir des médias
            ->disable(Action::DELETE); // garde l’unicité 1–1 (ou enlève si tu veux supprimer)
    }

    public function toggle(EntityManagerInterface $em): Response
    {
        /** @var Carrousel $item */
        $item = $this->getContext()->getEntity()->getInstance();
        $item->setIsActive(!$item->isActive());
        $em->flush();

        return $this->redirect($this->getContext()->getReferrer());
    }
}
