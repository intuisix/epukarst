<?php

namespace App\Form;

use App\Entity\Reading;
use App\Entity\Station;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SystemReadingStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('station', TextType::class, [
                'label' => "Station",
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'placeholder' => 'Dénomination',
                ],
            ])
            ->add('atlasCode', TextType::class, [
                'label' => "AKWA",
                'disabled' => true,
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => "N°",
                ],
            ])
            ->add('measures', CollectionType::class, [
                'label' => "Mesure",
                'entry_type' => SystemReadingMeasureType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reading::class,
        ]);
        $resolver->setRequired('stations');
        $resolver->setAllowedTypes('stations', 'App\Entity\Station[]');
    }
}
