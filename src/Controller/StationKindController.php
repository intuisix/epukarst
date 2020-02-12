<?php

namespace App\Controller;

use App\Entity\StationKind;
use App\Service\Breadcrumbs;
use App\Form\StationKindType;
use App\Repository\StationKindRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StationKindController extends AbstractController
{
    /**
     * @Route("/station-kind", name="station_kind")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(StationKindRepository $repository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des types de station");

        return $this->render('station_kind/index.html.twig', [
            'stationKinds' => $repository->findAll(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/station-kind/create", name="station_kind_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Création d'un type de station");

        /* Instancier un nouveau genre de station */
        $stationKind = new StationKind();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(StationKindType::class, $stationKind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($stationKind);
            $manager->flush();
            
            $this->addFlash('success', "Le genre de station <strong>{$stationKind->getName()}</strong> a été créé avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('station_kind/form.html.twig', [
            'stationKind' => $stationKind,
            'form' => $form->createView(),
            'title' => "Créer un nouveau genre de station",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/station-kind/{id}/modify", name="station_kind_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(StationKind $stationKind, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'un type de station");

        /* Créer et traiter le formulaire */
        $form = $this->createForm(StationKindType::class, $stationKind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($stationKind);
            $manager->flush();
            
            $this->addFlash('success', "Le genre de station <strong>{$stationKind->getName()}</strong> a été créé avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('station_kind/form.html.twig', [
            'stationKind' => $stationKind,
            'form' => $form->createView(),
            'title' => "Modifier le genre de station {$stationKind->getName()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/station-kind/{id}/delete", name="station_kind_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(StationKind $stationKind, ObjectManager $manager, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'un type de station");

        if (count($stationKind->getStations()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer le genre de station <strong>{$stationKind->getName()}</strong> car il est associé à des stations.");
        } else {
            $manager->remove($stationKind);
            $manager->flush();

            $this->addFlash('success', "Le genre de station <strong>{$stationKind->getName()}</strong> a été supprimé avec succès.");
        }

        return $this->redirect($breadcrumbs->getPrevious());
    }
}
