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
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Formulaire permettant la saisie de l'heure, des mesures et des remarques
 * d'un relevé de station contenu dans un relevé de système.
 */
class SystemReadingStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fieldDateTime', TimeType::class, [
                'label' => "Heure",
                'widget' => 'single_text',
            ])
            ->add('measures', CollectionType::class, [
                'label' => "Mesure",
                'entry_type' => SystemReadingMeasureType::class,
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('encodingNotes', TextareaType::class, [
                'label' => "Remarques de l'encodage",
                'required' => false,
                'attr' => [
                    'placeholder' => "Entrez vos remarques propres à cette station",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Reading::class,
            ])
        ;
    }
}
