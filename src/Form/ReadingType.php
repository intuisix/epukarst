<?php

namespace App\Form;

use App\Entity\Reading;
use App\Entity\Station;
use App\Form\CustomType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/* TODO: Remplacer la classe Reading par une classe différente, car il ne faut pas encoder la date de validation et les notes de validation */
class ReadingType extends CustomType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('station', EntityType::class, [
                'class' => Station::class,
                'choice_label' => function($station) {
                    return "{$station->getBasin()->getSystem()->getName()} : {$station->getName()}";
                }
            ])
            ->add('code', TextType::class, $this->getConfig("Code", "Ce code sera complété automatiquement", false))
            ->add('fieldDateTime', DateType::class, $this->getConfig("Date de terrain", "Entrez la date des mesures", true, [ 'widget' => 'single_text' ]))
            ->add('encodingNotes', TextareaType::class, $this->getConfig("Remarques de l'encodage", "Introduisez vos remarques concernant l'observation et/ou l'encodage", false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reading::class ]);
    }
}
