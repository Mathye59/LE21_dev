<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Categorie::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie')
            ->setEntityLabelInPlural('Catégories')
            ->setPageTitle(Crud::PAGE_INDEX, 'Catégories')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle catégorie')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la catégorie')
            ->setSearchFields(['nom'])
            ->setPaginatorPageSize(15);
    }

    public function configureFields(string $pageName): iterable
    {
        // Nom de la catégorie
        yield TextField::new('nom', 'Nom')
            ->setMaxLength(50);

        // Compteur des flashes liés (uniquement en index)
        yield IntegerField::new('flashesCount', 'Nb de flashes')
            ->onlyOnIndex()
            ->formatValue(function ($value, Categorie $cat) {
                return $cat->getFlashes()->count();
            });
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Personnalisation des libellés/icônes sans ré-ajouter les actions existantes
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) =>
                $a->setLabel('Nouvelle catégorie')->setIcon('fa fa-plus')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn(Action $a) =>
                $a->setLabel('Enregistrer et ajouter une autre')->setIcon('fa fa-plus-circle')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn(Action $a) =>
                $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn(Action $a) =>
                $a->setLabel('Enregistrer et continuer')->setIcon('fa fa-save')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn(Action $a) =>
                $a->setLabel('Enregistrer et revenir')->setIcon('fa fa-check')
            );
    }
}
