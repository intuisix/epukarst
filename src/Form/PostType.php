<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => "Titre",
                'attr' => [
                    'placeholder' => "Titre"
                ],
                'required' => true,
            ])
            ->add('summary', TextType::class, [
                'label' => "Résumé",
                'attr' => [
                    'placeholder' => "Résumé (facultatif)"
                ],
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'label' => "Slug",
                'attr' => [
                    'placeholder' => "Sera généré automatiquement",
                ],
                'required' => false,
            ])
            ->add('parent', EntityType::class, [
                'label' => "Parent",
                'class' => Post::class,
                'choice_label' => 'title',
                'placeholder' => "Pas de parent",
                'required' => false,
            ])
            ->add('author', EntityType::class, [
                'label' => "Auteur",
                'class' => User::class,
                'choice_label' => 'displayName',
                'placeholder' => "Pas d'auteur",
                'required' => false,
            ])
            ->add('home', CheckboxType::class, [
                'label' => "Sur la page d'accueil",
                'required' => false,
            ])
            ->add('topMenu', CheckboxType::class, [
                'label' => "Sur le menu de navigation",
                'required' => false,
            ])
            ->add('position', ChoiceType::class, [
                'label' => "Position",
                'choices' => $options['positions'],
                'choice_label' => function($choice, $key, $value) {
                    return ($value + 1) . ". " . $key;
                },
                'placeholder' => "(en dernier)",
                'required' => false,
            ])
            ->add('date', DateTimeType::class, [
                'label' => "Date de rédaction",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => false,
            ])
            ->add('publishFromDate', DateTimeType::class, [
                'label' => "Début de publication",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => false,
                'required' => false,
            ])
            ->add('publishToDate', DateTimeType::class, [
                'label' => "Fin de publication",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'disabled' => false,
                'required' => false,
            ])
            ->add('content', CKEditorType::class, [
                'label' => "Contenu",
                'attr' => [
                    'placeholder' => "Contenu de l'article, tel qu'il devra être affiché.",
                    'rows' => 15,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Post::class,
            ])
            ->setRequired('positions')
            ->setAllowedTypes('positions', 'integer[]')
        ;
    }
}
