<?php

namespace App\Controller\Admin;

use App\Entity\Carrousel;
use App\Form\CarrouselSlideType; // <= IMPORTANT
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CarrouselCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carrousel::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // adapte si ton Carrousel a un autre champ (titre, slug, etc.)
        yield TextField::new('titre', 'Titre')->hideOnIndex();

        yield CollectionField::new('carrouselSlides', 'Slides')
            ->setEntryType(CarrouselSlideType::class) // <= le form type ci-dessous
            ->allowAdd()
            ->allowDelete()
            ->setFormTypeOptions(['by_reference' => false]);
    }
}
