<?php

namespace App\Form;

use App\Entity\Alarm;
use App\Entity\Measure;
use App\Entity\Measurability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MeasureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fieldDateTime', DateTimeType::class, [
                'label' => "Date de terrain",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => true,
            ])
            ->add('encodingDateTime', DateTimeType::class, [
                'label' => "Date d'encodage",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => true,
            ])
            ->add('encodingAuthor', TextType::class, [
                'label' => "Auteur d'encodage",
                'disabled' => true,
            ])
            ->add('measurability', EntityType::class, [
                'label' => "Paramètre et instrument",
                'placeholder' => "Sélectionnez le paramètre et l'instrument",
                'class' => Measurability::class,
                'choice_label' => function(Measurability $measurability) {
                    return $measurability->getNameWithUnit();
                },
                'group_by' => function(Measurability $measurability) {
                    return $measurability->getParameter()->getName();
                },
            ])
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
                'label' => "Stable (exacte)",
                'required' => false,
            ])
            ->add('valid', CheckboxType::class, [
                'label' => "Valide (exploitable)",
                'required' => false,
            ])
            ->add('notes', TextareaType::class, [
                'label' => "Remarques",
                'attr' => [
                    'placeholder' => "Entrez vos remarques éventuelles" ],
                'required' => false,
            ])
            ->add('alarm', EntityType::class, [
                'label' => "Alarme",
                'class' => Alarm::class,
                'choice_label' => function(Alarm $alarm) {
                    $label = $alarm->getReportingDate()->format('d/m/Y');
                    $kind = $alarm->getKind();
                    if ($kind !== null) {
                        $label .= " : {$kind->getName()}";
                    }
                    return $label;
                },
                'group_by' => 'system.name',
                'placeholder' => "Mesure non liée à une alarme",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }
}
