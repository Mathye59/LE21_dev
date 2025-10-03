<?php

namespace App\Form;

use App\Entity\FormContact;
use App\Entity\Tatoueur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomPrenom')
            ->add('email')
            ->add('telephone')
            ->add('sujet')
            ->add('message')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('tatoueur', EntityType::class, [
                'class' => Tatoueur::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormContact::class,
        ]);
    }
}
