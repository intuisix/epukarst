<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\Station;
use App\Entity\StationKind;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SystemStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => "Code identifiant",
                'attr' => [
                    'placeholder' => "Ce code pourra être attribué automatiquement"
                ],
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => "Dénomination",
                'attr' => [
                    'placeholder' => "Entrez une dénomination"
                ],
                'required' => true,
            ])
            ->add('basin', EntityType::class, [
                'label' => "Bassin",
                'class' => Basin::class,
                'choices' => $options['basins'],
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => "Sélectionnez un bassin",
            ])
            ->add('kind', EntityType::class, [
                'label' => "Type",
                'class' => StationKind::class,
                'choice_label' => 'name',
                'placeholder' => "Sélectionnez un type (facultatif)",
                'required' => false,
            ])
            ->add('atlasCode', TextType::class, [
                'label' => "Code AKWA",
                'attr' => [
                    'placeholder' => "Entrez le numéro AKWA (facultatif)"
                ],
                'required' => false,
            ])
            ->add('description', CKEditorType::class, [
                'label' => "Description",
                'attr' => [
                    'placeholder' => "Entrez une description la plus détaillée possible afin de permettre la réalisation des mesures toujours au même emplacement, même lorsque d'autres personnes sont amenées à réaliser les mesures sur cette station",
                ],
                'config' => [
                    'height' => 250,
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
        ;
    }
}
