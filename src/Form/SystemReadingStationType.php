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
/*            ->add('code')
            ->add('fieldDateTime')
            ->add('encodingDateTime')
            ->add('encodingNotes')
            ->add('validationDateTime')
            ->add('validationNotes')
            ->add('validated')*/
/*            ->add('station', EntityType::class, [
                'label' => "Station",
                'class' => Station::class,
                'choices' => $options['stations'],
                'choice_label' => 'name',
            ])*/
            ->add('station', TextType::class, [
                'label' => "Station",
                'required' => true,
                'disabled' => true,
            ])
/*            ->add('encodingAuthor')
            ->add('validationAuthor')
            ->add('systemReading')*/
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
