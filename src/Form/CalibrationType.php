<?php

namespace App\Form;

use App\Entity\Calibration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CalibrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('doneDate', DateType::class, [
                'label' => "Date du contrôle",
                'widget' => 'single_text',
            ])
            ->add('dueDate', DateType::class, [
                'label' => "Date d'expiration",
                'widget' => 'single_text',
            ])
            ->add('operatorName', TextType::class, [
                'label' => "Nom de l'opérateur",
                'attr' => [
                    'placeholder' => "Entrez le nom de la personne qui a procédé à l'étalonnage"
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => "Remarques",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez une éventuelle remarque",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Calibration::class,
        ]);
    }
}
