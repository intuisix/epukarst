<?php

namespace App\Form;

use App\Entity\StationKind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class StationKindType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Dénomination",
                'attr' => [
                    'placeholder' => "Entrez le nom du genre de station (p.ex. stalactite, résurgence, ...)",
                ],
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description (HTML)",
                'attr' => [
                    'placeholder' => "Entrez une définition du genre de station",
                    'rows' => 5,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StationKind::class,
        ]);
    }
}