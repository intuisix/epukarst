<?php

namespace App\Controller;

use App\Entity\Reading;
use App\Entity\Station;
use App\Form\ReadingType;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReadingController extends AbstractController
{
    /**
     * @Route("/reading/{page<\d+>?1}", name="reading")
     */
    public function index(int $page, PaginationService $pagination)
    {
        $pagination
            ->setEntityClass(Reading::class)
            ->setPage($page);

        return $this->render('reading/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Gère l'encodage d'un nouveau relevé.
     * 
     * @Route("/reading/encode", name="reading_encode")
     */
    public function encode(ObjectManager $manager, Request $request) {
        $reading = new Reading();
        $reading
            ->setEncodingDateTime(new \DateTime('now'));

        $form = $this->createForm(ReadingType::class, $reading);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été encodé avec succès.");
    
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode() ]);
        }

        return $this->render('reading/encode.html.twig', [
            'form' => $form->createView() ]);
    }

    /**
     * Montre un relevé existant.
     * 
     * @Route("/reading/{code}", name="reading_show")
     */
    public function show(Reading $reading) {
        return $this->render('reading/show.html.twig', [
            'reading' => $reading ]);
    }

    /**
     * @Route("/reading/{code}/modify", name="reading_modify")
     */
    public function modify(Reading $reading, ObjectManager $manager, Request $request) {
        $form = $this->createForm(ReadingType::class, $reading);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($reading);
            $manager->flush();
            
            $this->addFlash('success', "Le relevé <strong>{$reading->getCode()}</strong> a été modifié avec succès.");
    
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode() ]);
        }

        return $this->render('reading/modify.html.twig', [
            'reading' => $reading,
            'form' => $form->createView() ]);
    }
}
