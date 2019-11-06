<?php

namespace App\Form;

use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\MeasureType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/* TODO: Remplacer la classe Reading par une classe différente, car il ne faut pas encoder la date de validation et les notes de validation */
class ReadingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('station', EntityType::class, [
                'label' => 'Station',
                'class' => Station::class,
                'placeholder' => 'Sélectionnez une station',
                'choice_label' => function(Station $station) {
                    return $station->getBasin()->getName(). " : " . $station->getName();
                },
                'group_by' => function(Station $station) {
                    return $station->getBasin()->getSystem()->getName();
                }
            ])
            ->add('code', TextType::class, [
                'label' => "Code",
                'required' => false,
                'attr' => [
                    'placeholder' => "Ce code sera complété automatiquement" ],
            ])
            ->add('fieldDateTime', DateType::class, [
                'label' => "Date de terrain",
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => "Entrez la date des mesures" ],
            ])
            ->add('encodingNotes', TextareaType::class, [
                'label' => "Remarques de l'encodage",
                'required' => false,
                'attr' => [
                    'placeholder' => "Introduisez vos remarques concernant l'observation et/ou l'encodage" ],
            ])
            ->add('measures', CollectionType::class, [
                'label' => "Mesures",
                'entry_type' => MeasureType::class,
                'allow_add' => true,
                'allow_delete' => true ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reading::class,
        ]);
    }
}
