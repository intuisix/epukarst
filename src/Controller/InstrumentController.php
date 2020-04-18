<?php

namespace App\Controller;

use App\Entity\Instrument;
use App\Form\InstrumentType;
use App\Service\Breadcrumbs;
use App\Repository\InstrumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InstrumentController extends AbstractController
{
    /**
     * Affiche l'index des instruments.
     * 
     * @Route("/instrument", name="instrument")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(InstrumentRepository $instrumentRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des instruments");

        return $this->render('instrument/index.html.twig', [
            'instruments' => $instrumentRepository->findAll(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche et traite le formulaire de création d'instrument.
     * 
     * @Route("/instrument/create", name="instrument_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Créer un instrument");

        /* Instancier un nouvel instrument */
        $instrument = new Instrument();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(InstrumentType::class, $instrument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mémoriser l'instrument */
            $this->storeInstrument($instrument, $manager);
            
            $this->addFlash('success', "L'instrument <strong>{$instrument->getName()}</strong> a été créé avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('instrument/form.html.twig', [
            'instrument' => $instrument,
            'form' => $form->createView(),
            'title' => 'Ajouter un nouvel instrument',
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche et traite le formulaire de modification d'instrument.
     * 
     * @Route("/instrument/{id}/modify", name="instrument_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(Instrument $instrument, EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modifier un instrument");

        /* Créer et traiter le formulaire */
        $form = $this->createForm(InstrumentType::class, $instrument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mémoriser l'instrument */
            $this->storeInstrument($instrument, $manager);
            
            $this->addFlash('success', "L'instrument <strong>{$instrument->getName()}</strong> a été modifié avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('instrument/form.html.twig', [
            'instrument' => $instrument,
            'form' => $form->createView(),
            'title' => "Modifier l'instrument {$instrument->getName()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche et traite le formulaire de duplication d'instrument.
     * 
     * @Route("/instrument/{id}/duplicate", name="instrument_duplicate")
     * @IsGranted("ROLE_ADMIN")
     */
    public function duplicate(Instrument $original, EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Dupliquer un instrument");

        /* Dupliquer l'instrument original */
        $instrument = $original->duplicate();

        /* Créer et traiter le formulaire */
        $form = $this->createForm(InstrumentType::class, $instrument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mémoriser l'instrument */
            $this->storeInstrument($instrument, $manager);
            
            $this->addFlash('success', "L'instrument <strong>{$instrument->getName()}</strong> a été créé avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('instrument/form.html.twig', [
            'instrument' => $instrument,
            'form' => $form->createView(),
            'title' => "Dupliquer l'instrument {$instrument->getName()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la demande de suppression d'un instrument.
     *
     * @Route("/instrument/{id}/delete", name="instrument_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Instrument $instrument, EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Supprimer un instrument");

        if (count($instrument->getDerivedInstruments()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer l'instrument <strong>{$instrument->getName()}</strong> car il sert de modèle à d'autres instruments.");
        } else {
            /* Créer et traiter le formulaire de confirmation */
            $form = $this->createFormBuilder()->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /* Supprimer l'instrument */
                $manager->remove($instrument);
                $manager->flush();

                $this->addFlash('success', "L'instrument <strong>{$instrument->getName()}</strong> a été supprimé avec succès.");

                return $this->redirect($breadcrumbs->getPrevious('instrument'));
            }
    
            return $this->render('instrument/delete.html.twig', [
                'form' => $form->createView(),
                'instrument' => $instrument,
                'title' => "Supprimer l'instrument {$instrument->getName()}",
                'breadcrumbs' => $breadcrumbs,
            ]);
        }

        return $this->redirect($breadcrumbs->getPrevious());
    }

    /**
     * Mémorise l'instrument dans la base de données.
     *
     * @param Instrument $instrument
     * @param EntityManagerInterface $manager
     * @return void
     */
    private function storeInstrument(Instrument $instrument, EntityManagerInterface $manager)
    {
        /* Persister les étalonnages */
        foreach ($instrument->getCalibrations() as $calibration) {
            $calibration->setInstrument($instrument);
            $manager->persist($calibration);
        }

        /* Persister les paramètres */
        foreach ($instrument->getMeasurabilities() as $measurability) {
            $measurability->setInstrument($instrument);
            $manager->persist($measurability);
        }

        $manager->persist($instrument);
        $manager->flush();
    }
}
