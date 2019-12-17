<?php

namespace App\Form;

use App\Entity\System;
use App\Form\SystemRoleType;
use App\Form\SystemPictureType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SystemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => "Code identifiant",
                'attr' => [
                    'placeholder' => "Entrez un code unique"
                ]
            ])
            ->add('name', TextType::class, [
                'label' => "Dénomination",
                'attr' => [
                    'placeholder' => "Entrez le nom du système"
                ]
            ])
            ->add('commune', TextType::class, [
                'label' => "Commune",
                'attr' => [
                    'placeholder' => "Entrez le nom de la commune"
                ]
            ])
            ->add('basin', TextType::class, [
                'label' => "Bassin versant",
                'attr' => [
                    'placeholder' => "Entrez le nom du bassin dans lequel se déverse le système"
                ]
            ])
            ->add('waterMass', TextType::class, [
                'label' => "Code de masse d'eau",
                'attr' => [
                    'placeholder' => "Entrez le code de masse d'eau (facultatif)"
                ],
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'label' => "Slug pour l'URL",
                'required' => false,
                'attr' => [
                    'placeholder' => "Si vous laissez ce champ vide, le slug sera attribué automatiquement"
                ]
            ])
            ->add('introduction', TextType::class, [
                'label' => "Introduction générale",
                'attr' => [
                    'placeholder' => "Entrez une introduction générale, qui sera affichée juste en-dessous du nom du système"
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description détaillée (HTML)",
                'attr' => [
                    'rows' => 8,
                    'placeholder' => "En utilisant le format HTML, entrez une description détaillée qui permettra aux visiteurs de découvrir les particularités du système et les raisons pour lesquelles il est étudié"
                ],
                'help' => "En HTML, chaque paragraphe est commencé par <p> et terminé par </p>. Pour mettre un texte en gras, entourez-le de <b> et </b>. Pour les italiques, c'est <i> et </i>. Les hyperliens sont créés avec <a href=\"url-référencé\"> et </a>.",
            ])
            ->add('pictures', CollectionType::class, [
                'label' => 'Photographies',
                'entry_type' => SystemPictureType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => [
                    'files' => $options['picture_files'],
                ]
            ])
            ->add('newPictures', FileType::class, [
                'label' => "Ajouter des photos",
                'help' => "Utilisez ce contrôle pour charger une ou plusieurs photos qui seront ajoutées au système. Ensuite, enregistrez le système, et vous serez amené à introduire les légendes de chacune des photos ajoutées.",
                'attr' => [
                    'placeholder' => "Choisissez un ou plusieurs fichiers (JPEG ou PNG, maximum 1 Mo chacun)",
                ],
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                            ],
                            'mimeTypesMessage' => "Les fichiers doivent être au format JPEG ou PNG",
                        ])
                    ])
                ]
            ])
            ->add('basins', CollectionType::class, [
                'label' => "Bassins",
                'entry_type' => BasinType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('parameters', CollectionType::class, [
                'label' => "Paramètres",
                'entry_type' => SystemParameterType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('userRoles', CollectionType::class, [
                'label' => "Rôles",
                'entry_type' => SystemRoleType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => System::class,
            ])
            ->setRequired('picture_files');
    }
}
