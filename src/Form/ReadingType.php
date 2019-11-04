<?php

namespace App\Form;

use App\Entity\Reading;
use App\Form\CustomType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/* TODO: Remplacer la classe Reading par une classe différente, car il ne faut pas encoder la date de validation et les notes de validation */
class ReadingType extends CustomType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, $this->getConfig("Code", "Ce code sera complété automatiquement"))
            ->add('encodingDateTime', DateTimeType::class, $this->getConfig("Date de l'encodage", ""))
            ->add('validationDateTime', DateTimeType::class, $this->getConfig("Date de la validation", ""))
            ->add('encodingNotes', TextareaType::class, $this->getConfig("Remarques de l'encodage", "Introduisez vos remarques concernant l'observation et/ou l'encodage"))
            ->add('validationNotes', TextareaType::class, $this->getConfig("Remarques de la validation", "Introduisez vos remarques concernant la validation"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reading::class,
        ]);
    }
}
