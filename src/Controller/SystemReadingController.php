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
     * Gère l'encodage d'un nouveau relevé pour un système.
     * 
     * @Route("/system-reading/encode/{code}", name="system_reading_encode")
     * @IsGranted("ROLE_USER")
     */
    public function encode(System $system, ObjectManager $manager, Request $request, StationRepository $stationRepository, MeasurabilityRepository $measurabilityRepository, BasinRepository $basinRepository, StationKindRepository $stationKindRepository)
    {
        /* Obtenir la liste des stations du système */
        $queryBuilder = $stationRepository->createQueryBuilder('s')
            ->innerJoin('s.basin', 'b')
            ->where('b.system = :system')
            ->setParameter('system', $system->getId());
        $systemStations = $queryBuilder->getQuery()->getResult();

        if (empty($systemStations)) {
            $this->addFlash('danger', "Il n'est pas possible d'encoder de mesures pour l'instant, car aucune station n'a été ajoutée au système <strong>{$system->getName()}</strong>.");
            return $this->redirectToRoute('reading');
        }

        /* Obtenir la liste de paramètres du système */
        $systemParameters = $system->getParameters()->map(function(SystemParameter $p) {
            return $p->getInstrumentParameter();
        });

        if (empty($systemParameters)) {
            $this->addFlash('danger', "Il n'est pas possible d'encoder de mesures pour l'instant, car aucun paramètre n'a encore été attribué au système <strong>{$system->getName()}</strong>.");
            return $this->redirectToRoute('reading');
        }

        /* Instancier un nouveau relevé de système */
        $systemReading = new SystemReading();
        $systemReading
            ->setSystem($system)
            ->setEncodingDateTime(new \DateTime('now'))
            ->setEncodingAuthor($this->getUser());

        /* Pour chaque station du système, ajouter un relevé de station à celui du système */
        foreach ($systemStations as $station) {
            /* Créer le relevé de station */
            $stationReading = new Reading();
            $stationReading->setStation($station);

            /* Pour chaque paramètre, ajouter une nouvelle mesure au relevé de station */
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

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemReadingType::class, $systemReading, [
            'showEncoding' => true,
            'showValidation' => false,
        ]);
        $form->handleRequest($request);

        /* Vérifier le soumission et la validité du formulaire */
        if ($form->isSubmitted() && $form->isValid()) {
            /* Obtenir des informations du relevé de système */
            $fieldDateTime = $systemReading->getFieldDateTime();
            $encodingDateTime = $systemReading->getEncodingDateTime();
            $encodingAuthor = $systemReading->getEncodingAuthor();

            /* Traiter chacun des relevés de station */
            foreach ($systemReading->getStationReadings() as $stationReading) {
                /* Supprimer les mesures pour lesquelles aucune valeur n'a été encodée */
                foreach ($stationReading->getMeasures() as $measure) {
                    if (empty($measure->getValue())) {
                        $stationReading->removeMeasure($measure);
                    }
                }

                /* Traiter les mesures restantes */
                $measures = $stationReading->getMeasures();
                if (0 != $measures->count()) {
                    /* Définir les propriétés de chaque mesure et persister ces dernières dans la base de données */
                    foreach ($measures as $measure) {
                        $measure
                            ->setFieldDateTime($fieldDateTime)
                            ->setEncodingDateTime($encodingDateTime)
                            ->setEncodingAuthor($encodingAuthor)
                            ->setReading($stationReading);
                        $manager->persist($measure);
                    }

                    /* Définir les propriétés du relevé de station et la persister dans la base de données */
                    $stationReading
                        ->setFieldDateTime($fieldDateTime)
                        ->setEncodingDateTime($encodingDateTime)
                        ->setEncodingAuthor($encodingAuthor)
                        ->setSystemReading($systemReading);
                    $manager->persist($stationReading);
                } else {
                    /* Enlever le relevé de la station car il est vide */
                    $systemReading->removeStationReading($stationReading);
                }
            }

            /* Persister le relevé de système dans la base de données */
            $manager->persist($systemReading);
            $manager->flush();

            $this->addFlash('success', "Le relevé <strong>{$systemReading->getCode()}</strong> a été encodé avec succès.");

            return $this->redirectToRoute('reading');
        }

        return $this->render('system_reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Encoder un nouveau relevé de système",
            'system' => $system,
            'systemParameters' => $systemParameters,
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
