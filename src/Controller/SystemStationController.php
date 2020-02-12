<?php

namespace App\Controller;

use App\Entity\System;
use App\Entity\Station;
use App\Service\Breadcrumbs;
use App\Form\SystemStationType;
use App\Repository\StationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SystemStationController extends AbstractController
{
    /**
     * @Route("/system-station/{code}", name="system_station")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="system")
     */
    public function index(System $system, StationRepository $stationRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Liste des stations du système");

        return $this->render('system_station/index.html.twig', [
            'system' => $system,
            'title' => "Stations de {$system->getName()}",
            'stations' => $stationRepository->findBySystem($system),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/system-station/create/{code}", name="system_station_create")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="system")
     */
    public function create(System $system, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Création d'une station");

        /* Créer une nouvelle instance de station */
        $station = new Station();

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemStationType::class, $station, [
            'basins' => $system->getBasins(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Persister la station dans la base de données */
            $manager->persist($station);
            $manager->flush();

            $this->addFlash('success', "La station <strong>{$station->getName()}</strong> a été créée avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('system_station/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Ajouter une station à {$system->getName()}",
            'system' => $system,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/system-station/modify/{code}", name="system_station_update")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="station")
     */
    public function modify(Station $station, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'une station");

        $system = $station->getBasin()->getSystem();

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemStationType::class, $station, [
            'basins' => $system->getBasins(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Persister la station dans la base de données */
            $manager->persist($station);
            $manager->flush();

            $this->addFlash('success', "La station <strong>{$station->getName()}</strong> a été modifiée avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('system_station/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier la station {$station->getName()}",
            'system' => $system,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/system-station/delete/{code}", name="system_station_delete")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="station")
     */
    public function delete(Station $station, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'une station");

        $system = $station->getBasin()->getSystem();

        if (count($station->getReadings()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer la station <strong>{$station->getName()}</strong> car elle possède des relevés.");
            return $this->redirect($breadcrumbs->getPrevious());
        } else {
            /* Créer et traiter le formulaire de confirmation */
            $form = $this->createFormBuilder()->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Supprimer la station */
                $manager->remove($station);
                $manager->flush();

                $this->addFlash('success', "La station <strong>{$system->getName()}</strong> a été supprimée avec succès.");

                return $this->redirect($breadcrumbs->getPrevious('station'));
            } else {
                return $this->render('system_station/delete.html.twig', [
                    'form' => $form->createView(),
                    'title' => "Supprimer la station {$station->getName()}",
                    'station' => $station,
                    'breadcrumbs' => $breadcrumbs,
                ]);
            }
        }
    }
}
