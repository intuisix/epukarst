<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Alarm;
use App\Entity\Basin;
use App\Entity\System;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\AttachmentType;
use App\Entity\Measurability;
use App\Entity\SystemReading;
use Symfony\Component\Form\FormEvent;
use App\Form\SystemReadingStationType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
                'label' => "Code de la fiche",
                'required' => false,
                'help' => "Ce code sera généré automatiquement si vous n'indiquez rien.",
            ])
            ->add('fieldDateTime', DateType::class, [
                'label' => "Date de terrain",
                'widget' => 'single_text',
                'required' => true,
                'help' => "Indiquez la date de la dernière mesure."
            ])
            ->add('alarm', EntityType::class, [
                'label' => "Alarme",
                'class' => Alarm::class,
                'choice_label' => 'name',
                'group_by' => 'system.name',
                'placeholder' => "Pas d'alarme liée",
                'required' => false,
                'help' => "Une alarme sera créée automatiquement si une valeur est hors norme.",
            ])
            ->add('stationReadings', CollectionType::class, [
                'label' => "Stations",
                'entry_type' => SystemReadingStationType::class,
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('controls', CollectionType::class, [
                'label' => "Contrôles",
                'entry_type' => SystemReadingControlType::class,
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('newAttachments', FileType::class, [
                'label' => "Ajouter des pièces jointes",
                'help' => "Utilisez ce contrôle pour charger une ou plusieurs pièces jointes n'excédant pas 2 Mo.",
                'attr' => [
                    'placeholder' => "Choisissez un ou plusieurs fichiers",
                ],
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '2048k',
                            'maxSizeMessage' => "Les fichiers doivent faire moins de 2.048 kilo-octets.",
                        ])
                    ])
                ]
            ])
            ->add('attachments', CollectionType::class, [
                'label' => "Pièces jointes",
                'entry_type' => AttachmentType::class,
                'allow_add' => false,
                'allow_delete' => true,
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
                        'placeholder' => "Entrez vos remarques non spécifiques aux stations, concernant les observations, les mesures ou l'encodage",
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
                        'placeholder' => "Entrez vos remarques concernant la validation",
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
