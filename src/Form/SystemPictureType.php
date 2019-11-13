<?php

namespace App\Form;

use App\Entity\SystemPicture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SystemPictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('caption', TextType::class, [
                'label' => "Légende de la photo",
                'attr' => [
                    'placeholder' => "Entrez une légende qui apparaîtra avec la photo"
                ],
            ])
            ->add('fileName', TextType::class, [
                'label' => "Nom du fichier",
                'disabled' => true,
                'attr' => [
                    'placeholder' => "Entrez le nom du fichier"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SystemPicture::class,
        ]);
    }
}
