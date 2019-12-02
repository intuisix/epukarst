<?php

namespace App\Form;

use App\Entity\Measure;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemReadingMeasureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value')
            /*->add('tolerance')
            ->add('stable')
            ->add('valid')
            ->add('notes')
            ->add('encodingDateTime')
            ->add('fieldDateTime')
            ->add('reading')
            ->add('measurability')
            ->add('encodingAuthor')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }
}
