<?php

namespace App\Controller;

use App\Entity\System;
use App\Entity\SystemParameter;
use App\Form\SystemParameterType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SystemParameterController extends AbstractController
{
    /**
     * @Route("/system-parameter/{code}", name="system_parameter")
     * @IsGranted("SYSTEM_ENCODE", subject="system")
     */
    public function index(System $system)
    {
        return $this->render('system_parameter/index.html.twig', [
            'system' => $system,
            'title' => "Paramètres de {$system->getName()}",
        ]);
    }

    /**
     * @Route("/system-parameter/create/{code}", name="system_parameter_create")
     * @IsGranted("SYSTEM_ENCODE", subject="system")
     */
    public function create(System $system, ObjectManager $manager, Request $request)
    {
        /* Créer une instance de paramètre de système */
        $systemParameter = new SystemParameter();
        $systemParameter->setSystem($system);

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemParameterType::class, $systemParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Persister le paramètre en base de données */
            $manager->persist($systemParameter);
            $manager->flush();

            $this->addFlash('success', "Le paramètre <strong>{$systemParameter->getName()}</strong> a été enregistré avec succès.");

            return $this->redirectToRoute('system_parameter', [
                'code' => $system->getCode()]);
        }

        return $this->render('system_parameter/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Ajouter un paramètre à {$system->getName()}",
            'system' => $system,
        ]);
    }

    /**
     * @Route("/system-parameter/update/{id}", name="system_parameter_update")
     * @IsGranted("SYSTEM_ENCODE", subject="systemParameter")
     */
    public function update(SystemParameter $systemParameter, ObjectManager $manager, Request $request)
    {
        $system = $systemParameter->getSystem();

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemParameterType::class, $systemParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Persister le paramètre en base de données */
            $manager->persist($systemParameter);
            $manager->flush();

            $this->addFlash('success', "Le paramètre <strong>{$systemParameter->getName()}</strong> a été enregistré avec succès.");

            return $this->redirectToRoute('system_parameter', [
                'code' => $system->getCode()]);
        }

        return $this->render('system_parameter/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier un paramètre de {$system->getName()}",
            'system' => $system,
        ]);
    }

    /**
     * @Route("/system-parameter/delete/{id}", name="system_parameter_delete")
     * @IsGranted("SYSTEM_ENCODE", subject="systemParameter")
     */
    public function delete(SystemParameter $systemParameter, ObjectManager $manager, Request $request)
    {
        $system = $systemParameter->getSystem();

        /* Créer et traiter le formulaire de confirmation */
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Supprimer le paramètre */
            $manager->remove($systemParameter);
            $manager->flush();

            $this->addFlash('success', "Le paramètre <strong>{$systemParameter->getName()}</strong> a été supprimé avec succès.");
        } else {
            return $this->render('system_parameter/delete.html.twig', [
                'form' => $form->createView(),
                'title' => "Supprimer le paramètre {$systemParameter->getName()}",
                'systemParameter' => $systemParameter,
            ]);
        }

        return $this->redirectToRoute('system_parameter', [
            'code' => $system->getCode(),
        ]);
    }
}
