<?php

namespace App\Form;

use App\Entity\Measure;
use App\Entity\Measurability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MeasureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', NumberType::class, [
                'label' => "Valeur",
                'attr' => [
                    'placeholder' => "Entrez la valeur" ],
            ])
            ->add('tolerance', NumberType::class, [
                'label' => "Tolérance",
                'attr' => [
                    'placeholder' => "Entrez la tolérance éventuelle" ],
                'required' => false,
            ])
            ->add('stable', CheckboxType::class, [
                'label' => "Stable (lue correctement)",
                'data' => true,
                'required' => false,
            ])
            ->add('valid', CheckboxType::class, [
                'label' => "Valide (exploitable)",
                'data' => true,
                'required' => false,
            ])
            ->add('notes', TextareaType::class, [
                'label' => "Remarques",
                'attr' => [
                    'placeholder' => "Entrez vos remarques éventuelles" ],
                'required' => false,
            ])
            ->add('measurability', EntityType::class, [
                'label' => "Paramètre et instrument",
                'placeholder' => "Sélectionnez le paramètre et l'instrument",
                'class' => Measurability::class,
                'choice_label' => function(Measurability $measurability) {
                    $parameter = $measurability->getParameter();
                    $unit = $parameter->getUnit();
                    return
                        $parameter->getName() . " : " .
                        $measurability->getInstrument()->getName() .
                        (empty($unit) ? "" : " ($unit)");
                },
                'group_by' => function(Measurability $measurability) {
                    return $measurability->getParameter()->getName();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }
}
