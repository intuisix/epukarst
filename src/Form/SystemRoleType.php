<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\System;
use App\Entity\UserRole;
use App\Entity\SystemRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
            ->add('author', EntityType::class, [
                'class' => User::class,
                'label' => "Utilisateur",
                'choice_label' => 'displayName',
                'required' => false,
                'placeholder' => "Par défaut",
            ])
            ->add('canView', CheckboxType::class, [
                'label' => "Visualisation",
                'required' => false,
            ])
            ->add('canEncode', CheckboxType::class, [
                'label' => "Encodage",
                'required' => false,
            ])
            ->add('canValidate', CheckboxType::class, [
                'label' => "Validation",
                'required' => false,
            ])
            ->add('canExport', CheckboxType::class, [
                'label' => "Exportation",
                'required' => false,
            ])
            ->add('canDelete', CheckboxType::class, [
                'label' => "Suppression",
                'required' => false,
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
