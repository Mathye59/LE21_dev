<?php

namespace App\Controller\Admin;

use App\Entity\Entreprise;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

// Pour le champ d’upload Vich (image unique)
use Vich\UploaderBundle\Form\Type\VichImageType;

class EntrepriseCrudController extends AbstractCrudController
{
    /**
     * Adapter si tu veux un autre nombre d’items / page.
     */
    private const PAGE_SIZE = 15;

    /**
     * C'est bien cette entité que l'on pilote ici.
     */
    public static function getEntityFqcn(): string
    {
        return Entreprise::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Entreprise')
            ->setEntityLabelInPlural('Entreprise')
            ->setPageTitle(Crud::PAGE_INDEX, 'Fiche entreprise')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer la fiche entreprise')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la fiche entreprise')
            ->setPaginatorPageSize(self::PAGE_SIZE)
            ->setSearchFields(['nom', 'adresse', 'facebook', 'instagram', 'horaires']);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Confort d’usage sur la page d’édition
        return $actions
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn(Action $a) =>
                $a->setLabel('Enregistrer et continuer'))
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }

    public function configureFields(string $pageName): iterable
    {
        // === Champs texte de base ===
        yield TextField::new('nom', 'Nom')->setMaxLength(50);
        yield TextareaField::new('adresse', 'Adresse')->setNumOfRows(2);
        yield TextField::new('horaires', 'Horaires')
            ->setHelp('Ex. "Mar > Sam : 10h–18h"')
            ->hideOnIndex();

        // === Réseaux ===
        yield UrlField::new('facebook', 'Facebook')->hideOnIndex()->setRequired(false);
        yield UrlField::new('instagram', 'Instagram')->hideOnIndex()->setRequired(false);

        // === Upload du logo (Vich) ===
        // - "logoFile" n’est PAS stocké en BDD : c’est un champ technique pour l’upload
        // - Vich remplira "logoName" (le nom de fichier) automatiquement
        yield TextField::new('logoFile', 'Logo')
            ->setFormType(VichImageType::class)
            ->onlyOnForms()
            ->setRequired(false)
            ->setHelp('Téléversez un fichier image pour remplacer le logo (optionnel).')
            ->setFormTypeOptions([
                'allow_delete'   => true,   // bouton "supprimer le fichier" (efface la valeur en BDD)
                'download_uri'   => false,  // pas de lien de téléchargement
                'asset_helper'   => true,   // aide pour la résolution des chemins publics
            ]);

        // Aperçu du logo (à partir de logoName) : seulement sur index/détail
        // ⚠️ Ajuste le chemin public en fonction de ta config Vich (voir plus bas)
        yield ImageField::new('logoName', 'Aperçu')
            ->setBasePath('/uploads/company_logos')
            ->onlyOnIndex();
    }
}
