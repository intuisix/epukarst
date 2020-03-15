<?php

namespace App\Controller;

use App\Entity\Alarm;
use App\Entity\System;
use App\Entity\Control;
use App\Entity\Measure;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\Attachment;
use App\Service\Breadcrumbs;
use App\Entity\Measurability;
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
     * Affiche la liste des fiches de tous les systèmes.
     * 
     * @Route("/system-reading/{page<\d+>?1}", name="system_reading")
     * @IsGranted("SYSTEM_OBSERVER")
     */
    public function index(int $page, PaginationService $pagination, SystemRepository $systemRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des fiches");

        $pagination
            ->setEntityClass(SystemReading::class)
            ->setOrderBy([
                'fieldDateTime' => 'DESC',
                'encodingDateTime' => 'DESC',
                'code' => 'DESC',
            ])
            ->setPage($page)
        ;

        return $this->render('system_reading/index.html.twig', [
            'pagination' => $pagination,
            'systems' => $systemRepository->findAll(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche la liste des fiches d'un système donné.
     * 
     * @Route("/system-reading/list/{code}", name="system_reading_list")
     * @IsGranted("SYSTEM_OBSERVER", subject="system")
     */
    public function list(System $system, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Liste des fiches d'un système");

        return $this->render('system_reading/list.html.twig', [
            'system' => $system,
            'breadcrumbs' => $breadcrumbs,
            'title' => "Fiches de {$system->getName()}",
        ]);
    }

    /**
     * Gère l'encodage d'une nouvelle fiche pour un système donné.
     * 
     * @Route("/system-reading/encode/{code}", name="system_reading_encode")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="system")
     */
    public function encode(System $system, ObjectManager $manager, Request $request, StationRepository $stationRepository, MeasurabilityRepository $instrumentParameterRepository, BasinRepository $basinRepository, StationKindRepository $stationKindRepository, SystemParameterRepository $systemParameterRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Création d'une fiche");

        /* Instancier une nouvelle fiche */
        $systemReading = new SystemReading();
        $systemReading
            ->setSystem($system)
            ->setEncodingDateTime(new \DateTime('now'))
            ->setEncodingAuthor($this->getUser());

        /* Obtenir les listes ordonnées des paramètres et des stations */
        $systemStations = $stationRepository->findSystemStations($system);
        $systemParameters = $systemParameterRepository->findSystemParameters($system);
        $systemParameters = $this->loadSystemParameters($systemReading, $system, $systemParameterRepository);

        if (empty($systemParameters)) {
            $this->addFlash('danger', "Pour pouvoir encoder une fiche, les paramètres à encoder doivent être définis pour le système <strong>{$system->getName()}</strong>.");
            return $this->redirect($breadcrumbs->getPrevious());
        } else if (empty($systemStations)) {
            $this->addFlash('danger', "Pour pouvoir encoder une fiche, les stations à encoder doivent être définies pour le système <strong>{$system->getName()}</strong>.");
            return $this->redirect($breadcrumbs->getPrevious());
        }

        /* Ajouter un nouveau contrôle pour chaque paramètre du système*/
        if (!empty($systemParameters)) {
            foreach ($systemParameters as $systemParameter) {
                $control = $this->createControl($systemParameter);
                $systemReading->addControl($control);
            }
        }

        /* Ajouter un nouveau relevé pour chaque station du système */
        if (!empty($systemStations) && !empty($systemParameters)) {
            foreach ($systemStations as $station) {
                $stationReading = $this->createStationReading($station, $systemParameters);
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
            /* Ajouter les nouvelles pièces jointes */
            $this->addNewAttachments($systemReading, $form, $this->getUser(), $manager);
            /* Mémoriser la fiche */
            $this->storeSystemReading($systemReading, $manager);

            $this->addFlash('success', "La fiche <strong>{$systemReading->getCode()}</strong> a été encodée avec succès.<br>Nous vous prions néanmoins de bien vouloir vérifier qu'elle a été enregistrée correctement et complètement.");

            /* Aller sur la page de visualisation */
            $breadcrumbs->removeLast();
            return $this->redirectToRoute('system_reading_show', [
                'code' => $systemReading->getCode(),
            ]);
        }

        return $this->render('system_reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Encoder une fiche pour {$system->getName()}",
            'system' => $system,
            'systemParameters' => $systemParameters,
            'conversions_enabled' => true,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Gère la modification d'une fiche.
     * 
     * @Route("/system-reading/{code}/modify", name="system_reading_edit")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="systemReading")
     */
    public function modify(SystemReading $systemReading, Request $request, ObjectManager $manager, StationRepository $stationRepository, SystemParameterRepository $systemParameterRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'une fiche");

        if ($systemReading->countValidatedReadings()) {
            $this->addFlash('danger', "La fiche {$systemReading->getCode()} ne peut pas être modifiée car au moins un de ses relevés a été validé.");
            return $this->redirect($breadcrumbs->getPrevious());
        }

        /* Obtenir les listes ordonnées des paramètres et des stations */
        $system = $systemReading->getSystem();
        $systemStations = $stationRepository->findSystemStations($system);
        $systemParameters = $this->loadSystemParameters($systemReading, $system, $systemParameterRepository);

        /* Charger les valeurs de contrôle et les relevés */
        if ((false == $this->loadControls($systemReading, $systemParameters)) ||
            (false == $this->loadStationReadings($systemReading, $systemStations, $systemParameters))) {
                /* Cas spécial non géré pour l'instant */
                $this->addFlash('danger', "La fiche {$systemReading->getCode()} ne peut être modifiée car elle contient des mesures excédentaires par rapport aux paramètres actuellement assignés au système.");
                return $this->redirect($breadcrumbs->getPrevious());
        }

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemReadingType::class, $systemReading, [
            'showEncoding' => true,
            'showValidation' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Ajouter les nouvelles pièces jointes */
            $this->addNewAttachments($systemReading, $form, $this->getUser(), $manager);
            /* Mémoriser la fiche */
            $this->storeSystemReading($systemReading, $manager);

            $this->addFlash('success', "La fiche <strong>{$systemReading->getCode()}</strong> a été mise à jour avec succès.<br>Nous vous prions néanmoins de bien vouloir vérifier qu'elle a été enregistrée correctement et complètement.");

            /* Aller sur la page de visualisation */
            $breadcrumbs->removeLast();
            return $this->redirectToRoute('system_reading_show', [
                'code' => $systemReading->getCode(),
            ]);
        }

        return $this->render('system_reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier la fiche {$systemReading->getCode()}",
            'system' => $system,
            'systemParameters' => $systemParameters,
            'conversions_enabled' => false,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Gère la suppression d'une fiche.
     *
     * @Route("system-reading/{code}/delete", name="system_reading_delete")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="systemReading")
     */
    public function delete(SystemReading $systemReading, Request $request, ObjectManager $manager, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'une fiche");

        if ($systemReading->countValidatedReadings()) {
            $this->addFlash('danger', "La fiche <strong>{$systemReading->getCode()}</strong> ne peut pas être supprimée car au moins un de ses relevés a été validé.");
            return $this->redirect($breadcrumbs->getPrevious());
        }

        /* Créer et traiter le formulaire de confirmation */
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Supprimer la fiche et son contenu */
            $manager->remove($systemReading);
            $manager->flush();
    
            $this->addFlash('success', "La fiche <strong>{$systemReading->getCode()}</strong> a été supprimée avec succès.");

            return $this->redirect($breadcrumbs->getPrevious('system_reading'));
        }

        return $this->render('system_reading/delete.html.twig', [
            'form' => $form->createView(),
            'systemReading' => $systemReading,
            'title' => "Supprimer la fiche $systemReading",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche une fiche.
     * 
     * @Route("/system-reading/{code}", name="system_reading_show")
     * @IsGranted("SYSTEM_OBSERVER", subject="systemReading")
     */
    public function show(SystemReading $systemReading, ParameterRepository $parameterRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Visualisation d'une fiche", 'system_reading');

        return $this->render('system_reading/show.html.twig', [
            'systemReading' => $systemReading,
            'parameters' => $parameterRepository->findFavorites(),
            'breadcrumbs' => $breadcrumbs,
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
     * Charge les mesures de contrôle contenues dans une fiche, dans
     * l'ordre de la liste des paramètres du système, et en insérant les
     * éventuelles mesures manquantes pour qu'elles puissent être saisies par
     * l'utilisateur.
     *
     * @param SystemReading $systemReading
     * @param SystemParameter[] $systemParameters
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

        /* Recréer le tableau dans la fiche, dans l'ordre, et en y insérant les mesures manquantes */
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

        /* Restaurer les mesures supplémentaires. Elles ne seront pas affichées, mais au moins elles ne seront pas perdues! */
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
     * Charge les mesures contenues dans un relevé, dans l'ordre
     * de la liste des paramètres du système, et en insérant les éventuelles
     * mesures manquantes pour qu'elles puissent être saisies par l'utilisateur.
     *
     * @param Reading $stationReading
     * @param SystemParameter[] $systemParameters
     * @return boolean false s'il existe plusieurs mesures pour l'un des
     * paramètres ou s'il existe des mesures qui concernent des paramètres
     * autres que ceux spécifiés par dans la liste.
     */
    private function loadMeasures(Reading $stationReading, array $systemParameters) : bool
    {
        $success = true;

        /* Déplacer les mesures vers un tableau temporaire */
        $stationMeasures = [];
        foreach ($stationReading->getMeasures() as $stationMeasure) {
            $stationMeasures[] = $stationMeasure;
            $stationReading->removeMeasure($stationMeasure);
        }

        /* Reconstituer le tableau de mesures, dans l'ordre des paramètres du système et en insérant des mesures vides pour les paramètres n'ayant pas de mesure */
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

        /* Restaurer les mesures supplémentaires: elles ne seront pas affichées, mais au moins elles ne seront pas perdues! */
        foreach ($stationMeasures as $stationMeasure) {
            $stationReading->addMeasure($measure);
            $success = false;
        }

        return $success;
    }

    /**
     * Crée un relevé avec des mesures vides pour chacun des paramètres du
     * système.
     * 
     * @param Station $station
     * @param array $systemParameters
     * @return StationReading
     */
    private function createStationReading(Station $station, array $systemParameters)
    {
        $stationReading = new Reading();
        $stationReading->setStation($station);
        /* Pour chaque paramètre, ajouter une nouvelle mesure au relevé en activant la conversion de valeur */
        foreach ($systemParameters as $systemParameter) {
            $measure = $this->createMeasure($systemParameter, true);
            $stationReading->addMeasure($measure);
        }
        return $stationReading;
    }

    /**
     * Trouve, dans le tableau donné, le relevé correspondant à la station
     * donnée.
     *
     * @param Station $station
     * @param array $stationReadings
     * @return StationReading|null
     */
    private function findStationInReadings(Station $station, array $stationReadings)
    {
        foreach ($stationReadings as $key => $stationReading) {
            if ($stationReading->getStation() === $station) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Charge les relevés contenus dans une fiche, dans l'ordre de la liste des
     * stations du système, et en insérant les éventuels relevés manquants pour
     * qu'ils puissent être saisis par l'utilisateur.
     *
     * @param SystemReading $systemReading
     * @param array $systemStations
     * @param array $systemParameters
     * @return boolean false si une station est présente plusieurs fois parmi
     * les relevés, ou si une station liée au relevé est absente de la liste
     * des stations du système.
     */
    private function loadStationReadings(SystemReading $systemReading, array $systemStations, array $systemParameters)
    {
        $success = true;

        /* Déplacer les relevés dans un tableau temporaire */
        $stationReadings = [];
        foreach ($systemReading->getStationReadings() as $stationReading) {
            if (!$this->loadMeasures($stationReading, $systemParameters)) {
                $success = false;
            }
            $stationReadings[] = $stationReading;
            $systemReading->removeStationReading($stationReading);
        }

        /* Reconstituer le tableau des relevés */
        foreach ($systemStations as $systemStation) {
            $key = $this->findStationInReadings($systemStation, $stationReadings);
            if (null !== $key) {
                $stationReading = $stationReadings[$key];
                array_splice($stationReadings, $key, 1);
            } else {
                $stationReading = $this->createStationReading($systemStation, $systemParameters);
            }
            $systemReading->addStationReading($stationReading);
        }

        /* Restaurer les relevés supplémentaires */
        foreach ($stationReadings as $stationReading) {
            $systemReading->addStationReading($stationReading);
            $success = false;
        }

        return $success;
    }

    /**
     * Détecte si la valeur d'une mesure est hors norme et, dans ce cas,
     * crée automatiquement une alarme liée à la fiche.
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
            /* Lier l'alarme à la fiche */
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
     * Ajoute les nouvelles pièces jointes à la fiche.
     *
     * @param SystemReading $systemReading
     * @param Form $form
     * @param User $user
     * @param ObjectManager $anager
     * @return void
     */
    private function addNewAttachments(SystemReading $systemReading, $form, $user, ObjectManager $manager)
    {
        /* Ajouter les pièces jointes qui viennent d'être chargées */
        foreach ($form['newAttachments']->getData() as $uploadedFile) {
            $attachment = new Attachment();
            $attachment->setUploadedFile($uploadedFile, $user);
            $systemReading->addAttachment($attachment);
            $manager->persist($attachment);
        }
    }

    /**
     * Mémorise ou met à jour la fiche dans la base de données, en y supprimant:
     * - les mesures ne contenant pas de valeur,
     * - les stations ne comportant ni mesures ni remarques,
     * - les contrôles ne contenant pas de valeur.
     *
     * @param SystemReading $systemReading
     * @param ObjectManager $manager
     * @return void
     */
    private function storeSystemReading(SystemReading $systemReading, ObjectManager $manager)
    {
        /* Obtenir la date de terrain de la fiche */
        $fieldDateTime = $systemReading->getFieldDateTime();
        /* Construire la date d'encodage et l'auteur pour les nouveaux relevés et les nouvelles mesures */
        $encodingDateTime = new \DateTime('now');
        $encodingAuthor = $this->getUser();

        /* Traiter chacun des relevés */
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
                /* Ajuster la date et l'heure du relevé */
                $stationDateTime = $stationReading->getFieldDateTime();
                if (null === $stationDateTime) {
                    /* Par défaut, utiliser la même date et la même heure que la fiche */
                    $stationDateTime = $fieldDateTime;
                    $stationReading->setFieldDateTime($stationDateTime);
                }

                /* Traiter chaque mesure du relevé */
                foreach ($measures as $measure) {
                    /* Associer la mesure au relevé */
                    $measure->setReading($stationReading);
                    /* Définir la date de terrain de la mesure */
                    $measure->setFieldDateTime($stationDateTime);
                    /* Définir la date d'encodage de la mesure */
                    if (null === $measure->getEncodingDateTime()) {
                        $measure->setEncodingDateTime($encodingDateTime);
                    }
                    /* Définir l'auteur de l'encodage de la mesure */
                    if (null === $measure->getEncodingAuthor()) {
                        $measure->setEncodingAuthor($encodingAuthor);
                    }
                    /* Mémoriser la mesure dans la base de données */
                    $manager->persist($measure);
                    /* Détecter les valeurs hors normes, si la mesure n'a pas déjà été liée à une alarme */
                    if (null === $measure->getAlarm()) {
                        $this->testNormativeLimits($measure, $systemReading, $manager);
                    }
                }

                /* Associer la fiche au relevé */
                $stationReading->setSystemReading($systemReading);
                /* Définir la date d'encodage du relevé */
                if (null === $stationReading->getEncodingDateTime()) {
                    $stationReading->setEncodingDateTime($encodingDateTime);
                }
                /* Définir l'auteur de l'encodage du relevé */
                if (null === $stationReading->getEncodingAuthor()) {
                    $stationReading->setEncodingAuthor($encodingAuthor);
                }
                /* Mémoriser le relevé en base de données */
                $manager->persist($stationReading);
            } else {
                /* Enlever le relevé car il est vide et aucune remarque n'a été fournie */
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

        /* Persister la fiche dans la base de données */
        $manager->persist($systemReading);
        $manager->flush();
    }

    /**
     * Ajoute un paramètre dans le tableau, s'il n'y est pas déjà présent.
     *
     * @param Measurability $instrumentParameter
     * @param SystemParameter[] $systemParameters
     * @return void
     */
    private function addSystemParameter(Measurability $instrumentParameter, &$systemParameters)
    {
        /* Déterminer si le paramètre est déjà présent dans le tableau */
        foreach ($systemParameters as $parameterFromArray) {
            if ($parameterFromArray->getInstrumentParameter() === $instrumentParameter) {
                /* Il est déjà présent */
                return;
            }
        }

        /* Créer le paramètre */
        $newSystemParameter = new SystemParameter();
        $newSystemParameter->setInstrumentParameter($instrumentParameter);
        $systemParameters[] = $newSystemParameter;
    }

    /**
     * Charge le tableau des paramètres devant être affichés dans les colonnes
     * du formulaire d'encodage.
     *
     * @param SystemReading $systemReading
     * @param System $system
     * @param SystemParameterRepository $systemParameterRepository
     * @return void
     */
    private function loadSystemParameters(SystemReading $systemReading, System $system, SystemParameterRepository $systemParameterRepository)
    {
        /* Commencer par charger les paramètres associés au système */
        $systemParameters = $systemParameterRepository->findSystemParameters($system);

        /* Ajouter les paramètres qui ne seraient pas(plus) associés au système, mais figurent néanmoins parmi les contrôles */
        foreach ($systemReading->getControls() as $control) {
            $instrumentParameter = $control->getInstrumentParameter();
            $this->addSystemParameter($instrumentParameter, $systemParameters);
        }

        /* Ajouter les paramètres qui ne seraient pas(plus) associés au système, mais figurent néanmoins parmi les mesures dans les relevés */
        foreach ($systemReading->getStationReadings() as $stationReading) {
            foreach ($stationReading->getMeasures() as $measure) {
                $instrumentParameter = $measure->getMeasurability();
                $this->addSystemParameter($instrumentParameter, $systemParameters);
            }
        }

        /* Terminer par trier les paramètres */
        usort($systemParameters, function(SystemParameter $a, SystemParameter $b) {
            /* Par position de paramètre */
            $result = $a->getInstrumentParameter()->getParameter()->getPosition() <=> $b->getInstrumentParameter()->getParameter()->getPosition();
            if (0 === $result) {
                /* Par code d'instrument, pour une même position */
                $result = $a->getInstrumentParameter()->getInstrument()->getCode() <=> $b->getInstrumentParameter()->getInstrument()->getCode();
            }
            return $result;
        });

        return $systemParameters;
    }
}
