<?php

namespace App\Form;

use App\Entity\Parameter;
use App\Entity\FilterMeasure;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class FilterMeasureType extends AbstractType
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
                'placeholder' => "Paramètre",
            ])
            ->add('minimumValue', NumberType::class, [
                'label' => "Entre",
                'required' => false,
                'attr' => [
                    'placeholder' => "Minimum"
                ]
            ])
            ->add('maximumValue', NumberType::class, [
                'label' => "et",
                'required' => false,
                'attr' => [
                    'placeholder' => "Maximum"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FilterMeasure::class,
        ]);
    }
}
