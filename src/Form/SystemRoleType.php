<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemRoleType extends AbstractType
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
            ->add('linkedUser', EntityType::class, [
                'class' => User::class,
                'label' => "Utilisateur",
                'choice_label' => 'displayName',
                'required' => true,
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
