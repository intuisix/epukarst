<?php

namespace App\Form;

use App\Entity\Basin;
use App\Entity\Filter;
use App\Entity\System;
use App\Entity\Station;
use Doctrine\ORM\QueryBuilder;
use App\Repository\BasinRepository;
use App\Repository\StationRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
        //$filter = $options['filter'];
        //$systems = $filter->getSystems();
        //$basins = $filter->getBasins();
        $systems = $options['systems'];
        $basins = $options['basins'];

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
            ->add('systems', EntityType::class, [
                'choice_label' => 'name',
                'class' => System::class,
                'expanded' => true,
                'label' => "Systèmes",
                'multiple' => true,
                'placeholder' => "Choisissez un système",
                'required' => false,
            ])
            ->add('basins', EntityType::class, [
                'choice_label' => 'name',
                'class' => Basin::class,
                'multiple' => true,
                'required' => false,
                'expanded' => true,
                'query_builder' => function(BasinRepository $br) use ($systems) {
                    $qb = $br->createQueryBuilder('b');
                    if (count($systems) == 0) {
                        $qb->where('b.system is null');
                    } else {
                        $qb->where(
                            $qb->expr()->in('b.system', $systems)
                        );
                    }
                    return $qb->orderBy('b.name', 'ASC');
                }
            ])
            ->add('stations', EntityType::class, [
                'choice_label' => 'name',
                'class' => Station::class,
                'expanded' => true,
                'label' => "Stations",
                'multiple' => true,
                'placeholder' => "Choisissez une station",
                'query_builder' => function(StationRepository $sr) use ($basins) {
                    $qb = $sr->createQueryBuilder('s');
                    if (count($basins) == 0) {
                        $qb->where('s.basin is null');
                    } else {
                        $qb->where(
                            $qb->expr()->in('s.basin', $basins)
                        );
                    }
                    return $qb->orderBy('s.name', 'ASC');
                },
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'systems' => [],
            'basins' => [],
            //'filter' => new Filter(),
        ]);
        $resolver->setAllowedTypes('systems', 'string[]');
        $resolver->setAllowedTypes('basins', 'string[]');
        //$resolver->setAllowedTypes('filter', 'App\Entity\Filter');
    }
}
