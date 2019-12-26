<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Alarm;
use App\Entity\System;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AlarmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('beginningDate', DateType::class, [
                'label' => "Date de début",
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endingDate', DateType::class, [
                'label' => "Date de fin",
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('reportingDate', DateType::class, [
                'label' => "Date de signalement",
                'widget' => 'single_text',
                'disabled' => true,
            ])
            ->add('reportingAuthor', EntityType::class, [
                'label' => "Signalé par",
                'class' => User::class,
                'choice_label' => 'displayName',
                'required' => true,
                'disabled' => true,
            ])
            ->add('notes', TextareaType::class, [
                'label' => "Remarques",
                'required' => true,
                'attr' => [
                    'placeholder' => "Décrivez vos observations par rapport à la pollution",
                    'rows' => 8,
                ]
            ])
            ->add('system', EntityType::class, [
                'label' => "Système",
                'class' => System::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => "Sélectionnez un système",
            ])
//            ->add('measures')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Alarm::class,
        ]);
    }
}
