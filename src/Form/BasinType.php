<?php

namespace App\Form;

use App\Entity\Basin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BasinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => "Code identifiant",
                'attr' => [
                    'placeholder' => 'Entrez un code unique'
                ],
            ])
            ->add('name', TextType::class, [
                'label' => "Nom",
                'attr' => [
                    'placeholder' => 'Entrez une dénomination'
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'attr' => [
                    'placeholder' => 'Entrez une description détaillée',
                    'rows' => 5
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Basin::class,
        ]);
    }
}
