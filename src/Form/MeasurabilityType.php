<?php

namespace App\Form;

use App\Entity\Parameter;
use App\Entity\Measurability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MeasurabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameter', EntityType::class, [
                'label' => "Paramètre",
                'class' => Parameter::class,
                'choice_label' => function(Parameter $parameter) {
                    return $parameter->getNameWithUnit();
                },
                'placeholder' => "Sélectionnez un paramètre",
            ])
            ->add('minimumValue', NumberType::class, [
                'label' => "Valeur minimum",
                'required' => false,
                'attr' => [
                    'placeholder' => "Limite inférieure",
                ],
            ])
            ->add('maximumValue', NumberType::class, [
                'label' => "Valeur maximum",
                'required' => false,
                'attr' => [
                    'placeholder' => "Limite supérieure",
                ],
            ])
            ->add('tolerance', NumberType::class, [
                'label' => "Tolérance",
                'required' => false,
                'attr' => [
                    'placeholder' => "Précision de mesure",
                ],
            ])
            ->add('inputUnit', TextType::class, [
                'label' => "Unité de saisie",
                'required' => false,
                'attr' => [
                    'placeholder' => "Unité"
                ],
            ])
            ->add('inputConversion', TextType::class, [
                'label' => "Conversion de saisie",
                'required' => false,
                'attr' => [
                    'placeholder' => "(x+3.5)*(1.2^3)"
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => "Remarques",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez des remarques éventuelles",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measurability::class,
        ]);
    }
}
