<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => "Prénom",
            ])
            ->add('lastName', TextType::class, [
                'label' => "Nom",
            ])
            ->add('organization', TextType::class, [
                'label' => "Organisation",
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => "E-mail",
            ])
            ->add('phone', TelType::class, [
                'label' => "Téléphone",
                'required' => false,
            ])
            ->add('displayName', TextType::class, [
                'label' => "Pseudonyme",
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'required' => false,
            ])
            ->add('picture', UrlType::class, [
                'label' => "Photo",
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
