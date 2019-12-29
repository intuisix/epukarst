<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Alarm;
use App\Entity\Basin;
use App\Entity\System;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\Measurability;
use App\Entity\SystemReading;
use Symfony\Component\Form\FormEvent;
use App\Form\SystemReadingStationType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SystemReadingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => "Code du relevé",
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ce code sera attribué automatiquement',
                ]
            ])
            ->add('fieldDateTime', DateType::class, [
                'label' => "Date de terrain",
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('alarm', EntityType::class, [
                'label' => "Alarme",
                'class' => Alarm::class,
                'choice_label' => 'name',
                'group_by' => 'system.name',
                'placeholder' => "Relevé non lié à une alarme",
                'required' => false,
            ])
            ->add('stationReadings', CollectionType::class, [
                'label' => "Stations",
                'entry_type' => SystemReadingStationType::class,
                'allow_add' => false,
                'allow_delete' => false,
            ])
        ;

        if ($options['showEncoding']) {
            $builder
                ->add('encodingAuthor', TextType::class, [
                    'label' => "Auteur de l'encodage",
                    'disabled' => true,
                    'required' => true,
                ])
                ->add('encodingDateTime', DateTimeType::class, [
                    'label' => "Date de l'encodage",
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'disabled' => true,
                    'required' => true,
                ])
                ->add('encodingNotes', TextareaType::class, [
                    'label' => "Remarques de l'encodage",
                    'required' => false,
                    'attr' => [
                        'placeholder' => "Entrez vos remarques concernant le système en général",
                    ]
                ])
            ;
        }

        if ($options['showValidation']) {
            $builder
                ->add('validationAuthor', TextType::class, [
                    'label' => "Auteur de la validation",
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('validationDateTime', DateTimeType::class, [
                    'label' => "Date de la validation",
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('validationNotes', TextareaType::class, [
                    'label' => "Remarques de la validation",
                    'required' => false,
                    'attr' => [
                        'placeholder' => "Entrez vos remarques concernant le système en général",
                    ]
                ])
                ->add('validationStatus', ChoiceType::class, [
                    'label' => "Etat de la validation",
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SystemReading::class,
                'showEncoding' => false,
                'showValidation' => false,
            ])
        ;
    }
}
