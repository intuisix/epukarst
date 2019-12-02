<?php

namespace App\Controller;

use App\Entity\System;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\SystemReading;
use App\Form\SystemReadingType;
use App\Repository\StationRepository;
use App\Repository\MeasurabilityRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SystemReadingController extends AbstractController
{
    /**
     * @Route("/system-reading", name="system_reading")
     */
    public function index()
    {
        return $this->render('system_reading/index.html.twig', [
        ]);
    }

    /**
     * Gère l'encodage d'un nouveau relevé pour un système.
     * 
     * @Route("/system-reading/encode/{code}", name="system_reading_encode")
     * @IsGranted("ROLE_USER")
     */
    public function encode(System $system, ObjectManager $manager, Request $request, StationRepository $stationRepository, MeasurabilityRepository $measurabilityRepository)
    {
        /* Instancier un nouveau relevé */
        $systemReading = new SystemReading();
        $systemReading
            ->setEncodingAuthor($this->getUser())
            ->setEncodingDateTime(new \DateTime('now'))
            ->setSystem($system)
            ->setCode(uniqid())
        ;

        /* Obtenir la liste des stations du système */
        $queryBuilder = $stationRepository->createQueryBuilder('s')
            ->innerJoin('s.basin', 'b')
            ->where('b.system = :system')
            ->setParameter('system', $system->getId());
        $stations = $queryBuilder->getQuery()->getResult();

        $i = 0;

        /* Ajouter toutes les stations du système au relevé */
        foreach ($stations as $station) {
            $stationReading = new Reading();
            $stationReading->setStation($station);

            foreach ($system->getParameters() as $systemParameter) {
                $measure = new Measure();
                $measure->setMeasurability($systemParameter->getInstrumentParameter());
                $measure->setValue(++$i);  // Pour tester
                $stationReading->addMeasure($measure);
            }

            $systemReading->addStationReading($stationReading);
        }

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemReadingType::class, $systemReading, [
            'stations' => $stations,
            'measurabilities' => $measurabilityRepository->findAll()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($systemReading);
    //        $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$systemReading->getCode()}</strong> a été encodé avec succès.");
    
     //       return $this->redirectToRoute('system_reading_show', [
      //          'code' => $systemReading->getCode(),
        //    ]);
        }

        return $this->render('system_reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Encoder un nouveau relevé de système",
        ]);
    }

    /**
     * @Route("/system-reading/{code}", name="system_reading_show")
     */
    public function show(SystemReading $reading)
    {
        dump($reading);
        return $this->render('system_reading/index.html.twig', [
            ]);
    }
}
