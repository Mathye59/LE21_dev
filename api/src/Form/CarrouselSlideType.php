<?php

namespace App\Form;

use App\Entity\CarrouselSlide;
use App\Entity\Media;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class CarrouselSlideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b
            ->add('position', IntegerType::class, [
                'required' => false,
                'empty_data' => '0',
                'label' => 'Position',
            ])
            ->add('titre', null, [
                'required' => false,
                'label' => 'Titre',
            ])
            ->add('media', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'Nom du fichier', // ou un __toString() dans Media
                'placeholder' => '— Choisir un média —',
                'label' => 'Média',
            ]);
    }
}
