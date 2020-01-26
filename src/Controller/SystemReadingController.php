<?php

namespace App\Controller;

use App\Entity\Alarm;
use App\Entity\System;
use App\Entity\Control;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\SystemReading;
use App\Entity\SystemParameter;
use App\Form\SystemReadingType;
use FormulaParser\FormulaParser;
use App\Service\PaginationService;
use App\Repository\BasinRepository;
use App\Repository\SystemRepository;
use App\Repository\StationRepository;
use App\Repository\ParameterRepository;
use App\Repository\StationKindRepository;
use App\Repository\MeasurabilityRepository;
use App\Repository\SystemParameterRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SystemReadingController extends AbstractController
{
    /**
     * @Route("/system-reading/{page<\d+>?1}", name="system_reading")
     * @IsGranted("SYSTEM_OBSERVER")
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
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="system")
     */
    public function encode(System $system, ObjectManager $manager, Request $request, StationRepository $stationRepository, MeasurabilityRepository $instrumentParameterRepository, BasinRepository $basinRepository, StationKindRepository $stationKindRepository, SystemParameterRepository $systemParameterRepository)
    {
        /* Obtenir la liste des stations du système */
        $systemStations = $stationRepository->findSystemStations($system);

        /* Obtenir la liste ordonnée de paramètres du système */
        $systemParameters = $systemParameterRepository->findSystemParameters($system);

        /* Instancier un nouveau relevé de système */
        $systemReading = new SystemReading();
        $systemReading
            ->setSystem($system)
            ->setEncodingDateTime(new \DateTime('now'))
            ->setEncodingAuthor($this->getUser());

        /* Pour chaque paramètre du système, ajouter un contrôle */
        if (!empty($systemParameters)) {
            foreach ($systemParameters as $systemParameter) {
                $control = $this->createControl($systemParameter);
                $systemReading->addControl($control);
            }
        }

        /* Pour chaque station du système, ajouter un relevé de station au relevé de système */
        if (!empty($systemStations) && !empty($systemParameters)) {
            foreach ($systemStations as $station) {
                /* Créer le relevé de station */
                $stationReading = new Reading();
                $stationReading->setStation($station);
                /* Pour chaque paramètre, ajouter une nouvelle mesure au relevé de station en activant la conversion de valeur */
                foreach ($systemParameters as $systemParameter) {
                    $measure = $this->createMeasure($systemParameter, true);
                    $stationReading->addMeasure($measure);
                }

                /* Ajouter la station au relevé */
                $systemReading->addStationReading($stationReading);
            }
        }

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemReadingType::class, $systemReading, [
            'showEncoding' => true,
            'showValidation' => false,
        ]);
        $form->handleRequest($request);

        /* Vérifier le soumission et la validité du formulaire */
        if ($form->isSubmitted() && $form->isValid()) {
            /* Mémoriser le relevé de système */
            $this->storeSystemReading($systemReading, $manager);

            $this->addFlash('success', "Le relevé <strong>{$systemReading->getCode()}</strong> contenant <strong>{$systemReading->getStationReadings()->count()}</strong> relevés de stations a été encodé avec succès.");

            return $this->redirectToRoute('system_reading_show', [
                'code' => $systemReading->getCode(),
            ]);
        }

        return $this->render('system_reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Encoder un relevé pour {$system->getName()}",
            'system' => $system,
            'systemParameters' => $systemParameters,
            'conversions_enabled' => true,
        ]);
    }

    /**
     * @Route("/system-reading/{code}/edit", name="system_reading_edit")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="systemReading")
     */
    public function edit(SystemReading $systemReading, Request $request, ObjectManager $manager, SystemParameterRepository $systemParameterRepository)
    {
        if ($systemReading->countValidatedReadings()) {
            $this->addFlash('danger', "Ce relevé de système ne peut pas être modifié car au moins un de ses relevés de station a été validé.<br>Faites les modifications sur les relevés de station individuellement.");
            return $this->redirect($request->headers->get('referer'));
        }

        /* Obtenir la liste ordonnée de paramètres du système */
        $system = $systemReading->getSystem();
        $systemParameters = $systemParameterRepository->findSystemParameters($system);

        if ((false == $this->loadControls($systemReading, $systemParameters)) ||(false == $this->loadMeasures($systemReading, $systemParameters))) {
                /* Cas spécial non géré pour l'instant */
                $this->addFlash('danger', "Ce relevé de système ne peut être modifié car il contient des mesures excédentaires par rapport aux paramètres actuellement assignés au système.<br>Faites les modifications sur les relevés de station directement.");
                return $this->redirect($request->headers->get('referer'));
        }

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemReadingType::class, $systemReading, [
            'showEncoding' => true,
            'showValidation' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mémoriser le relevé de système */
            $this->storeSystemReading($systemReading, $manager);

            $this->addFlash('success', "Le relevé <strong>{$systemReading->getCode()}</strong> contenant <strong>{$systemReading->getStationReadings()->count()}</strong> relevés de stations a été mis à jour avec succès.");

            return $this->redirectToRoute('system_reading_show', [
                'code' => $systemReading->getCode(),
            ]);
        }

        return $this->render('system_reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier le relevé {$systemReading->getCode()}",
            'system' => $system,
            'systemParameters' => $systemParameters,
            'conversions_enabled' => false,
        ]);
    }

    /**
     * Traite la suppression d'un relevé de système.
     *
     * @Route("system-reading/{code}/delete", name="system_reading_delete")
     * @IsGranted("SYSTEM_MANAGER", subject="systemReading")
     */
    public function delete(SystemReading $systemReading, Request $request, ObjectManager $manager)
    {
        if ($systemReading->countValidatedReadings()) {
            $this->addFlash('danger', "Ce relevé de système ne peut pas être supprimé car au moins un de ses relevés de station a été validé.<br>Supprimez les relevés de station non validés individuellement.");
            return $this->redirect($request->headers->get('referer'));
        }

        /* Créer et traiter le formulaire de confirmation */
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Supprimer le relevé de système et son contenu */
            $manager->remove($systemReading);
            $manager->flush();
    
            $this->addFlash('success', "Le relevé <strong>{$systemReading->getCode()}</strong> a été supprimé avec succès.");

            return $this->redirectToRoute('system_show_readings', [
                'slug' => $systemReading->getSystem()->getSlug(),
            ]);
        }

        return $this->render('system_reading/delete.html.twig', [
            'form' => $form->createView(),
            'systemReading' => $systemReading,
            'title' => "Supprimer le relevé $systemReading",
        ]);
    }

    /**
     * @Route("/system-reading/{code}", name="system_reading_show")
     * @IsGranted("SYSTEM_OBSERVER", subject="systemReading")
     */
    public function show(SystemReading $systemReading, ParameterRepository $parameterRepository)
    {
        return $this->render('system_reading/show.html.twig', [
            'systemReading' => $systemReading,
            'parameters' => $parameterRepository->findFavorites(),
        ]);
    }

    /**
     * Crée une valeur de contrôle vide liée à un paramètre de système.
     *
     * @param SystemParameter $systemParameter
     * @return Control
     */
    private function createControl(SystemParameter $systemParameter)
    {
        $control = new Control();
        $control
            ->setInstrumentParameter($systemParameter->getInstrumentParameter());
        return $control;
    }

    /**
     * Trouve un paramètre de système parmi un tableau de valeurs de contrôle.
     *
     * @param SystemParameter $systemParameter
     * @param Control[] $controls
     * @return integer|string|null
     */
    private function findParameterInControls(SystemParameter $systemParameter, array $controls)
    {
        foreach ($controls as $key => $control) {
            if ($control->getInstrumentParameter() === $systemParameter->getInstrumentParameter()) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Charge les valeurs de contrôle contenues dans un relevé de système, dans
     * l'ordre de la liste des paramètres du système, et en insérant les
     * éventuelles valeurs manquantes pour qu'elles puissent être saisies par
     * l'utilisateur.
     *
     * @param SystemReading $systemReading
     * @param array $systemParameters
     * @return bool
     */
    private function loadControls(SystemReading $systemReading, array $systemParameters) : bool
    {
        $success = true;

        /* Déplacer toutes les mesures de contrôle vers un tableau temporaire */
        $controls = [];
        foreach ($systemReading->getControls() as $control) {
            $controls[] = $control;
            $systemReading->removeControl($control);
        }

        /* Recréer le tableau dans le relevé de système, dans l'ordre, et en y
        insérant les mesures manquantes */
        foreach ($systemParameters as $systemParameter) {
            $key = $this->findParameterInControls($systemParameter, $controls);
            if (null !== $key) {
                $control = $controls[$key];
                array_splice($controls, $key, 1);
            } else {
                $control = $this->createControl($systemParameter);
            }
            $systemReading->addControl($control);
        }

        /* Restaurer les mesures supplémentaires. Elles ne seront pas affichées,
        mais au moins elles ne seront pas perdues! */
        foreach ($controls as $control) {
            $constol->addMeasure($control);
            $success = false;
        }

        return $success;
    }

    /**
     * Crée une mesure vide liée à un paramètre de système.
     *
     * @param SystemParameter $systemParameter
     * @return Measure
     */
    private function createMeasure(SystemParameter $systemParameter, bool $conversionRequired)
    {
        $measure = new Measure($conversionRequired);
        $measure
            ->setMeasurability($systemParameter->getInstrumentParameter())
            ->setValue(null)
            ->setStable(true)
            ->setValid(true);
        return $measure;
    }

    /**
     * Trouve un paramètre de système parmi un tableau de mesures.
     *
     * @param SystemParameter $systemParameter
     * @param Measure[] $measures
     * @return integer|string|null
     */
    private function findParameterInMeasures(SystemParameter $systemParameter, array $measures)
    {
        foreach ($measures as $key => $measure) {
            if ($measure->getMeasurability() === $systemParameter->getInstrumentParameter()) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Charge les mesures de stations contenues dans un relevé de système, dans
     * l'ordre de la liste des paramètres du système, et en insérant les
     * éventuelles valeurs manquantes pour qu'elles puissent être saisies par
     * l'utilisateur.
     *
     * @param SystemReading $systemReading
     * @param array $systemParameters
     * @return boolean
     */
    private function loadMeasures(SystemReading $systemReading, array $systemParameters) : bool
    {
        $success = true;

        /* Traiter les relevés de stations: dans chacun d'eux, les mesures ne sont pas nécessairement dans le même ordre que les paramètres du système, or cet ordre est important car les paramètres sont en en-tête des colonnes du formulaire. Il se peut également qu'il n'y ait pas de mesure pour certains paramètres, ou qu'il y ait plusieurs mesures pour d'autres. */
        foreach ($systemReading->getStationReadings() as $stationReading) {
            /* Déplacer les mesures du relevé vers un tableau temporaire */
            $stationMeasures = [];
            foreach ($stationReading->getMeasures() as $stationMeasure) {
                $stationMeasures[] = $stationMeasure;
                $stationReading->removeMeasure($stationMeasure);
            }

            /* Reconstituer le tableau de mesures du relevé, dans l'ordre des paramètres du système et en insérant des mesures vides pour les paramètres n'ayant pas de mesure */
            foreach ($systemParameters as $systemParameter) {
                $key = $this->findParameterInMeasures($systemParameter, $stationMeasures);
                if (null !== $key) {
                    $measure = $stationMeasures[$key];
                    array_splice($stationMeasures, $key, 1);
                } else {
                    /* Créer une mesure sans activer la conversion de valeur */
                    $measure = $this->createMeasure($systemParameter, false);
                }
                $stationReading->addMeasure($measure);
            }

            /* Restaurer les mesures supplémentaires. Elles ne seront pas affichées, mais au moins elles ne seront pas perdues! */
            foreach ($stationMeasures as $stationMeasure) {
                $stationReading->addMeasure($measure);
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Détecte si la valeur d'une mesure est hors norme et, dans ce cas,
     * crée automatiquement une alarme liée au relevé de système.
     *
     * @param Measure $measure
     * @param SystemReading $systemReading
     * @param ObjectManager $manager
     */
    private function testNormativeLimits(Measure $measure, SystemReading $systemReading, ObjectManager $manager)
    {
        if ($measure->getValid()) {
            /* Obtenir la valeur et le paramètre */
            $value = $measure->getValue();
            $parameter = $measure->getMeasurability()->getParameter();
            /* Tester la limite inférieure */
            $minimum = $parameter->getNormativeMinimum();
            if ((null !== $minimum) && ($value < $minimum)) {
                $this->createNormativeAlarm($systemReading, $measure,
                    " - " . $measure->getReading()->getStation()->getName() .
                    " : " . $parameter->getTitle() .
                    " = " . $value . " < " . $minimum . " " .
                    $parameter->getUnit(), $manager);
            }
            /* Tester la limite supérieure */
            $maximum = $parameter->getNormativeMaximum();
            if ((null !== $maximum) && ($value > $maximum)) {
                $this->createNormativeAlarm($systemReading, $measure,
                    " - " . $measure->getReading()->getStation()->getName() .
                    " : " . $parameter->getTitle() .
                    " = " . $value . " > " . $maximum . " " .
                    $parameter->getUnit(), $manager);
            }
        }
    }

    /**
     * Crée une alarme normative, si elle n'existe pas encore, et y ajoute la
     * mesure indiquée.
     *
     * @param SystemReading $systemReading
     * @param Measure $measure
     * @param string $note
     * @param ObjectManager $manager
     * @return void
     */
    private function createNormativeAlarm(SystemReading $systemReading, Measure $measure, string $note, ObjectManager $manager)
    {
        $alarm = $systemReading->getAlarm();
        if (null === $alarm) {
            /* Créer une nouvelle alarme */
            $alarm = new Alarm();
            $alarm
                ->setSystem($measure->getReading()->getStation()->getBasin()->getSystem())
                ->setReportingAuthor($measure->getEncodingAuthor())
                ->setReportingDate($measure->getEncodingDateTime())
                ->setNotes("Certaines valeurs mesurées dépassent les normes.");
            /* Lier l'alarme au relevé de système */
            $systemReading->setAlarm($alarm);

            $this->addFlash('warning', "Une alarme a été créée automatiquement car certaines valeurs dépassent les normes.");
        }
        /* Ajouter le commentaire à l'alarme */
        if (!empty($note)) {
            $alarm->setNotes($alarm->getNotes() . PHP_EOL . $note);
        }
        /* Ajouter la mesure à l'alarme */
        $alarm->addMeasure($measure);
        $measure->setAlarm($alarm);
        /* Ajouter l'alarme à la base de données */
        $manager->persist($alarm);
        return $alarm;
    }

    /**
     * Mémorise ou met à jour le relevé de système dans la base de données, en
     * y supprimant les mesures non complétées et les stations ne comportant
     * ni mesures ni remarques.
     *
     * @param SystemReading $systemReading
     * @param ObjectManager $manager
     * @return void
     */
    private function storeSystemReading(SystemReading $systemReading, ObjectManager $manager)
    {
        /* Mettre en cache des informations du relevé de système */
        $fieldDateTime = $systemReading->getFieldDateTime();
        $encodingDateTime = $systemReading->getEncodingDateTime();
        $encodingAuthor = $systemReading->getEncodingAuthor();

        /* Traiter chacun des relevés de station */
        foreach ($systemReading->getStationReadings() as $stationReading) {
            $station = $stationReading->getStation();

            /* Supprimer les mesures pour lesquelles aucune valeur n'a été encodée */
            foreach ($stationReading->getMeasures() as $measure) {
                if (null === $measure->getValue()) {
                    $stationReading->removeMeasure($measure);
                }
            }

            /* Traiter les mesures restantes */
            $measures = $stationReading->getMeasures();
            if ((0 != $measures->count()) || !empty($stationReading->getEncodingNotes())) {
                /* Définir les propriétés de chaque mesure et persister ces dernières dans la base de données */
                foreach ($measures as $measure) {
                    $measure
                        ->setFieldDateTime($fieldDateTime)
                        ->setEncodingDateTime($encodingDateTime)
                        ->setEncodingAuthor($encodingAuthor)
                        ->setReading($stationReading);
                    $manager->persist($measure);

                    /* Détecter les valeurs hors normes, si la mesure n'a pas déjà été liée à une alarme */
                    if (null === $measure->getAlarm()) {
                        $this->testNormativeLimits($measure, $systemReading, $manager);
                    }
                }

                /* Définir les propriétés du relevé de station et la persister dans la base de données */
                $stationReading
                    ->setFieldDateTime($fieldDateTime)
                    ->setEncodingDateTime($encodingDateTime)
                    ->setEncodingAuthor($encodingAuthor)
                    ->setSystemReading($systemReading);
                $manager->persist($stationReading);
            } else {
                /* Enlever le relevé de station car il est vide et aucune remarque n'a été fournie */
                $systemReading->removeStationReading($stationReading);
            }
        }

        /* Traiter chacune des valeurs de contrôle */
        foreach ($systemReading->getControls() as $control) {
            if (null === $control->getValue()) {
                $systemReading->removeControl($control);
                $manager->remove($control);
            } else {
                $control
                    ->setSystemReading($systemReading)
                    ->setDateTime($fieldDateTime);
                $manager->persist($control);
            }
        }

        /* Persister le relevé de système dans la base de données */
        $manager->persist($systemReading);
        $manager->flush();
    }
}
