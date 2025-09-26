<?php

namespace App\Controller\Admin;

use App\Entity\Flash;
use App\Entity\Tatoueur;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

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
            ->setEntityLabelInPlural('Flashes')
            ->setEntityLabelInSingular('Flash')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['temps', 'statut', 'tatoueur.nom', 'tatoueur.email', 'categorie.nom']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
            // ->setPermission(Action::NEW, 'ROLE_ADMIN'); // décommente si besoin
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('categorie'))
            ->add(ChoiceFilter::new('statut')->setChoices([
                'Disponible'   => 'disponible',
                'Réservé'      => 'reserve',
                'Indisponible' => 'indisponible',
            ]))
            ->add(EntityFilter::new('tatoueur'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('temps', 'Durée')->hideOnIndex();

        yield ChoiceField::new('statut', 'Statut')->setChoices([
            'Disponible'   => 'disponible',
            'Réservé'      => 'reserve',
            'Indisponible' => 'indisponible',
        ]);

        yield AssociationField::new('categorie')->setLabel('Catégorie')->autocomplete();
        yield AssociationField::new('tatoueur')->setLabel('Tatoueur')->autocomplete();

        yield Field::new('imageFile')->setFormType(VichImageType::class)->onlyOnForms()->setLabel('Image');
        yield ImageField::new('imageName')->setBasePath('uploads/flashes')->onlyOnIndex()->setLabel('Aperçu');
    }

    /**
     * Restreint la liste aux flashes du tatoueur connecté (sauf admin).
     */
    // public function createIndexQueryBuilder(
    //     SearchDto $searchDto,
    //     EntityRepository $repo,
    //     FieldCollection $fields,
    //     FilterCollection $filters
    // ) {
    //     $qb = $repo->createQueryBuilder($searchDto, $fields, $filters);

    //     if ($this->isGranted('ROLE_TATOUEUR') && !$this->isGranted('ROLE_ADMIN')) {
    //         $user = $this->getUser();
    //         if ($user && method_exists($user, 'getTatoueur') && $user->getTatoueur() instanceof Tatoueur) {
    //             $qb->andWhere('entity.tatoueur = :t')->setParameter('t', $user->getTatoueur());
    //         } else {
    //             $qb->andWhere('1 = 0'); // pas de tatoueur lié -> rien
    //         }
    //     }

    //     return $qb;
    // }
}
