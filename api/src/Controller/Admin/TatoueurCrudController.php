<?php

namespace App\Controller\Admin;

use App\Entity\Tatoueur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class TatoueurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tatoueur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tatoueur')
            ->setEntityLabelInPlural('Tatoueurs')
            ->setPageTitle(Crud::PAGE_INDEX, 'Tatoueurs')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau tatoueur')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le tatoueur')
            ->setPaginatorPageSize(15)
            // champs utilisables dans la barre de recherche (index)
            ->setSearchFields(['prenom', 'nom', 'pseudo', 'email']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Prénom / Nom
        yield TextField::new('prenom', 'Prénom')
            ->setFormTypeOption('attr.maxlength', 50);

        yield TextField::new('nom', 'Nom')
            ->setFormTypeOption('attr.maxlength', 50);

        // Pseudo (OPTIONNEL)
        yield TextField::new('pseudo', 'Pseudo')
            ->setRequired(false)                                 // nullable en BDD
            ->setFormTypeOption('attr.placeholder', '(optionnel)')
            ->setFormTypeOption('attr.maxlength', 50);

        // Email
        yield EmailField::new('email', 'Email')
            ->setFormTypeOption('attr.maxlength', 255);

        // Entreprise — sélection EXISTANTE uniquement (pas de bouton “+ Nouveau”)
        
        // Assure-toi que Entreprise::__toString() retourne le nom pour un affichage propre.
        yield AssociationField::new('entreprise', 'Entreprise')
            ->autocomplete()
            ->setRequired(true);
    }
}
