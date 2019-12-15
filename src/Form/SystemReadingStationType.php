<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\StationType;
use App\Entity\StationKind;
use App\Form\StationSimplifiedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SystemReadingStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
/*            ->add('station', EntityType::class, [
                'label' => "Station",
                'class' => Station::class,
                'choice_label' => 'name',
                'choices' => $options['stations'],
                'required' => true,
                'disabled' => true,
                'attr' => [
                    'placeholder' => 'Station',
                ],
            ])*/
            ->add(
                $builder->create('station', StationSimplifiedType::class, [
                    'basins' => $options['basins'],
//                    'disabled' => true,
                ])
            )
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
        $resolver
            ->setDefaults([
                'data_class' => Reading::class,
            ])
            ->setRequired('stations')
            ->setAllowedTypes('stations', 'App\Entity\Station[]')
            ->setRequired('basins')
            ->setAllowedTypes('basins', 'App\Entity\Basin[]');
    }
}
