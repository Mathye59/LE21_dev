<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TatoueurUserType extends AbstractType
{
    public function __construct(private AuthorizationCheckerInterface $auth) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Email de connexion du compte lié (User)
        $builder->add('email', EmailType::class, [
            'label' => 'Email de connexion',
            'required' => true,
        ]);

        // Les rôles ne sont éditables que par un admin
        if ($this->auth->isGranted('ROLE_ADMIN')) {
            $builder->add('roles', ChoiceType::class, [
                'label'    => 'Rôles',
                'multiple' => true,
                'expanded' => false,
                'choices'  => [
                    'Employé (ROLE_USER)'      => 'ROLE_USER',
                    'Administrateur (ROLE_ADMIN)' => 'ROLE_ADMIN',
                ],
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // très important : le sous-form représente un User
        ]);
    }
}
