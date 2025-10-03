<?php
namespace App\Form;

use App\Entity\Carrousel;
use App\Entity\Media;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarrouselType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $options): void
    {
        $b->add('media', EntityType::class, [
                'class' => Media::class,
                'choice_label' => 'filename',
                'placeholder' => '— choisir l’image —',
                'required' => true,
            ])
          ->add('title', TextType::class, ['required' => false])
          ->add('position', IntegerType::class, ['required' => false])
          ->add('isActive', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Carrousel::class, // ✅ nouvelle entité
        ]);
    }
}

