<?php

namespace App\Form;

use App\Entity\Parameter;
use Symfony\Component\Form\FormView;
use App\Repository\ParameterRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Formulaire permettant de définir un paramètre.
 */
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
                'label' => "Nom complet",
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
            ->add('displayColor', ChoiceType::class, [
                'label' => "Couleur d'affichage",
                'choices' => [
                    'Azure' => '#f0ffff',
                    'Beige' => '#f5f5dc',
                    'BurlyWood' => '#deb887',
                    'Coral' => '#ff7f50',
                    'Cornsilk' => '#fff8dc',
                    'Cyan' => '#00ffff',
                    'DarkSeaGreen' => '#8fbc8f',
                    'Gold' => '#ffd700',
                    'Goldenrot' => '#daa520',
                    'GreenYellow' => '#adff2f',
                    'GreenYellow' => '#adff2f',
                    'HotPink' => '#ff69b4',
                    'Ivory' => '#fffff0',
                    'Lavender' => '#e6e6fa',
                    'LemonChiffon' => '#fffacd',
                    'LightBlue' => '#add8e6',
                    'LightCoral' => '#f08080',
                    'LightCyan' => '#e0ffff',
                    'LightGray' => '#a9a9a9',
                    'LightGray' => '#d3d3d3',
                    'LightGreen' => '#90ee90',
                    'LightPink' => '#ffb6c1',
                    'LightSalmon' => '#ffa07a',
                    'LightYellow' => '#ffffe0',
                    'Salmon' => '#fa8072',
                    'SandyBrown' => '#f4a460',
                    'Silver' => '#c0c0c0',
                    'SkyBlue' => '#87ceeb',
                ],
                'choice_attr' => function ($choice, $key, $value) {
                    return [ 'style' => 'background-color: ' . $choice ];
                },
                'required' => false,
            ])
            ->add('choices', CollectionType::class, [
                'label' => "Liste de choix",
                'entry_type' => ParameterChoiceType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /* Ordonner les choix par valeur */
        usort(
            $view->children['choices']->children,
            function ($a, $b) {
                return
                    $a->vars['data']->getValue() <=>
                    $b->vars['data']->getValue();
            }
        );
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
