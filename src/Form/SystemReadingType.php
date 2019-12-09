<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\User;
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
            ->add('system', TextType::class, [
                'label' => "Système",
                'required' => true,
                'disabled' => true,
            ])
            ->add('code', TextType::class, [
                'label' => "Code",
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
            ->add('stationReadings', CollectionType::class, [
                'label' => "Stations",
                'entry_options' => [
                    'basins' => $options['basins'],
                    'stations' => $options['stations']],
                'entry_type' => SystemReadingStationType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('systemParameters', CollectionType::class, [
                'label' => 'Paramètres/instruments',
                'entry_type' => EntityType::class,
                'entry_options' => [
                     'label' => "Paramètre",
                     'class' => Measurability::class,
                     'choice_label' => 'nameWithUnit',
                     'required' => true,
                     'choices' => $options['measurabilities'],
                     'group_by' => 'parameter.name',
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
            ])
            ->add('encodingAuthor', TextType::class, [
                'label' => "Auteur de l'encodage",
                'disabled' => true,
                'required' => true,
            ])
            ->add('encodingDateTime', DateTimeType::class, [
                'label' => "Date d'encodage",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => true,
                'required' => true,
            ])
            ->add('encodingNotes', TextareaType::class, [
                'label' => "Remarques d'encodage",
                'required' => false,
            ])
        ;

        if ($options['validation']) {
            $form
                ->add('validationAuthor', TextType::class, [
                    'label' => "Auteur de la validation",
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('validationDateTime', DateTimeType::class, [
                    'label' => "Date de validation",
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('validationNotes', TextareaType::class, [
                    'label' => "Remarques de validation",
                    'required' => false,
                ])
                ->add('validationStatus', ChoiceType::class, [
                    'label' => "Etat de validation",
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
                'validation' => false,
            ])
            ->setRequired('stations')
            ->setAllowedTypes('stations', 'App\Entity\Station[]')
            ->setRequired('basins')
            ->setAllowedTypes('basins', 'App\Entity\Basin[]')
            ->setRequired('measurabilities')
            ->setAllowedTypes('measurabilities', 'App\Entity\Measurability[]');
    }
}
