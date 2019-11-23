<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Filter;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\FilterType;
use App\Entity\FilterMeasure;
use App\Form\ReadingEncodingType;
use App\Service\PaginationService;
use App\Form\ReadingValidationType;
use App\Repository\BasinRepository;
use App\Repository\SystemRepository;
use App\Repository\ReadingRepository;
use App\Repository\StationRepository;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReadingController extends AbstractController
{
    /**
     * @Route("/reading/{page<\d+>?1}", name="reading")
     * @IsGranted("ROLE_USER")
     */
    public function index(int $page, PaginationService $pagination, ParameterRepository $parameterRepository, Request $request, SystemRepository $systemRepository, BasinRepository $basinRepository, StationRepository $stationRepository, ReadingRepository $readingRepository)
    {
        $session = $request->getSession();

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
        ]);
    }

    /**
     * Gère l'encodage d'un nouveau relevé.
     * 
     * @Route("/reading/encode", name="reading_encode")
     * @IsGranted("ROLE_USER")
     */
    public function encode(ObjectManager $manager, Request $request) {
        /* Instancier un nouveau relevé */
        $reading = new Reading();
        $reading
            ->setEncodingAuthor($this->getUser())
            ->setEncodingDateTime(new DateTime('now'));

        /* Créer et traiter le formulaire */
        $form = $this->createForm(ReadingEncodingType::class, $reading);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Associer les mesures au relevé */
            $this->updateMeasures($reading,
                $this->getUser(),
                $reading->getEncodingDateTime(),
                $manager);
        
            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été encodé avec succès.");
    
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode()
            ]);
        }

        return $this->render('reading/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Encoder un nouveau relevé",
        ]);
    }

    /**
     * Gère la modification d'un relevé existant.
     * 
     * @Route("/reading/{code}/modify", name="reading_modify")
     * @IsGranted("ROLE_USER")
     */
    public function modify(Reading $reading, ObjectManager $manager, Request $request) {
        /* Créer et traiter le formulaire */
        $form = $this->createForm(ReadingEncodingType::class, $reading);
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
    
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode()
            ]);
        }

        return $this->render('reading/form.html.twig', [
            'reading' => $reading,
            'form' => $form->createView(),
            'title' => "Modifier le relevé {$reading->getCode()}",
        ]);
    }

    /**
     * Gère la validation d'un relevé existant.
     * 
     * @Route("/reading/{code}/validate", name="reading_validate")
     * @IsGranted("ROLE_USER")
     */
    public function validate(Reading $reading, ObjectManager $manager, Request $request)
    {
        /* Définir l'auteur et la date de la validation */
        $reading
            ->setValidationAuthor($this->getUser())
            ->setValidationDateTime(new DateTime('now'));

        /* Créer et traiter le formulaire */
        $form = $this->createForm(ReadingValidationType::class, $reading);
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
    
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode()
            ]);
        }

        return $this->render('reading/form.html.twig', [
            'reading' => $reading,
            'form' => $form->createView(),
            'title' => "Valider le relevé {$reading->getCode()}",
        ]);
    }

    /**
     * Affiche un relevé existant.
     * 
     * @Route("/reading/{code}", name="reading_show")
     * @IsGranted("ROLE_USER")
     */
    public function show(Reading $reading) {
        return $this->render('reading/show.html.twig', [
            'reading' => $reading
        ]);
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
    private function updateMeasures(Reading $reading, User $author, DateTime $dateTime, ObjectManager $manager)
    {
        /* Pour chaque mesure présente sur le formulaire */
        foreach ($reading->getMeasures() as $measure) {
            /* Associer au relevé */
            $measure->setReading($reading);

            /* Assurer que la date de l'encodage est définie */
            if (empty($measure->getEncodingDateTime())) {
                $measure->setEncodingDateTime($dateTime);
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
