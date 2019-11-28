<?php

namespace App\Controller;

use App\Entity\Parameter;
use App\Form\ParameterType;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParameterController extends AbstractController
{
    /**
     * Affiche l'index des paramètres.
     * 
     * @Route("/parameter", name="parameter")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(ParameterRepository $parameterRepository) {
        return $this->render('parameter/index.html.twig', [
            'parameters' => $parameterRepository->findAll()
        ]);
    }

    /**
     * Traite le formulaire de création d'un paramètre.
     * 
     * @Route("/parameter/create", name="parameter_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request) {
        /* Instancier un nouveau paramètre */
        $parameter = new Parameter();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(ParameterType::class, $parameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($parameter);
            $manager->flush();

            $this->addFlash('success', "Le paramètre <strong>{$parameter->getName()}</strong> a été créé avec succès.");

            return $this->redirectToRoute('parameter');
        }

        return $this->render('parameter/form.html.twig', [
            'parameter' => $parameter,
            'form' => $form->createView(),
            'title' => "Ajouter un nouveau paramètre",
        ]);
    }

    /**
     * Traite le formulaire de modification d'un paramètre.
     * 
     * @Route("/parameter/{id}/modify", name="parameter_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(Parameter $parameter, ObjectManager $manager, Request $request) {
        /* Créer et traiter le formulaire */
        $form = $this->createForm(ParameterType::class, $parameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($parameter);
            $manager->flush();

            $this->addFlash('success', "Le paramètre <strong>{$parameter->getName()}</strong> a été modifié avec succès.");

            return $this->redirectToRoute('parameter');
        }

        return $this->render('parameter/form.html.twig', [
            'parameter' => $parameter,
            'form' => $form->createView(),
            'title' => "Modifier le paramètre {$parameter->getName()}",
        ]);
    }

    /**
     * Traite la demande de suppression d'un paramètre.
     *
     * @Route("/parameter/{id}/delete", name="parameter_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Parameter $parameter, ObjectManager $manager) {
        $manager->remove($parameter);
        $manager->flush();

        $this->addFlash('success', "Le paramètre <strong>{$parameter->getName()}</strong> a été supprimé avec succès.");

        return $this->redirectToRoute('parameter');
    }
}
