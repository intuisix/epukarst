<?php

namespace App\Form;

use App\Entity\Parameter;
use App\Repository\ParameterRepository;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom",
                'attr' => [
                    'placeholder' => "Nom court"
                ]
            ])
            ->add('unit', TextType::class, [
                'label' => "Unité",
                'required' => false,
                'attr' => [
                    'placeholder' => "Unité de mesure"
                ]
            ])
            ->add('normativeMinimum', NumberType::class, [
                'label' => "Seuil minimum normatif",
                'required' => false,
                'attr' => [
                    'placeholder' => "Valeur facultative"
                ]
            ])
            ->add('normativeMaximum', NumberType::class, [
                'label' => "Seuil maximum normatif",
                'required' => false,
                'attr' => [
                    'placeholder' => "Valeur facultative"
                ]
            ])
            ->add('physicalMinimum', NumberType::class, [
                'label' => "Valeur minimum physique",
                'required' => false,
                'attr' => [
                    'placeholder' => "Valeur facultative"
                ]
            ])
            ->add('physicalMaximum', NumberType::class, [
                'label' => "Valeur maximum physique",
                'required' => false,
                'attr' => [
                    'placeholder' => "Valeur facultative"
                ]
            ])
            ->add('title', TextType::class, [
                'label' => "Titre",
                'attr' => [
                    'placeholder' => "Appellation usuelle non abrégée"
                ]
            ])
            ->add('introduction', TextType::class, [
                'label' => "Introduction générale",
                'attr' => [
                    'placeholder' => "Introduction indiquant brièvement ce que le paramètre représente"
                ]
            ])
            ->add('description', CKEditorType::class, [
                'label' => "Description détaillée",
                'attr' => [
                    'placeholder' => "Entrez une description détaillée qui permettra aux utilisateurs de savoir à quoi sert le paramètre et en quoi son suivi est utile pour la prévention des pollutions",
                    'rows' => 6
                ],
            ])
            ->add('favorite', CheckboxType::class, [
                'label' => "Favori",
                'help' => "S'il est marqué comme favori, ce paramètre apparaîtra dans les listes de relevés, dans lesquelles les mesures sont rassemblées sous forme agrégée.",
                'required' => false
            ])
            ->add('position', ChoiceType::class, [
                'label' => "Position",
                'choices' => $options['positions'],
                'choice_label' => function($choice, $key, $value) {
                    return ($value + 1) . ". " . $key;
                },
                'placeholder' => "(en dernier)",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Parameter::class,
            ])
            ->setRequired('positions')
            ->setAllowedTypes('positions', 'integer[]')
        ;
    }
}
