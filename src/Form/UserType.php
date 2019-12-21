<?php

namespace App\Form;

use App\Entity\User;
use App\Form\UserRoleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => "Prénom",
                'required' => false,
                'attr' => [
                    'placeholder' => "Prénom",
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => "Nom",
                'required' => false,
                'attr' => [
                    'placeholder' => "Nom",
                ],
            ])
            ->add('organization', TextType::class, [
                'label' => "Organisation",
                'required' => false,
                'attr' => [
                    'placeholder' => "Société, organisme ou club",
                ],
            ])
            ->add('email', TextType::class, [
                'label' => "E-mail",
                'required' => true,
                'attr' => [
                    'placeholder' => "nom.prenom@exemple.org",
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => "Téléphone",
                'required' => false,
                'attr' => [
                    'placeholder' => "+32 999 99.99.99",
                ],
            ])
            ->add('displayName', TextType::class, [
                'label' => "Pseudonyme",
                'required' => false,
                'attr' => [
                    'placeholder' => "Peut être complété automatiquement",
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'required' => false,
                'attr' => [
                    'placeholder' => "Brève présentation de l'utilisateur (facultatif)",
                ],
            ])
            ->add('picture', UrlType::class, [
                'label' => "Avatar",
                'required' => false,
                'attr' => [
                    'placeholder' => "https://www.exemple.org/photo",
                ],
            ])
            ->add('userRoles', CollectionType::class, [
                'label' => "Rôles",
                'entry_type' => UserRoleType::class,
                'allow_add' => true,
                'allow_delete' => true,
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
