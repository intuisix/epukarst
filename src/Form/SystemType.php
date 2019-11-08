<?php

namespace App\Form;

use App\Entity\System;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SystemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => "Code",
                'help' => "Ce code sera utilisé pour construire ceux des futurs relevés relatifs à ce système.",
                'attr' => [
                    'placeholder' => "Choisissez un code"
                ]
            ])
            ->add('name', TextType::class, [
                'label' => "Dénomination",
                'attr' => [
                    'placeholder' => "Entrez le nom du système"
                ]
            ])
            ->add('commune', TextType::class, [
                'label' => "Commune",
                'attr' => [
                    'placeholder' => "Entrez le nom de la commune"
                ]
            ])
            ->add('basin', TextType::class, [
                'label' => "Bassin versant",
                'attr' => [
                    'placeholder' => "Entrez le nom du bassin dans lequel se déverse le système"
                ]
            ])
            ->add('number', TextType::class, [
                'label' => "Code AKWA",
                'attr' => [
                    'placeholder' => "Entrez le numéro AKWA facultatif"
                ]
            ])
            ->add('waterMass', TextType::class, [
                'label' => "Code de masse d'eau",
                'attr' => [
                    'placeholder' => "Entrez le numéro de masse d'eau facultatif"
                ]
            ])
            ->add('slug', TextType::class, [
                'label' => "Slug pour l'URL",
                'required' => false,
                'attr' => [
                    'placeholder' => "Si vous laissez ce champ vide, le slug sera attribué automatiquement"
                ]
            ])
            ->add('introduction', TextType::class, [
                'label' => "Introduction générale",
                'attr' => [
                    'placeholder' => "Entrez une introduction générale, qui sera affichée juste en-dessous du nom du système"
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description détaillée (HTML)",
                'attr' => [
                    'rows' => 8,
                    'placeholder' => "En utilisant le format HTML, entrez une description détaillée qui permettra aux visiteurs de découvrir les particularités du système et les raisons pour lesquelles il est étudié"
                ],
                'help' => "En HTML, chaque paragraphe est commencé par <p> et terminé par </p>. Pour mettre un texte en gras, entourez-le de <b> et </b>. Pour les italiques, c'est <i> et </i>. Les hyperliens sont créés avec <a href=\"url-référencé\"> et </a>.",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => System::class,
        ]);
    }
}
