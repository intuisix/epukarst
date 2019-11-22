<?php

namespace App\Form;

use App\Entity\Instrument;
use App\Form\CalibrationType;
use App\Form\MeasurabilityType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class InstrumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => "Code d'inventaire",
                'help' => "La notion d'instrument recouvre, au choix, un appareil identifié individuellement ou un ensemble de consommables identifiés par lot.",
                'attr' => [
                    'placeholder' => "Entrez un code identifiant unique",
                ],
            ])
            ->add('name', TextType::class, [
                'label' => "Dénomination",
                'attr' => [
                    'placeholder' => "Entrez une dénomination unique",
                ],
            ])
            ->add('model', TextType::class, [
                'label' => "Marque et/ou modèle",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la marque et/ou le modèle éventuel",
                ],
            ])
            ->add('serialNumber', TextType::class, [
                'label' => "Numéro de série",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez le numéro de série éventuel donné par le fabricant",
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description détaillée",
                'attr' => [
                    'placeholder' => "Entrez une description détaillée de l'instrument, comme, par exemple, son utilité, des instructions d'utilisation, des précautions de sécurité, la vérification du bon fonctionnement, ...  (Vous associerez les paramètres mesurés par cet instrument dans la suite du formulaire.)",
                ]
            ])
            ->add('measurabilities', CollectionType::class, [
                'label' => "Paramètres mesurables",
                'entry_type' => MeasurabilityType::class,
                'allow_add' => true,
                'allow_delete' => true
            ])
            ->add('calibrations', CollectionType::class, [
                'label' => "Etalonnages",
                'entry_type' => CalibrationType::class,
                'allow_add' => true,
                'allow_delete' => true
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /* Ordonner les mesurabilités par nom de paramètre */
        usort(
            $view->children['measurabilities']->children,
            function ($a, $b) {
                return
                    $a->vars['data']->getParameter()->getName() <=>
                    $b->vars['data']->getParameter()->getName();
            }
        );

        /* Ordonner les étalonnages par dates */
        usort(
            $view->children['calibrations']->children,
            function ($a, $b) {
                return
                    $a->vars['data']->getDoneDate() <=>
                    $b->vars['data']->getDoneDate();
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Instrument::class,
        ]);
    }
}
