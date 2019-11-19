<?php

namespace App\Controller;

use App\Entity\Filter;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\FilterType;
use App\Form\ReadingType;
use App\Service\PaginationService;
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

        if ($session->has('systems')) {
            $systemIds = $session->get('systems');
            foreach ($systemIds as $systemId) {
                $filter->addSystem($systemRepository->findOneById($systemId));
            }
        }

        if ($session->has('basins')) {
            $basinIds = $session->get('basins');
            foreach ($basinIds as $basinId) {
                $filter->addBasin($basinRepository->findOneById($basinId));
            }
        }

        if ($session->has('stations')) {
            $stationIds = $session->get('stations');
            foreach ($stationIds as $stationId) {
                $filter->addStation($stationRepository->findOneById($stationId));
            }
        }

        if ($session->has('minimumDate')) {
            $filter->setMinimumDate($session->get('minimumDate'));
        }

        if ($session->has('maximumDate')) {
            $filter->setMaximumDate($session->get('maximumDate'));
        }

        $form = $this->createForm(FilterType::class, $filter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $systemIds = $filter->getSystems()->map(function($system) { return $system->getId(); })->getValues();
            $session->set('systems', $systemIds);

            $basinIds = $filter->getBasins()->map(function($basin) {
                return $basin->getId(); })->getValues();
            $session->set('basins', $basinIds);

            $stationIds = $filter->getStations()->map(function($station) {
                    return $station->getId(); })->getValues();
            $session->set('stations', $stationIds);

            $session->set('minimumDate', $filter->getMinimumDate());
            $session->set('maximumDate', $filter->getMaximumDate());
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
        $encodingAuthor = $this->getUser();
        $encodingDateTime = new \DateTime('now');

        $reading = new Reading();
        $reading
            ->setEncodingAuthor($encodingAuthor)
            ->setEncodingDateTime($encodingDateTime);

        /* Créer et traiter le formulaire */
        $form = $this->createForm(ReadingType::class, $reading);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Associer les mesures au relevé */
            foreach ($reading->getMeasures() as $measure) {
                $measure
                ->setReading($reading)
                ->setEncodingDateTime($encodingDateTime)
                ->setEncodingAuthor($encodingAuthor);
            $manager->persist($measure);
            }
        
            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été encodé avec succès.");
    
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode()
            ]);
        }

        return $this->render('reading/encode.html.twig', [
            'form' => $form->createView()
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
        $form = $this->createForm(ReadingType::class, $reading);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* (Ré-)associer les mesures au relevé. Associer à l'utilisateur
            actuel les mesures qui viennent d'être ajoutées, et leur attribuer
            en lot la date courante. */
            $encodingDateTime = new \DateTime('now');
            foreach ($reading->getMeasures() as $measure) {
                $measure->setReading($reading);
                if (empty($measure->getEncodingDateTime())) {
                    $measure->setEncodingDateTime($encodingDateTime);
                }
                if (empty($measure->getEncodingAuthor())) {
                    $measure->setEncodingAuthor($this->getUser());
                }
                $manager->persist($measure);
            }

            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été modifié avec succès.");
    
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode()
            ]);
        }

        return $this->render('reading/modify.html.twig', [
            'reading' => $reading,
            'form' => $form->createView()
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
}
