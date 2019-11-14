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

class FilterType extends AbstractType
{
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
//            ->add('name')
/*            ->add('systems', CollectionType::class, [
                'label' => "Systèmes",
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => System::class,
                    'choice_label' => 'name',
                    'placeholder' => "Choisissez un système",
                    'required' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
            ]) */
            ->add('systems', EntityType::class, [
                'choice_label' => 'name',
                'class' => System::class,
                'expanded' => true,
                'label' => "Systèmes",
                'multiple' => true,
                'placeholder' => "Choisissez un système",
                'required' => false,
            ])
/*            ->add('basins', EntityType::class, [
                'choice_label' => 'name',
                'class' => Basin::class,
                'expanded' => true,
                'label' => "Bassins",
                'multiple' => true,
                'placeholder' => "Choisissez un bassin",
                'query_builder' => function(BasinRepository $br) {
                    return $br->createQueryBuilder('b')
                        ->orderBy('b.name', 'ASC');
                },
                'required' => false,
            ])
            ->add('stations', EntityType::class, [
                'choice_label' => 'name',
                'class' => Station::class,
                'expanded' => true,
                'label' => "Stations",
                'multiple' => true,
                'placeholder' => "Choisissez une station",
                'query_builder' => function(StationRepository $sr) {
                    return $sr->createQueryBuilder('s')
                        ->orderBy('s.name', 'ASC');
                },
                'required' => false,
            ])*/
        ;

        $formModifier = function($form, $systems = null) {
            $form->add('basins', EntityType::class, [
                'choice_label' => 'name',
                'class' => Basin::class,
                'multiple' => true,
                'required' => false,
                'expanded' => true,
                'query_builder' => function(BasinRepository $br) use ($systems) {
                    $qb = $br->createQueryBuilder('b');
                    if (count($systems) > 0) {
                        $qb->where(
                            $qb->expr()->in(
                                'b.system',
                                $systems->map(function($system) {
                                    return $system->getId();
                                })->getValues()
                            ));
                    }
                    return $qb->orderBy('b.name', 'ASC');
                }
            ]);
        };

        $builder
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    $data = $event->getData();
                    $formModifier($event->getForm(), $data->getSystems());
                }
            )
            ->get('systems')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    $systems = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $systems);
                }
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
        ]);
    }
}
