<?php

namespace App\Form;

use App\Entity\Parameter;
use App\Entity\Measurability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MeasurabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameter', EntityType::class, [
                'label' => "Paramètre",
                'class' => Parameter::class,
                'choice_label' => function(Parameter $parameter) {
                    $unit = $parameter->getUnit();
                    return $parameter->getName() .
                        (empty($unit) ? "" : " ($unit)");
                },
                'placeholder' => "Sélectonnez un paramètre",
            ])
            ->add('minimumValue', NumberType::class, [
                'label' => "Valeur maximum",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la valeur minimum mesurable",
                ],
            ])
            ->add('maximumValue', NumberType::class, [
                'label' => "Valeur maximum",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la valeur maximum mesurable",
                ],
            ])
            ->add('tolerance', NumberType::class, [
                'label' => "Tolérance",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez la précision des mesures",
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => "Remarques",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez des remarques éventuelles",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measurability::class,
        ]);
    }
}
