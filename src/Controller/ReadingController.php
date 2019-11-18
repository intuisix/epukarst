<?php

namespace App\Controller;

use App\Entity\Filter;
use App\Entity\Reading;
use App\Entity\Station;
use App\Form\FilterType;
use App\Form\ReadingType;
use App\Service\PaginationService;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReadingController extends AbstractController
{
    /**
     * @Route("/reading/{page<\d+>?1}", name="reading")
     * @IsGranted("ROLE_USER")
     */
    public function index(int $page, PaginationService $pagination, ParameterRepository $parameterRepository, Request $request, SessionInterface $session)
    {
        $filter = $request->request->get('filter');
        $systems = $filter['systems'] ?? [];
        $basins = $filter['basins'] ?? [];
        $stations = $filter['stations'] ?? [];

        $pagination
            ->setEntityClass(Reading::class)
            ->setCriteria([
                'station' => $stations])
            ->setPage($page)
        ;

        /* Instancier un filtre */
        $filter = new Filter();
        $form = $this->createForm(FilterType::class, $filter, [
            'systems' => $systems,
            'basins' => $basins,
            //'filter' => $session->get('reading-filter'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('reading-filter', $filter);
        }

        return $this->render('reading/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'parameters' => $parameterRepository->findFavorites()
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
