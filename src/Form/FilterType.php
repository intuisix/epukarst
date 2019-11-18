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
            ]);

        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();
                    $this->addBasinsField($form, $data->getSystems());
                    $this->addStationsField($form, $data->getBasins());
                }
            )
            ->get('systems')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $parent = $form->getParent();
                    dump($parent);
                    $this->addBasinsField($parent, $form->getData());
                    $this->addStationsField($parent, $parent->get('basins')->getData());
                }
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
        ]);
    }

    private function addBasinsField($form, $systems = null) {
        $form
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
                            $qb->expr()->in(
                                'b.system',
                                $systems->map(function($system) {
                                    return $system->getId();
                                })->getValues()
                            )
                        );
                    }
                    return $qb->orderBy('b.name', 'ASC');
                }
            ]);
    }

    private function addStationsField($form, $basins = null) {
        $form
            ->add('stations', EntityType::class, [
                'choice_label' => 'name',
                'class' => Station::class,
                'expanded' => true,
                'label' => "Stations",
                'multiple' => true,
                'placeholder' => "Choisissez une station",
                'query_builder' => function(StationRepository $sr) use ($basins) {
                    dump($basins);
                    $qb = $sr->createQueryBuilder('s');
                    if (count($basins) == 0) {
                        $qb->where('s.basin is null');
                    } else {
/*                        $qb->where(
                            $qb->expr()->in(
                                's.basin',
                                $basins->map(function($basin) {
                                    return $basin->getId();
                                })->getValues()
                            )
                        );*/
                    }
                    return $qb->orderBy('s.name', 'ASC');
                },
                'required' => false,
            ]);
    }
}
