<?php

namespace App\Form;

use App\Entity\SystemPicture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SystemPictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $files = $options['files'];

        $builder
            ->add('caption', TextType::class, [
                'label' => "Légende de la photo",
                'attr' => [
                    'placeholder' => "Entrez une légende qui apparaîtra avec la photo"
                ],
            ])
            ->add('fileName', ChoiceType::class, [
                'label' => "Nom du fichier",
                'disabled' => false,
                'attr' => [
                    'placeholder' => "Entrez le nom du fichier"
                ],
                'choices' => $files,
                'choice_label' => function($key) use ($files) {
                    return $key;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SystemPicture::class,
            ])
            ->setRequired('files')
        ;
    }
}
