<?php

namespace App\Controller;

use App\Entity\System;
use App\Form\SystemType;
use App\Entity\SystemPicture;
use App\Repository\SystemRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SystemController extends AbstractController
{
    /**
     * Affiche l'index des systèmes karstiques.
     * 
     * @Route("/system", name="system")
     */
    public function index(SystemRepository $systemRepository)
    {
        return $this->render('system/index.html.twig', [
            'systems' => $systemRepository->findAll()
        ]);
    }

    /**
     * Traite l'ajout d'un système karstique.
     * 
     * @Route("/system/add", name="system_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function add(ObjectManager $manager, Request $request)
    {
        /* Instancier un nouveau système */
        $system = new System();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemType::class, $system);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les propriétés du système */
            $picturesAdded = $this->update($system, $form, $manager);

            $this->addFlash('success', "Le système <strong>{$system->getName()}</strong> a été créé avec succès.");
    
            if ($picturesAdded) {
                $this->addFlash('info', "Veuillez maintenant compléter les légendes des photos.");
                return $this->redirectToRoute('system_modify', [
                    'slug' => $system->getSlug(),
                ]);
            } else {
                return $this->redirectToRoute('system_show', [
                    'slug' => $system->getSlug(),
                ]);
            }
        }

        return $this->render('system/form.html.twig', [
            'system' => $system,
            'form' => $form->createView(),
            'title' => "Ajouter un nouveau système",
        ]);
    }

    /**
     * Traite la modification d'un système karstique.
     * 
     * @Route("/system/{slug}/modify", name="system_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(System $system, ObjectManager $manager, Request $request)
    {
        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemType::class, $system);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les propriétés du système */
            $picturesAdded = $this->update($system, $form, $manager);

            $this->addFlash('success', "Le système <strong>{$system->getName()}</strong> a été modifié avec succès.");
    
            if ($picturesAdded) {
                $this->addFlash('info', "Veuillez maintenant compléter les légendes des photos.");
                return $this->redirectToRoute('system_modify', [
                    'slug' => $system->getSlug(),
                ]);
            } else {
                return $this->redirectToRoute('system_show', [
                    'slug' => $system->getSlug(),
                ]);
            }
        }

        return $this->render('system/form.html.twig', [
            'system' => $system,
            'form' => $form->createView(),
            'title' => "Modifier le système {$system->getName()}"
        ]);
    }

    /**
     * Traite la demande de suppression d'un système karstique.
     * 
     * @Route("/system/{slug}/delete", name="system_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(System $system, ObjectManager $manager, Request $request)
    {
        if (count($system->getBasins()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer le système <strong>{$system->getName()}</strong> car il possède des bassins.");
        } else {
            $manager->remove($system);
            $manager->flush();
    
            $this->addFlash('success', "Le système <strong>{$system->getName()}</strong> a été supprimé avec succès.");
        }

        return $this->redirectToRoute('system');
    }

    /**
     * Affiche un système karstique.
     * 
     * @Route("/system/{slug}", name="system_show")
     */
    public function show(System $system)
    {
        return $this->render('system/show.html.twig', [
            'system' => $system
        ]);
    }

    /**
     * Met à jour les propriétés du système.
     *
     * @param System $system
     * @return void
     */
    private function update(System $system, $form, ObjectManager $manager)
    {
        foreach ($system->getBasins() as $basin) {
            $basin->setSystem($system);
            $manager->persist($basin);
        }

        foreach ($system->getPictures() as $picture) {
            $picture->setSystem($system);
            $manager->persist($picture);
        }

        foreach ($system->getParameters() as $parameter) {
            $parameter->setSystem($system);
            $manager->persist($parameter);
        }

        $picturesAdded = false;
        foreach ($form['newPictures']->getData() as $uploadedFile)
        {
            $picture = new SystemPicture();
            $picture->setUploadedFile($uploadedFile);
            $system->addPicture($picture);
            $manager->persist($picture);
            $picturesAdded = true;
        }

        $manager->persist($system);
        $manager->flush();

        return $picturesAdded;
    }
}
