<?php

namespace App\Form;

use App\Entity\Basin;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
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
                    'placeholder' => "Entrez un code unique",
                ],
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'label' => "Nom",
                'attr' => [
                    'placeholder' => "Entrez la dénomination",
                ],
                'required' => true,
            ])
            ->add('description', CKEditorType::class, [
                'label' => "Description",
                'attr' => [
                    'placeholder' => "Entrez une description du bassin",
                ],
                'config' => [
                    'height' => 250,
                ],
                'required' => true,
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
