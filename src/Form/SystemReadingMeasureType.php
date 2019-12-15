<?php

namespace App\Form;

use App\Entity\Measure;
use App\Entity\Measurability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SystemReadingMeasureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('measurability', EntityType::class, [
                'required' => true,
                'class' => Measurability::class,
                'choice_label' => 'nameWithUnit',
                'attr' => [ 'hidden' => 'hidden' ],
            ])
            ->add('value', NumberType::class, [
                'label' => null,
                'required' => false,
            ])
            ->add('stable', CheckboxType::class, [
                'label' => "S",
                'required' => false,
            ])
            ->add('valid', CheckboxType::class, [
                'label' => "V",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }
}
