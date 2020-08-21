<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\Filter;
use App\Entity\System;
use App\Entity\Station;
use Doctrine\ORM\QueryBuilder;
use App\Form\FilterMeasureType;
use App\Repository\BasinRepository;
use App\Repository\StationRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Formulaire permettant de filtrer les relevés affichés.
 */
class FilterType extends AbstractType
{
    /**
     * Génère le formulaire.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('minimumDate', DateType::class, [
                'label' => "Entre",
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('maximumDate', DateType::class, [
                'label' => "et",
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('validated', CheckboxType::class, [
                'label' => "Validé",
                'data' => true,
                'required' => false,
            ])
            ->add('invalidated', CheckboxType::class, [
                'label' => "Invalidé",
                'data' => true,
                'required' => false,
            ])
            ->add('submitted', CheckboxType::class, [
                'label' => "Brouillon",
                'data' => true,
                'required' => false,
            ])
            ->add('systems', EntityType::class, [
                'choice_label' => 'name',
                'class' => System::class,
                'expanded' => true,
                'label' => "Systèmes",
                'multiple' => true,
                'required' => true,
            ])
            ->add('basins', EntityType::class, [
                'choice_label' => 'name',
                'class' => Basin::class,
                'label' => "Bassins",
                'multiple' => true,
                'required' => true,
                'expanded' => true,
            ])
            ->add('stations', EntityType::class, [
                'choice_label' => 'name',
                'class' => Station::class,
                'label' => "Stations",
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ])
            ->add('measures', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => FilterMeasureType::class,
                'label' => "Paramètres",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
        ]);
    }
}
