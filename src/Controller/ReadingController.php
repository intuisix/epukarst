<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Filter;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\FilterType;
use App\Form\ReadingType;
use App\Service\Breadcrumbs;
use App\Entity\FilterMeasure;
use App\Service\PaginationService;
use App\Repository\BasinRepository;
use App\Repository\SystemRepository;
use App\Repository\ReadingRepository;
use App\Repository\StationRepository;
use App\Repository\ParameterRepository;
use App\Service\ReadingExporterService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReadingController extends AbstractController
{
    /**
     * @Route("/reading/{page<\d+>?1}", name="reading")
     * @IsGranted("SYSTEM_OBSERVER")
     */
    public function index(int $page, PaginationService $pagination, ParameterRepository $parameterRepository, Request $request, SystemRepository $systemRepository, BasinRepository $basinRepository, StationRepository $stationRepository, ReadingRepository $readingRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Index des relevés de station");

        /* Obtenir l'objet de session */
        $session = $request->getSession();

        /* Charger un filtre basé sur les informations de session */
        $filter = $this->getFilter($session, $systemRepository, $basinRepository, $stationRepository);

        /* Créer et traiter le formulaire de filtre */
        $form = $this->createForm(FilterType::class, $filter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Sérialiser les systèmes */
            $systemIds = $filter->getSystems()->map(function($system) { return $system->getId(); })->getValues();
            $session->set('systems', $systemIds);

            /* Sérialiser les bassins */
            $basinIds = $filter->getBasins()->map(function($basin) {
                return $basin->getId(); })->getValues();
            $session->set('basins', $basinIds);

            /* Sérialiser les stations */
            $stationIds = $filter->getStations()->map(function($station) {
                    return $station->getId(); })->getValues();
            $session->set('stations', $stationIds);

            /* Sérialiser les dates */
            $session->set('minimumDate', $filter->getMinimumDate());
            $session->set('maximumDate', $filter->getMaximumDate());

            /* Sérialiser les états */
            $session->set('validated', $filter->getValidated());
            $session->set('invalidated', $filter->getInvalidated());
            $session->set('submitted', $filter->getSubmitted());

            /* Sérialiser les mesures */
            $measures = [];
            foreach ($filter->getMeasures() as $measure) {
                $measures[$measure->getParameter()->getId()] = [
                    'minimumValue' => $measure->getMinimumValue(),
                    'maximumValue' => $measure->getMaximumValue(),
                ];
            }
            $session->set('measures', $measures);
        }

        $pagination
            ->setEntityClass(Reading::class)
            ->setQueryBuilder($readingRepository->getQueryBuilder($filter))
            ->setPage($page)
        ;

        return $this->render('reading/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'systems' => $systemRepository->findAll(),
            'parameters' => $parameterRepository->findFavorites(),
            'breadcrumbs' => null,  /* Volontairement, pas de fil d'ariane */
        ]);
    }

    /**
     * Gère l'encodage d'un nouveau relevé.
     * 
     * @Route("/reading/encode", name="reading_encode")
     * @IsGranted("SYSTEM_CONTRIBUTOR")
     */
    public function encode(ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Encodage d'un relevé de station");

        /* Instancier un nouveau relevé */
        $reading = new Reading();
        $reading
            ->setEncodingAuthor($this->getUser())
            ->setEncodingDateTime(new DateTime('now'));

        /* Créer et traiter le formulaire */
        $form = $this->createForm(ReadingType::class, $reading, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Associer les mesures au relevé */
            $this->updateMeasures($reading,
                $reading->getEncodingAuthor(),
                $reading->getEncodingDateTime(),
                $manager);
        
            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été encodé avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Encoder un nouveau relevé",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Gère la modification d'un relevé existant.
     * 
     * @Route("/reading/{code}/modify", name="reading_modify")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="reading")
     */
    public function modify(Reading $reading, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'un relevé de station");

        if (null !== $reading->getValidated()) {
            if ($this->isGranted('SYSTEM_MANAGER')) {
                $this->addFlash('info', "Etant donné que le relevé <strong>{$reading->getCode()}</strong> est validé ou invalidé, vous ne pouvez plus le modifier qu'en le validant à nouveau.");
            } else {
                $this->addFlash('danger', "Etant donné que le relevé <strong>{$reading->getCode()}</strong> est validé ou invalidé, il ne peut plus être modifié que par un gestionnaire de <strong>{$reading->getStation()->getBasin()->getSystem()->getName()}</strong>.");
            }

            return $this->redirect($breadcrumbs->getPrevious());
        }

        /* Créer et traiter le formulaire */
        $form = $this->createForm(ReadingType::class, $reading, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Associer les mesures au relevé */
            $this->updateMeasures($reading,
                $this->getUser(),
                new DateTime('now'),
                $manager);

            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été modifié avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('reading/form.html.twig', [
            'reading' => $reading,
            'form' => $form->createView(),
            'title' => "Modifier le relevé {$reading->getCode()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Gère la validation d'un relevé existant.
     * 
     * @Route("/reading/{code}/validate", name="reading_validate")
     * @IsGranted("SYSTEM_MANAGER", subject="reading")
     */
    public function validate(Reading $reading, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Validation d'un relevé de station");

        /* Définir l'auteur et la date de la validation */
        $reading
            ->setValidationAuthor($this->getUser())
            ->setValidationDateTime(new DateTime('now'));

        /* Créer et traiter le formulaire en mode validation */
        $form = $this->createForm(ReadingType::class, $reading, [
            'user' => $this->getUser(),
            'validation' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Associer les mesures au relevé */
            $this->updateMeasures($reading,
                $reading->getValidationAuthor(),
                $reading->getValidationDateTime(),
                $manager);

            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été enregistré avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('reading/form.html.twig', [
            'reading' => $reading,
            'form' => $form->createView(),
            'title' => "Valider le relevé {$reading->getCode()}",
            'validation' => true,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Exporte des relevés.
     * 
     * @Route("/reading/export", name="reading_export")
     * @IsGranted("SYSTEM_MANAGER")
     *
     * @param ReadingRepository $readingRepository
     * @return Response
     */
    public function export(ReadingRepository $readingRepository, ReadingExporterService $exporter, ParameterRepository $parameterRepository, Request $request, SystemRepository $systemRepository, BasinRepository $basinRepository, StationRepository $stationRepository)
    {
        /* Tester s'il faut exporter tous les relevés ou seulement ceux qui sont sélectionnés individuellement */
        if ($request->request->get('all-readings') ||
            $request->request->get('readings') === null) {
            /* Charger un filtre basé sur les informations de session, puis obtenir tous les relevés correspondant au filtre, par ordre chronologique */
            $session = $request->getSession();
            $filter = $this->getFilter($session, $systemRepository, $basinRepository, $stationRepository);
            $exporter->setReadings($readingRepository->getQueryBuilder($filter)->getQuery()->getResult());
        } else {
            /* Déterminer les relevés sélectionnés pour l'exportation, et les obtenir par ordre chronologique */
            $ids = array_keys($request->request->get('readings'));
            $exporter->setReadings($readingRepository->findBy(
                ['code' => $ids],
                ['fieldDateTime' => 'ASC']));
        }

        /* Charger la liste des paramètres à exporter */
        $exporter->setParameters($parameterRepository->findAllOrdered());

        /* Exporter les relevés dans une feuille de calcul en mémoire */
        $spreadsheet = $exporter->getSpreadsheet();

        /* Générer le fichier au format Excel 2007 Excel (.xlsx) dans le
        répertoire de fichiers fichiers temporaire du système */
        $writer = new Xlsx($spreadsheet);
        $fileName = "epukarst_readings_" . uniqid() . ".xlsx";
        $tempName = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempName);

        /* Renvoyer le fichier en pièce jointe de la réponse */
        return $this->file($tempName, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * Traite la suppression d'un relevé.
     *
     * @Route("reading/{code}/delete", name="reading_delete")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="reading")
     */
    public function delete(Reading $reading, Request $request, ObjectManager $manager, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'un relevé de station");

        if (true === $reading->getValidated()) {
            $this->addFlash('danger', "Le relevé <strong>{$reading->getCode()}</strong> ne peut être supprimé car il a été validé.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->remove($reading);
            $manager->flush();
    
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été supprimé avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious('station_reading'));
        }

        return $this->render('reading/delete.html.twig', [
            'form' => $form->createView(),
            'reading' => $reading,
            'title' => "Supprimer le relevé $reading",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche un relevé.
     * 
     * @Route("/reading/{code}", name="reading_show")
     * @IsGranted("SYSTEM_OBSERVER", subject="reading")
     */
    public function show(Reading $reading, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Visualisation d'un relevé de station", 'station_reading');

        return $this->render('reading/show.html.twig', [
            'reading' => $reading,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Instancie un objet filtre d'après les informations mémorisées dans la session.
     *
     * @param Session $session
     * @param SystemRepository $systemRepository
     * @param BasinRepository $basinRepository
     * @param StationRepository $stationRepository
     * @return Filter
     */
    private function getFilter(Session $session, SystemRepository $systemRepository, BasinRepository $basinRepository, StationRepository $stationRepository)
    {
        /* Instancier un filtre */
        $filter = new Filter();

        /* Désérialiser les systèmes */
        if ($session->has('systems')) {
            $systemIds = $session->get('systems');
            foreach ($systemIds as $systemId) {
                $system = $systemRepository->findOneById($systemId);
                if ($system != null) {
                    $filter->addSystem($system);
                }
            }
        }

        /* Désérialiser les bassins */
        if ($session->has('basins')) {
            $basinIds = $session->get('basins');
            foreach ($basinIds as $basinId) {
                $basin = $basinRepository->findOneById($basinId);
                if (null != $basin) {
                    $filter->addBasin($basin);
                }
            }
        }

        /* Désérialiser les stations */
        if ($session->has('stations')) {
            $stationIds = $session->get('stations');
            foreach ($stationIds as $stationId) {
                $station = $stationRepository->findOneById($stationId);
                if (null != $station) {
                    $filter->addStation($station);
                }
            }
        }

        /* Désérialiser les dates */
        if ($session->has('minimumDate')) {
            $filter->setMinimumDate($session->get('minimumDate'));
        }
        if ($session->has('maximumDate')) {
            $filter->setMaximumDate($session->get('maximumDate'));
        }

        /* Désérialiser les états */
        if ($session->has('validated')) {
            $filter->setValidated($session->get('validated'));
        }
        if ($session->has('invalidated')) {
            $filter->setInvalidated($session->get('invalidated'));
        }
        if ($session->has('submitted')) {
            $filter->setSubmitted($session->get('submitted'));
        }

        /* Désérialiser les mesures */
        if ($session->has('measures')) {
            $measures = $session->get('measures');
            foreach ($measures as $parameterId => $measure) {
                $parameter = $parameterRepository->findOneById($parameterId);
                if (null != $parameter) {
                    $filterMeasure = new FilterMeasure();
                    $filterMeasure
                        ->setParameter($parameter)
                        ->setMinimumValue($measure['minimumValue'])
                        ->setMaximumValue($measure['maximumValue']);
                    $filter->addMeasure($filterMeasure);
                }
            }
        }

        return $filter;
    }

    /**
     * Associe les mesures au relevé et définit l'utilisateur et la date
     * d'encodage, si ces informations n'existent pas encore.
     *
     * @param Reading $reading
     * @param User $author
     * @param DateTime $dateTime
     * @return void
     */
    private function updateMeasures(Reading $reading, User $author, DateTime $encodingDateTime, ObjectManager $manager)
    {
        /* Pour chaque mesure présente sur le formulaire */
        foreach ($reading->getMeasures() as $measure) {
            /* Associer au relevé */
            $measure->setReading($reading);

            /* Assurer que la date de mesure est définie */
            if (empty($measure->getFieldDateTime())) {
                $measure->setFieldDateTime($reading->getFieldDateTime());
            }

            /* Assurer que la date de l'encodage est définie */
            if (empty($measure->getEncodingDateTime())) {
                $measure->setEncodingDateTime($encodingDateTime);
            }

            /* Assurer que l'auteur de l'encodage est définie */
            if (empty($measure->getEncodingAuthor())) {
                $measure->setEncodingAuthor($author);
            }

            /* Persister en base de données */
            $manager->persist($measure);
        }
    }
}
