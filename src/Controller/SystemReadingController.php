<?php

namespace App\Controller;

use App\Entity\System;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\SystemReading;
use App\Entity\SystemParameter;
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
            ->setSystem($system)
            ->setEncodingDateTime(new \DateTime('now'))
            ->setEncodingAuthor($this->getUser());

        /* Déterminer la liste des stations du système */
        $queryBuilder = $stationRepository->createQueryBuilder('s')
            ->innerJoin('s.basin', 'b')
            ->where('b.system = :system')
            ->setParameter('system', $system->getId());
        $stations = $queryBuilder->getQuery()->getResult();

        /* Déterminer la liste de paramètres d'instrument par défaut du relevé de système, à partir de la liste qui est associée au système */
        $systemParameters = $system->getParameters()->map(function(SystemParameter $p) {
            return $p->getInstrumentParameter();
        });

        /* Peupler les lignes du tableau d'encodage des mesures: pour toutes les stations du système, il faut ajouter au relevé de système un relevé de station contenant, pour chaque paramètre d'instrument, une valeur vide par défaut */
        foreach ($stations as $station) {
            /* Créer le relevé de station */
            $stationReading = new Reading();
            $stationReading->setStation($station);

            /* Pour chaque paramètre d'instrument, ajouter une nouvelle mesure au relevé de station */
            foreach ($systemParameters as $systemParameter) {
                $measure = new Measure();
                $measure
                    ->setMeasurability($systemParameter)
                    ->setValue(null)
                    ->setStable(true)
                    ->setValid(true);
                $stationReading->addMeasure($measure);
            }

            /* Ajouter la station au relevé */
            $systemReading->addStationReading($stationReading);
        }

        /* Créer le formulaire */
        $form = $this->createForm(SystemReadingType::class, $systemReading, [
            'stations' => $stations,
            'measurabilities' => $measurabilityRepository->findAll(),
        ]);

        /* Peupler les en-têtes de colonnes du tableau d'encodage des mesures: ce sont les paramètres d'instrument du relevé de système */
        $form->get('systemParameters')->setData($systemParameters);

        /* Etant donné que les champs du formulaire correspondant aux codes AKWA ne sont pas mappés car ils sont relatifs aux stations et non à leur relevé, il faut récupérer le code AKWA de chaque station pour l'inscrire dans le champ correspondant du formulaire */
        foreach ($form->get('stationReadings') as $child) {
            $stationReading = $child->getData();
            $station = $stationReading->getStation();
            $child->get('atlasCode')->setData($station->getAtlasCode());
        }

        /* Traiter le formulaire */
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Obtenir des informations du relevé de système */
            $fieldDateTime = $systemReading->getFieldDateTime();
            $encodingDateTime = $systemReading->getEncodingDateTime();
            $encodingAuthor = $systemReading->getEncodingAuthor();

            /* Traiter les relevés de station */
            foreach ($systemReading->getStationReadings() as $stationReading) {
                /* Définir les propriétés du relevé de station et l'associer au relevé de système */
                $stationReading
                    ->setFieldDateTime($fieldDateTime)
                    ->setEncodingDateTime($encodingDateTime)
                    ->setEncodingAuthor($encodingAuthor)
                    ->setSystemReading($systemReading);

                /* Supprimer les mesures sans valeur */
                foreach ($stationReading->getMeasures() as $measure) {
                    if (empty($measure->getValue())) {
                        $stationReading->removeMeasure($measure);
                    }
                }

                /* Traiter les autres mesures */
                foreach ($stationReading->getMeasures() as $measure) {
                    /* Définir les propriétés de la mesure et l'associer au relevé de station */
                    $measure
                        ->setFieldDateTime($fieldDateTime)
                        ->setEncodingDateTime($encodingDateTime)
                        ->setEncodingAuthor($encodingAuthor)
                        ->setReading($stationReading);

                    /* Persister la mesure */
                    $manager->persist($measure);
                }

                /* Persister le relevé de station */
                $manager->persist($stationReading);
            }

            $manager->persist($systemReading);
            $manager->flush();

            $this->addFlash('success', "Le relevé <strong>{$systemReading->getCode()}</strong> a été encodé avec succès.");

            return $this->redirectToRoute('reading');
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
