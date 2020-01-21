<?php

namespace App\Form;

use App\Entity\ParameterChoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Formulaire permettant de définir la valeur et le libellé d'un choix lié à
 * un paramètre.
 */
class ParameterChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'label' => "Libellé",
                'attr' => [
                    'placeholder' => "Libellé",
                ]
            ])
            ->add('value', NumberType::class, [
                'label' => "Valeur",
                'attr' => [
                    'placeholder' => "Valeur",
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ParameterChoice::class,
        ]);
    }
}
