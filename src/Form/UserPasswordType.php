<?php

namespace App\Form;

use App\Entity\UserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * Formulaire pour le changement de mot de passe d'un utilisateur.
 */
class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => "Votre mot de passe actuel",
                'help' => $options['onBehalf'] ? "Entrez votre propre mot de passe actuel, et non celui de l'utilisateur dont vous changez le mot de passe." : null,
            ])
            ->add('wishedPassword', RepeatedType::class, [
                'label' => "Mot de passe souhaité",
                'type' => PasswordType::class,
                'invalid_message' => "Ces mots de passe sont différents. Veuillez entrer deux fois le même mot de passe souhaité.",
                'first_options' => [
                    'label' => "Mot de passe souhaité",
                ],
                'second_options' => [
                    'label' => "Confirmation du mot de passe",
                ],
            ])
            ->add('revealInEmail', CheckboxType::class, [
                'label' => "Révéler le mot de passe dans l'e-mail (non recommandé)",
                'help' => "Notez qu'un e-mail sera systématiquement transmis afin de confirmer le changement.",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => UserPassword::class,
            ])
            ->setRequired('onBehalf')
            ->setAllowedTypes('onBehalf', 'boolean');
    }
}
