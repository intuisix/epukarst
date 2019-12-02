<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Station;
use App\Entity\System;
use App\Entity\Reading;
use App\Entity\Measurability;
use App\Entity\SystemReading;
use Symfony\Component\Form\FormEvent;
use App\Form\SystemReadingStationType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('system', EntityType::class, [
                'label' => "Système",
                'class' => System::class,
                'choice_label' => 'name',
                'placeholder' => "Veuillez sélectionner un système",
                'required' => true,
            ])
            ->add('code', TextType::class, [
                'label' => "Code",
                'required' => false,
            ])
            ->add('fieldDateTime', DateTimeType::class, [
                'label' => "Date de terrain",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => true,
            ])
            ->add('encodingAuthor', EntityType::class, [
                'label' => "Auteur de l'encodage",
                'class' => User::class,
                'choice_label' => 'displayName',
                'required' => true,
            ])
            ->add('encodingDateTime', DateTimeType::class, [
                'label' => "Date d'encodage",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => true,
            ])
            ->add('encodingNotes', TextareaType::class, [
                'label' => "Remarques d'encodage",
                'required' => false,
            ])
            ->add('validationAuthor', EntityType::class, [
                'label' => "Auteur de la validation",
                'class' => User::class,
                'choice_label' => 'displayName',
                'required' => false,
            ])
            ->add('validationDateTime', DateTimeType::class, [
                'label' => "Date de validation",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
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
            ->add('stationReadings', CollectionType::class, [
                'label' => "Stations",
                'entry_options' => [
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
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onEvent'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SystemReading::class,
        ]);
        $resolver->setRequired('stations');
        $resolver->setAllowedTypes('stations', 'App\Entity\Station[]');
        $resolver->setRequired('measurabilities');
        $resolver->setAllowedTypes('measurabilities', 'App\Entity\Measurability[]');
    }

    /* TODO: Supprimer cette fonction inutile */
    public function onEvent(FormEvent $event)
    {
        $data = $event->getData();
        dump($event, $data);
    }
}
