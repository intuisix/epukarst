<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\System;
use App\Entity\SystemRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SystemRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('system', EntityType::class, [
                'class' => System::class,
                'label' => "Système",
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => "Par défaut",
            ])
            ->add('userAccount', EntityType::class, [
                'class' => User::class,
                'label' => "Utilisateur",
                'choice_label' => 'displayName',
                'required' => false,
                'placeholder' => "Par défaut",
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    "Observateur" => 'SYSTEM_OBSERVER',
                    "Contributeur" => 'SYSTEM_CONTRIBUTOR',
                    "Gestionnaire" => 'SYSTEM_MANAGER',
                ],
                'label' => "Rôle",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SystemRole::class,
            ]);
    }
}
