<?php

namespace App\Controller;

use App\Entity\Reading;
use App\Form\ReadingType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReadingController extends AbstractController
{
    /**
     * @Route("/reading", name="reading")
     */
    public function index()
    {
        return $this->render('reading/index.html.twig', [
            'controller_name' => 'ReadingController',
        ]);
    }

    /**
     * Crée un nouveau relevé.
     * 
     * @Route("/reading/add", name="reading_add")
     */
    public function add(Request $request) {
        /* Créer l'objet */
        $reading = new Reading();
        /* Créer le formulaire */
        $form = $this->createForm(ReadingType::class, $reading);
        /* Récupérer les champs de la requête vers le formulaire */
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Persister le relevé */
            $manager->persist($reading);
            $manager->flush();
            
            /* Ajouter un flash */
            $this->addFlash('success', "Le relevé <strong>{$reading->getName()}</strong> a été créé");
    
            /* Afficher le relevé persisté */
            return $this->redirectToRoute('reading_show', [
                'code' => $reading->getCode() ]);
        }

        return $this->render('reading/add.html.twig', [
            'form' => $form->createView() ]);
    }

    /**
     * Montre un relevé existant.
     * 
     * @Route("/reading/{code}", name="reading_show")
     */
    public function show(string $code) {
        return $this->render('reading/show.html.twig', [
            'code' => $code ]);
    }
}
