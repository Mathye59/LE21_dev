<?php

namespace App\Controller\Admin;

use App\Entity\Flash;
use App\Entity\Categorie;
use App\Entity\Tatoueur;

use App\Controller\Admin\CategorieCrudController;

use Doctrine\ORM\EntityManagerInterface;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

use Symfony\Component\Validator\Constraints\Length;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FlashCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Flash::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Libellés & titres
            ->setEntityLabelInSingular('Flash')
            ->setEntityLabelInPlural('Flashes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Flashes')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau flash')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le flash')
            // Tri par défaut : les plus récents d’abord si updatedAt existe
            ->setDefaultSort(['categorie.nom' => 'ASC', 'tatoueur.nom' => 'ASC', 'statut' => 'ASC'])
            ->setPaginatorPageSize(15);
    }

    public function configureFields(string $pageName): iterable
    {
        $isNew = $pageName === Crud::PAGE_NEW;

        // --- Champs texte simples ------------------------------------------------

        // Optionnel (nullable true dans l’entité)
        yield TextField::new('temps', 'Temps estimé')
            ->setHelp('Exemple: "2h", "3h30"…')
            ->setMaxLength(50);

        // Statut libre (tu pourras passer en ChoiceField si tu normalises les valeurs)
        yield ChoiceField::new('statut', 'Statut')
            ->setChoices([
                'Disponible'   => 'disponible',
                'Réservé'      => 'reserve',
                'Indisponible' => 'indisponible',
            ])
            ->renderAsNativeWidget();

        // --- Associations --------------------------------------------------------

        //  Catégories en multi-sélection + création 
    yield AssociationField::new('categories', 'Catégories')
        ->setCrudController(CategorieCrudController::class)
        ->setFormTypeOptions([
            'by_reference' => false,  
            'multiple' => true,
        ])
        ->autocomplete()              
        ->setRequired(true);

        // Tatoueur : sélection uniquement (pas d’ajout), donc pas de setCrudController().
        yield AssociationField::new('tatoueur', 'Tatoueur')
            ->setFormTypeOption('choice_label', 'nom') 
            ->autocomplete()
            ->setRequired(true);

        // --- Image (Vich) -------------------------------------------------------

        // Champ d’upload Vich : lié à Flash::imageFile (UploadableField)
        yield Field::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions([
                'required' => $isNew,          // requis à la création, optionnel en édition
                'allow_delete' => false,
            ])
            ->onlyOnForms();

        // Aperçu image (index/détail) basé sur le nom de fichier persistant (imageName)
        yield ImageField::new('imageName', 'Aperçu')
            ->setBasePath('/uploads/flashes') 
            ->hideOnForm();

        // (Optionnel) Dernière modif (si tu l’affiches dans la liste)
        yield DateTimeField::new('updatedAt', 'MAJ')
            ->onlyOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        // Juste un peu de cosmétique sur les libellés/icônes
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $a) =>
                $a->setLabel('Nouveau flash')->setIcon('fa fa-plus')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn(Action $a) =>
                $a->setLabel('Enregistrer et ajouter un autre')->setIcon('fa fa-plus-circle')
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
