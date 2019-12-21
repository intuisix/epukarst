<?php

namespace App\Controller;

use App\Entity\System;
use App\Entity\Station;
use App\Form\SystemStationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SystemStationController extends AbstractController
{
    /**
     * @Route("/system-station/{code}", name="system_station")
     */
    public function index(System $system)
    {
        return $this->render('system_station/index.html.twig', [
            'system' => $system,
            'title' => "Stations de {$system->getName()}",
        ]);
    }

    /**
     * @Route("/system-station/create/{code}", name="system_station_create")
     */
    public function create(System $system, ObjectManager $manager, Request $request)
    {
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

            return $this->redirectToRoute('system_station', [
                'code' => $system->getCode()
            ]);
        }

        return $this->render('system_station/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Ajouter une station à {$system->getName()}",
            'system' => $system,
        ]);
    }

    /**
     * @Route("/system-station/update/{code}", name="system_station_update")
     */
    public function update(Station $station, ObjectManager $manager, Request $request)
    {
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

            return $this->redirectToRoute('system_station', [
                'code' => $system->getCode()
            ]);
        }

        return $this->render('system_station/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier la station {$station->getName()}",
            'system' => $system,
        ]);
    }

    /**
     * @Route("/system-station/delete/{code}", name="system_station_delete")
     */
    public function delete(Station $station, ObjectManager $manager, Request $request)
    {
        $system = $station->getBasin()->getSystem();

        if (count($station->getReadings()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer la station <strong>{$station->getName()}</strong> car elle possède des relevés.");
        } else {
            /* Créer et traiter le formulaire de confirmation */
            $form = $this->createFormBuilder()->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Supprimer la station */
                $manager->remove($station);
                $manager->flush();

                $this->addFlash('success', "La station <strong>{$system->getName()}</strong> a été supprimée avec succès.");
            } else {
                return $this->render('system_station/delete.html.twig', [
                    'form' => $form->createView(),
                    'title' => "Supprimer la station {$station->getName()}",
                    'station' => $station,
                ]);
            }
        }

        return $this->redirectToRoute('system_station', [
            'code' => $system->getCode(),
        ]);
    }
}
