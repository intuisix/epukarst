<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\System;
use App\Entity\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('linkedRole', EntityType::class, [
                'class' => Role::class,
                'label' => "Rôle",
                'choice_label' => 'role',
                'required' => 'true',
                'placeholder' => "Sélectionnez un rôle",
            ])
            ->add('linkedSystem', EntityType::class, [
                'class' => System::class,
                'label' => "Système",
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => "Tous les systèmes",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserRole::class,
        ]);
    }
}
