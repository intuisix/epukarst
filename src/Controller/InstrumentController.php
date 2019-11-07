<?php

namespace App\Controller;

use App\Entity\Instrument;
use App\Form\InstrumentType;
use App\Repository\InstrumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
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
    public function index(InstrumentRepository $instrumentRepository)
    {
        return $this->render('instrument/index.html.twig', [
            'instruments' => $instrumentRepository->findAll()
        ]);
    }

    /**
     * Affiche et traite le formulaire de création d'instrument.
     * 
     * @Route("/instrument/create", name="instrument_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request) {
        /* Instancier un nouvel instrument */
        $instrument = new Instrument();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(InstrumentType::class, $instrument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($instrument->getCalibrations() as $calibration) {
                $calibration->setInstrument($instrument);
                $manager->persist($calibration);
            }

            foreach ($instrument->getMeasurabilities() as $measurability) {
                $measurability->setInstrument($instrument);
                $manager->persist($measurability);
            }

            $manager->persist($instrument);
            $manager->flush();
            
            $this->addFlash('success', "L'instrument <strong>{$instrument->getName()}</strong> a été créé avec succès.");
    
            return $this->redirectToRoute('instrument');
        }

        return $this->render('instrument/create.html.twig', [
            'instrument' => $instrument,
            'form' => $form->createView()
        ]);
    }

    /**
     * Affiche et traite le formulaire de modification d'instrument.
     * 
     * @Route("/instrument/{code}/modify", name="instrument_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(Instrument $instrument, ObjectManager $manager, Request $request) {
        /* Créer et traiter le formulaire */
        $form = $this->createForm(InstrumentType::class, $instrument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($instrument->getCalibrations() as $calibration) {
                $calibration->setInstrument($instrument);
                $manager->persist($calibration);
            }

            foreach ($instrument->getMeasurabilities() as $measurability) {
                $measurability->setInstrument($instrument);
                $manager->persist($measurability);
            }

            $manager->persist($instrument);
            $manager->flush();
            
            $this->addFlash('success', "L'instrument <strong>{$instrument->getName()}</strong> a été modifié avec succès.");
    
            return $this->redirectToRoute('instrument');
        }

        return $this->render('instrument/modify.html.twig', [
            'instrument' => $instrument,
            'form' => $form->createView()
        ]);
    }

    /**
     * Traite la demande de suppression d'un instrument.
     *
     * @Route("/instrument/{code}/delete", name="instrument_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Instrument $instrument, ObjectManager $manager) {
        $manager->remove($instrument);
        $manager->flush();

        $this->addFlash('success', "L'instrument <strong>{$instrument->getName()}</strong> a été supprimé avec succès.");

        return $this->redirectToRoute('instrument');
    }
}
