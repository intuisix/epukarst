<?php

namespace App\Form;

use App\Entity\Measurability;
use App\Entity\SystemParameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SystemParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('instrumentParameter', EntityType::class, [
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
            ->add('notes', TextareaType::class, [
                'label' => "Remarques",
                'attr' => [
                    'placeholder' => "Entrez éventuellement des remarques",
                    'rows' => 3,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SystemParameter::class,
        ]);
    }
}
