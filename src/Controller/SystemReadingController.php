<?php

namespace App\Controller;

use App\Entity\System;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\SystemReading;
use App\Entity\SystemParameter;
use App\Form\SystemReadingType;
use App\Service\PaginationService;
use App\Repository\BasinRepository;
use App\Repository\SystemRepository;
use App\Repository\StationRepository;
use App\Repository\ParameterRepository;
use App\Repository\StationKindRepository;
use App\Repository\MeasurabilityRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SystemReadingController extends AbstractController
{
    /**
     * @Route("/system-reading/{page<\d+>?1}", name="system_reading")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(int $page, PaginationService $pagination, SystemRepository $systemRepository)
    {
        $pagination
            ->setEntityClass(SystemReading::class)
            ->setOrderBy(['fieldDateTime' => 'DESC'])
            ->setPage($page)
        ;

        return $this->render('system_reading/index.html.twig', [
            'pagination' => $pagination,
            'systems' => $systemRepository->findAll(),
        ]);
    }

    /**
     * Crée et ajoute au relevé de système un relevé correspondant à la station
     * donnée et, pour chaque paramètre mesurable, une valeur de mesure vide par
     * défaut.
     *
     * @param [type] $station
     * @param [type] $systemReading
     * @param [type] $systemParameters
     * @return void
     */
    private function appendStationReadingTemplate($station, SystemReading $systemReading, $systemParameters)
    {
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

    /**
     * Gère l'encodage d'un nouveau relevé pour un système.
     * 
     * @Route("/system-reading/encode/{code}", name="system_reading_encode")
     * @IsGranted("ROLE_USER")
     */
    public function encode(System $system, ObjectManager $manager, Request $request, StationRepository $stationRepository, MeasurabilityRepository $measurabilityRepository, BasinRepository $basinRepository, StationKindRepository $stationKindRepository)
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

        /* Ajoute au relevé de système un relevé pour chaque station */
        foreach ($stations as $station) {
            $this->appendStationReadingTemplate($station, $systemReading, $systemParameters);
        }

        /* Créer le formulaire */
        $form = $this->createForm(SystemReadingType::class, $systemReading, [
            'stations' => $stations,
            'basins' => $system->getBasins()->toArray(),
            'measurabilities' => $measurabilityRepository->findAll(),
        ]);

        /* Peupler les en-têtes de colonnes du tableau d'encodage des mesures: ce sont les paramètres d'instrument du relevé de système */
        $form->get('systemParameters')->setData($systemParameters);

        /* Récupérer les valeurs des champs non mappés */
        foreach ($form->get('stationReadings') as $child) {
            $stationReading = $child->getData();
            $station = $stationReading->getStation();
            $child->get('name')->setData($station->getName());
            $child->get('atlasCode')->setData($station->getAtlasCode());
            $child->get('basin')->setData($station->getBasin());
            $child->get('kind')->setData($station->getKind());
            $child->get('description')->setData($station->getDescription());
        }

        /* Créer les éventuelles stations à créer */
        if ($request->request->has('system_reading')) {
            foreach ($request->request->get('system_reading')['stationReadings'] as $requestKey => $requestParam) {
                if (array_key_exists('name', $requestParam)) {
                    /* Créer la station */
                    $station = new Station();
                    $station
                        ->setName($requestParam['name'])
                        ->setAtlasCode($requestParam['atlasCode'])
                        ->setBasin($basinRepository->findOneById($requestParam['basin']))
                        ->setKind($stationKindRepository->findOneById($requestParam['kind']))
                        ->setDescription($requestParam['description']);
                    $manager->persist($station);

                    /* Ajouter au relevé de système un relevé de la station */
                    $this->appendStationReadingTemplate($station, $systemReading, $systemParameters);
                }
            }
        }

        /* Traiter le formulaire */
        $form->handleRequest($request);

        /* Tester la validité du formulaire */
        if ($form->isSubmitted() && $form->isValid()) {
            /* Obtenir des informations du relevé de système */
            $fieldDateTime = $systemReading->getFieldDateTime();
            $encodingDateTime = $systemReading->getEncodingDateTime();
            $encodingAuthor = $systemReading->getEncodingAuthor();

            /* Traiter les relevés de station */
            foreach ($systemReading->getStationReadings() as $stationReading) {
                /* Supprimer les mesures sans valeur */
                foreach ($stationReading->getMeasures() as $measure) {
                    if (empty($measure->getValue())) {
                        $stationReading->removeMeasure($measure);
                    }
                }

                /* Traiter les mesures restantes */
                $measureCount = 0;
                foreach ($stationReading->getMeasures() as $measure) {
                    ++$measureCount;
                    /* Définir les propriétés de la mesure et l'associer au relevé de station */
                    $measure
                        ->setFieldDateTime($fieldDateTime)
                        ->setEncodingDateTime($encodingDateTime)
                        ->setEncodingAuthor($encodingAuthor)
                        ->setReading($stationReading);
                    /* Persister la mesure */
                    $manager->persist($measure);
                }

                if (0 < $measureCount) {
                    /* Définir les propriétés du relevé de station et l'associer au relevé de système */
                    $stationReading
                        ->setFieldDateTime($fieldDateTime)
                        ->setEncodingDateTime($encodingDateTime)
                        ->setEncodingAuthor($encodingAuthor)
                        ->setSystemReading($systemReading);
                    /* Persister le relevé de station */
                    $manager->persist($stationReading);
                } else {
                    /* Enlever le relevé de la station car il est vide */
                    $systemReading->removeStationReading($stationReading);
                }
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
     * Traite la suppression d'un relevé de système.
     *
     * @Route("system-reading/{code}/delete", name="system_reading_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(SystemReading $systemReading, Request $request, ObjectManager $manager)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->remove($systemReading);
            $manager->flush();
    
            $this->addFlash('success', "Le relevé <strong>{$systemReading->getCode()}</strong> a été supprimé avec succès.");
    
            return $this->redirectToRoute('system_reading');
        }

        return $this->render('system_reading/delete.html.twig', [
            'form' => $form->createView(),
            'systemReading' => $systemReading,
            'title' => "Supprimer le relevé $systemReading",
        ]);
    }

    /**
     * @Route("/system-reading/{code}", name="system_reading_show")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(SystemReading $systemReading, ParameterRepository $parameterRepository)
    {
        return $this->render('system_reading/show.html.twig', [
            'systemReading' => $systemReading,
            'parameters' => $parameterRepository->findBy(
                [ 'favorite' => true ], [ 'name' => 'ASC']),
        ]);
    }
}
