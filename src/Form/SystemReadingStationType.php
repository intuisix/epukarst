<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\StationKind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SystemReadingStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('station', EntityType::class, [
                'label' => "Station",
                'class' => Station::class,
                'choice_label' => 'name',
                'choices' => $options['stations'],
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'placeholder' => 'Station',
                ],
            ])
            ->add('name', TextType::class, [
                'label' => "Station",
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Dénomination',
                ],
            ])
            ->add('atlasCode', TextType::class, [
                'label' => "AKWA",
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => "N°",
                ],
            ])
            ->add('basin', EntityType::class, [
                'label' => "Bassin",
                'class' => Basin::class,
                'choice_label' => 'name',
                'choices' => $options['basins'],
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Bassin',
                ]
            ])
            ->add('kind', EntityType::class, [
                'label' => "Genre",
                'class' => StationKind::class,
                'choice_label' => 'name',
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Genre',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'attr' => [
                    'placeholder' => "Entrez une description la plus détaillée possible afin de permettre la réalisation des mesures toujours au même emplacement, même lorsque d'autres personnes sont amenées à réaliser les mesures sur cette station",
                ],
                'mapped' => false,
            ])
            ->add('measures', CollectionType::class, [
                'label' => "Mesure",
                'entry_type' => SystemReadingMeasureType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Reading::class,
            ])
            ->setRequired('stations')
            ->setAllowedTypes('stations', 'App\Entity\Station[]')
            ->setRequired('basins')
            ->setAllowedTypes('basins', 'App\Entity\Basin[]');
    }
}
