<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\Station;
use App\Entity\StationKind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class StationSimplifiedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Station",
                'required' => true,
                'attr' => [
                    'placeholder' => 'Dénomination',
                ],
            ])
            ->add('atlasCode', TextType::class, [
                'label' => "AKWA",
                'required' => false,
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
                'attr' => [
                    'placeholder' => 'Bassin',
                ]
            ])
            ->add('kind', EntityType::class, [
                'label' => "Genre",
                'class' => StationKind::class,
                'choice_label' => 'name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Genre',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'attr' => [
                    'placeholder' => "Entrez une description la plus détaillée possible afin de permettre la réalisation des mesures toujours au même emplacement, même lorsque d'autres personnes sont amenées à réaliser les mesures sur cette station",
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Station::class,
            ])
            ->setRequired('basins')
            ->setAllowedTypes('basins', 'App\Entity\Basin[]')
        ;
    }
}
