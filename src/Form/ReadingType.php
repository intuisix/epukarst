<?php

namespace App\Form;

use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\MeasureType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Formulaire permettant l'encodage, la modification et la validation d'un
 * relevé.
 */
class ReadingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('station', EntityType::class, [
                'label' => 'Station',
                'class' => Station::class,
                'placeholder' => 'Sélectionnez une station',
                'choice_label' => function(Station $station) {
                    return $station->getBasin()->getName(). " : " . $station->getName();
                },
                'group_by' => function(Station $station) {
                    return $station->getBasin()->getSystem()->getName();
                }
            ])
            ->add('code', TextType::class, [
                'label' => "Code du relevé",
                'required' => false,
                'attr' => [
                    'placeholder' => "Ce code sera complété automatiquement" ],
            ])
            ->add('fieldDateTime', DateTimeType::class, [
                'label' => "Date de terrain (globale)",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'placeholder' => "Entrez la date et l'heure de terrain",
            ])
            ->add('measures', CollectionType::class, [
                'label' => "Mesures",
                'entry_type' => MeasureType::class,
                'allow_add' => true,
                'allow_delete' => true ])
            ->add('encodingDateTime', DateTimeType::class, [
                    'label' => "Date d'encodage",
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'disabled' => true,
                ])
            ->add('encodingAuthor', TextType::class, [
                    'label' => "Auteur de l'encodage",
                    'disabled' => true,
                ])
            ->add('encodingNotes', TextareaType::class, [
                'label' => "Remarques de l'encodage",
                'required' => false,
                'attr' => [
                    'placeholder' => "Introduisez vos remarques concernant l'observation et/ou l'encodage",
                ],
            ])
        ;

        if ($options['validation'] === true) {
            $builder
                ->add('validationDateTime', DateTimeType::class, [
                    'label' => "Date de validation",
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'disabled' => true,
                ])
                ->add('validationAuthor', TextType::class, [
                    'label' => "Auteur de la validation",
                    'disabled' => true,
                ])
                ->add('validationNotes', TextareaType::class, [
                    'label' => "Remarques de la validation",
                    'required' => false,
                    'attr' => [
                        'placeholder' => "Introduisez vos remarques concernant la validation",
                    ],
                ])
                ->add('validated', ChoiceType::class, [
                    'label' => "Etat",
                    'choices' => [
                        'Invalidé' => false,
                        'Validé' => true,
                    ],
                    'placeholder' => "Sélectionnez un état",
                    'required' => true,
                ]);
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /* Ordonner les mesures par nom de paramètre */
        usort(
            $view->children['measures']->children,
            function ($a, $b) {
                return
                    $a->vars['data']->getParameter()->getName() <=>
                    $b->vars['data']->getParameter()->getName();
            }
        );
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reading::class,
            'validation' => false,
        ]);
    }
}
