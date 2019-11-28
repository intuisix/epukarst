<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\Station;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class StationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => "Code identifiant",
                'attr' => [
                    'placeholder' => "Entrez un code unique"
                ]
            ])
            ->add('name', TextType::class, [
                'label' => "Dénomination",
                'attr' => [
                    'placeholder' => "Entrez une dénomination"
                ]
            ])
            ->add('basin', EntityType::class, [
                'label' => "Bassin",
                'class' => Basin::class,
                'choice_label' => 'name',
                'group_by' => 'system.name'
            ])
            ->add('kind', TextType::class, [
                'label' => "Genre",
                'attr' => [
                    'placeholder' => "Entrez un genre"
                ]
            ])
            ->add('atlasCode', TextType::class, [
                'label' => "Code AKWA",
                'attr' => [
                    'placeholder' => "Entrez le numéro AKWA (facultatif)"
                ],
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'attr' => [
                    'placeholder' => "Entrez une description la plus détaillée possible afin de permettre la réalisation des mesures toujours au même emplacement, même lorsque d'autres personnes sont amenées à réaliser les mesures sur cette station",
                    'rows' => 15,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Station::class,
        ]);
    }
}
