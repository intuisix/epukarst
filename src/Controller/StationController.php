<?php

namespace App\Controller;

use App\Entity\Station;
use App\Form\StationType;
use App\Repository\StationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StationController extends AbstractController
{
    /**
     * @Route("/station", name="station")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(StationRepository $stationRepository)
    {
        return $this->render('station/index.html.twig', [
            'stations' => $stationRepository->findAll()
        ]);
    }

    /**
     * Affiche et traite le formulaire d'ajout d'une station.
     * 
     * @Route("/station/create", name="station_create")
     * @IsGranted("ROLE_ADMIN")
     *
     * @return Response
     */
    public function create(ObjectManager $manager, Request $request)
    {
        /* Instancier une nouvelle station */
        $station = new Station();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(StationType::class, $station);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($station);
            $manager->flush();
            
            $this->addFlash('success', "La station <strong>{$station->getName()}</strong> a été créée avec succès.");
    
            return $this->redirectToRoute('station');
        }

        return $this->render('station/create.html.twig', [
            'station' => $station,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche et traite le formulaire de modification d'une station.
     * 
     * @Route("/station/{code}/modify", name="station_modify")
     * @IsGranted("ROLE_ADMIN")
     *
     * @return Response
     */
    public function modify(Station $station, ObjectManager $manager, Request $request)
    {
        /* Créer et traiter le formulaire */
        $form = $this->createForm(StationType::class, $station);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($station);
            $manager->flush();
            
            $this->addFlash('success', "La station <strong>{$station->getName()}</strong> a été modifiée avec succès.");
    
            return $this->redirectToRoute('station');
        }

        return $this->render('station/create.html.twig', [
            'station' => $station,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Traite la suppression d'une station.
     * 
     * @Route("/station/{code}/delete", name="station_delete")
     * @IsGranted("ROLE_ADMIN")
     *
     * @return Response
     */
    public function delete(Station $station, ObjectManager $manager, Request $request)
    {
        if (count($station->getReadings()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer la station <strong>{$station->getName()}</strong> car elle possède déjà des relevés.");
        } else {
            $manager->remove($station);
            $manager->flush();
            $this->addFlash('success', "La station <strong>{$station->getName()}</strong> a été supprimée avec succès.");
        }
        return $this->redirectToRoute('station');
    }
}
