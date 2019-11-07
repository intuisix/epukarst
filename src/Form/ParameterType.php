<?php

namespace App\Form;

use App\Entity\Parameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                    'placeholder' => "Entrez le nom abrégé"
                ]
            ])
            ->add('unit', TextType::class, [
                'label' => "Unité",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez l'unité de mesure"
                ]
            ])
            ->add('normativeMinimum', NumberType::class, [
                'label' => "Seuil minimum normatif",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la valeur minimum normative"
                ]
            ])
            ->add('normativeMaximum', NumberType::class, [
                'label' => "Seuil maximum normatif",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la valeur maximum physique"
                ]
            ])
            ->add('physicalMinimum', NumberType::class, [
                'label' => "Valeur minimum physique",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la valeur minimum mesurable"
                ]
            ])
            ->add('physicalMaximum', NumberType::class, [
                'label' => "Valeur minimum physique",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la valeur maximum mesurable"
                ]
            ])
            ->add('title', TextType::class, [
                'label' => "Titre",
                'attr' => [
                    'placeholder' => "Entrez l'appellation usuelle non abrégée"
                ]
            ])
            ->add('introduction', TextType::class, [
                'label' => "Introduction générale",
                'attr' => [
                    'placeholder' => "Entrez une introduction indiquant brièvement ce que le paramètre représente"
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description détaillée",
                'attr' => [
                    'placeholder' => "En format HTML, entrez une description détaillée qui permettra aux utilisateurs de savoir à quoi sert le paramètre et en quoi son suivi est utile pour la prévention des pollutions",
                    'rows' => 8
                ],
                'help' => "En HTML, chaque paragraphe est commencé par <p> et terminé par </p>. Pour mettre un texte en gras, entourez-le de <b> et </b>. Pour les italiques, c'est <i> et </i>. Les hyperliens sont créés avec <a href=\"url-référencé\"> et </a>.",
            ])
            ->add('favorite', CheckboxType::class, [
                'label' => "Favori",
                'help' => "S'il est marqué comme favori, ce paramètre apparaîtra dans la liste des relevés et pourra faire l'objet de filtrage.",
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parameter::class,
        ]);
    }
}
