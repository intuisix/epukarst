<?php

namespace App\Form;

use App\Entity\System;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('introduction')
            ->add('commune')
            ->add('basin')
            ->add('number')
            ->add('description')
            ->add('slug')
            ->add('picture')
            ->add('waterMass')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => System::class,
        ]);
    }
}
