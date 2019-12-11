<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'label' => "Mot de passe",
                'type' => PasswordType::class,
                'invalid_message' => "Les mots de passe sont différents. Veuillez en entrer des identiques.",
                'first_options' => [
                    'label' => "Mot de passe",
                ],
                'second_options' => [
                    'label' => "Confirmation du mot de passe",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
