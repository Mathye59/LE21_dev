<?php

namespace App\Controller\Admin;

use App\Entity\Entreprise;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

// Champs
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

// Vich for upload
use Vich\UploaderBundle\Form\Type\VichFileType;

class EntrepriseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Entreprise::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Entreprise')
            ->setEntityLabelInSingular('Entreprise')
            ->setPageTitle(Crud::PAGE_INDEX, 'Entreprise')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer l’entreprise')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l’entreprise')
            ->setPaginatorPageSize(15) // pagination par 15
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        // On ne rajoute PAS d’actions déjà existantes (sinon erreur “already exists”)
        return $actions
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn (Action $a) =>
                $a->setLabel('Créer'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $a) =>
                $a->setLabel('Enregistrer et continuer'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $a) =>
                $a->setLabel('Enregistrer et revenir'));
    }

    public function configureFields(string $pageName): iterable
    {
        // --- Identité / Contact
        yield TextField::new('nom', 'Nom')
            ->setMaxLength(50);

        yield TextEditorField::new('adresse', 'Adresse')
            ->setHelp('Adresse postale complète');

        // ⚠️ Ta propriété `telephone` est un float : c’est risqué (perte du 0 initial).
        // Si tu peux, change-la en string en BDD. En attendant, NumberField sans décimales :
        yield TextField::new('telephone', 'Téléphone')
            ->setMaxLength(10)
            ->setHelp('Ex: 03 20 12 34 56 ');

        yield EmailField::new('email', 'Email');

        // --- Réseaux
        yield UrlField::new('facebook', 'Facebook')->hideOnIndex()
            ->setHelp('URL complète (https://...)');
        yield UrlField::new('instagram', 'Instagram')->hideOnIndex()
            ->setHelp('URL complète (https://...)');

        // --- Horaires (affichage comme dans ta maquette)
        yield TextField::new('horairesOuverture', 'Heure d’ouverture')
            ->setHelp('Ex: 10h');
        yield TextField::new('horairesFermeture', 'Heure de fermeture')
            ->setHelp('Ex: 19h');
        yield TextField::new('horairePlus', 'Mention complémentaire')
            ->hideOnIndex()
            ->setHelp('Ex: Sur rendez-vous uniquement via contact');

        // --- Logo
        // Upload (formulaire uniquement)
        yield Field::new('logoFile', 'Logo')
            ->setFormType(VichFileType::class)
            ->onlyOnForms()
            ->setHelp('PNG/JPG • le fichier remplacera l’actuel');

        // Aperçu en liste/detail (basé sur le nom de fichier stocké)
        // Adapte le chemin si ta config Vich est différente
        yield ImageField::new('logoName', 'Aperçu logo')
            ->setBasePath('/uploads/company_logos')
            ->hideOnForm();

    
    }
}
