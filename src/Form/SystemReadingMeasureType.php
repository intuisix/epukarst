<?php

namespace App\Form;

use App\Entity\Measure;
use App\Entity\Measurability;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SystemReadingMeasureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('measurability', EntityType::class, [
                'required' => true,
                'class' => Measurability::class,
                'choice_label' => 'nameWithUnit',
                'attr' => [ 'hidden' => 'hidden' ],
            ])
            ->add('stable', CheckboxType::class, [
                'label' => "S",
                'required' => false,
                'attr' => [ 'tabindex' => -1 ],
            ])
            ->add('valid', CheckboxType::class, [
                'label' => "V",
                'required' => false,
                'attr' => [ 'tabindex' => -1 ],
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [ $this, 'onPreSetData' ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }

    /**
     * Adapte le type de contrôle affiché sur le formulaire en fonction du
     * paramètre mesuré: liste déroulante dans le cas où le paramètre comporte
     * des choix, ou saisie numérique sinon.
     *
     * @param FormEvent $event
     * @return void
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $measure = $event->getData();
        $choices = $measure->getMeasurability()->getParameter()->getChoicesArray();

        if ($choices !== null) {
            $form
                ->add('value', ChoiceType::class, [
                    'label' => null,
                    'required' => false,
                    'choices' => $choices,
                ]);
        } else {
            $form
                ->add('value', NumberType::class, [
                    'label' => null,
                    'required' => false,
                ]);
        }
    }
}
