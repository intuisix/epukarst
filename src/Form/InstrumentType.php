<?php

namespace App\Form;

use App\Entity\Instrument;
use App\Form\CalibrationType;
use App\Form\MeasurabilityType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                    'placeholder' => "Entrez la marque et/ou le modèle (facultatif)",
                ],
            ])
            ->add('serialNumber', TextType::class, [
                'label' => "Numéro de série ou de lot",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez le numéro de série donné par le fabricant (facultatif)",
                ],
            ])
            ->add('description', CKEditorType::class, [
                'label' => "Description détaillée",
                'attr' => [
                    'placeholder' => "Entrez une description détaillée de l'instrument, comme, par exemple, son utilité, ses instructions d'utilisation, ses précautions de sécurité, la façon de vérifier son bon fonctionnement, ...",
                    'rows' => 10,
                ],
            ])
            ->add('measurabilities', CollectionType::class, [
                'label' => "Paramètres",
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
            ->add('requiredInstruments', CollectionType::class, [
                'label' => "Liaisons",
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Instrument::class,
                    'choice_label' => 'name',
                    'placeholder' => "Sélectionnez un instrument",
                ],
                'allow_add' => true,
                'allow_delete' => true, 
            ])
            ->add('modelInstrument', EntityType::class, [
                'label' => "Instrument modèle",
                'class' => Instrument::class,
                'choice_label' => 'name',
                'placeholder' => "Sélectionnez un modèle (facultatif)",
                'help' => "Permet à cet instrument d'hériter de la description de son instrument modèle",
                'required' => false,
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /* Ordonner les paramètres par position */
        usort(
            $view->children['measurabilities']->children,
            function ($a, $b) {
                return
                    $a->vars['data']->getParameter()->getPosition() <=>
                    $b->vars['data']->getParameter()->getPosition();
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
