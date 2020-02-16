<?php

namespace App\Form;

use App\Entity\Attachment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class AttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom",
            ])
            ->add('mimeType', TextType::class, [
                'label' => "Type",
                'disabled' => true,
            ])
            ->add('uploadDateTime', DateTimeType::class, [
                'label' => "Date et heure",
                'disabled' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('uploadAuthor', TextType::class, [
                'label' => "Auteur",
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Attachment::class,
        ]);
    }
}
