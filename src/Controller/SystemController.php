<?php

namespace App\Controller;

use App\Entity\System;
use App\Form\SystemType;
use App\Service\Breadcrumbs;
use App\Entity\SystemPicture;
use App\Entity\SystemStations;
use App\Form\SystemStationsType;
use App\Service\PaginationService;
use App\Repository\SystemRepository;
use App\Repository\StationRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur permettant la gestion des systèmes.
 */
class SystemController extends AbstractController
{
    /**
     * Affiche l'index des systèmes karstiques.
     * 
     * @Route("/system", name="system")
     */
    public function index(SystemRepository $systemRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Index des systèmes");

        return $this->render('system/index.html.twig', [
            'systems' => $systemRepository->findAllOrdered(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche la liste des systèmes karstiques.
     * 
     * @Route("/system/list/{page<\d+>?1}", name="system_list")
     * @IsGranted("ROLE_ADMIN")
     */
    public function list(int $page, SystemRepository $systemRepository, PaginationService $pagination, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des systèmes");

        $pagination
            ->setEntityClass(System::class)
            ->setOrderBy(['code' => 'ASC'])
            ->setPage($page);

        return $this->render('system/list.html.twig', [
            'pagination' => $pagination,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la création d'un système karstique.
     * 
     * @Route("/system/create", name="system_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Création d'un système");

        /* Instancier un nouveau système */
        $system = new System();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemType::class, $system, [
            'picture_files' => SystemPicture::scanPicturesDir('images/systems'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les propriétés du système */
            $picturesAdded = $this->storeSystem($system, $form, $manager);

            $this->addFlash('success', "Le système <strong>{$system->getName()}</strong> a été créé avec succès.");

            if ($picturesAdded) {
                $this->addFlash('info', "Veuillez maintenant compléter les légendes des photos.");
                $breadcrumbs->removeLast();
                return $this->redirectToRoute('system_modify', [
                    'code' => $system->getCode(),
                ]);
            } else {
                return $this->redirect($breadcrumbs->getPrevious());
            }
        }

        return $this->render('system/form.html.twig', [
            'system' => $system,
            'form' => $form->createView(),
            'title' => "Ajouter un nouveau système",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la modification d'un système karstique.
     * 
     * @Route("/system/{code}/modify", name="system_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(System $system, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'un système");

        /* Créer et traiter le formulaire */
        $form = $this->createForm(SystemType::class, $system, [
            'picture_files' => SystemPicture::scanPicturesDir('images/systems'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les propriétés du système */
            $picturesAdded = $this->storeSystem($system, $form, $manager);

            $this->addFlash('success', "Le système <strong>{$system->getName()}</strong> a été modifié avec succès.");

            if ($picturesAdded) {
                $this->addFlash('info', "Veuillez maintenant compléter les légendes des photos.");
                return $this->redirectToRoute('system_modify', [
                    'code' => $system->getCode(),
                ]);
            } else {
                return $this->redirect($breadcrumbs->getPrevious());
            }
        }

        return $this->render('system/form.html.twig', [
            'system' => $system,
            'form' => $form->createView(),
            'title' => "Modifier le système {$system->getName()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la demande de suppression d'un système karstique.
     * 
     * @Route("/system/{code}/delete", name="system_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(System $system, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'un système");

        if ((count($system->getSystemReadings()) > 0) ||
            (count($system->getAlarms()) > 0)) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer le système <strong>{$system->getName()}</strong> car il possède des fiches ou des alarmes.");
        } elseif (count($system->getBasins()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer le système <strong>{$system->getName()}</strong> car il possède des bassins.");
        } else {
            $form = $this->createFormBuilder()->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->remove($system);
                $manager->flush();
                $this->addFlash('success', "Le système <strong>{$system->getName()}</strong> a été supprimé avec succès.");

                /* Rediriger vers la liste des systèmes car celui-ci n'existe plus */
                return $this->redirectToRoute('system_list');
            } else {
                return $this->render('system/delete.html.twig', [
                    'form' => $form->createView(),
                    'system' => $system,
                    'title' => "Supprimer le système {$system->getName()}",
                    'breadcrumbs' => $breadcrumbs,
                ]);
            }
        }

        return $this->redirect($breadcrumbs->getPrevious());
    }

    /**
     * Affiche un système karstique.
     * 
     * @Route("/system/{slug}", name="system_show")
     */
    public function show(System $system, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Visualisation d'un système");

        return $this->render('system/show.html.twig', [
            'system' => $system,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche les paramètres d'un système karstique.
     * 
     * @Route("/system/{slug}/parameters", name="system_show_parameters")
     */
    public function showParameters(System $system, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Liste des paramètres d'un système");

        $parameters = [];
        foreach ($system->getParameters() as $systemParameter) {
            $parameter = $systemParameter->getInstrumentParameter()->getParameter();
            if (!in_array($parameter, $parameters)) {
                $parameters[] = $parameter;
            }
        }

        return $this->render('system/parameters.html.twig', [
            'system' => $system,
            'parameters' => $parameters,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Affiche les instruments d'un système karstique.
     * 
     * @Route("/system/{slug}/instruments", name="system_show_instruments")
     */
    public function showInstruments(System $system, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Liste des instruments d'un système");

        /* Trouver tous les instruments de chaque chaîne de mesure du système */
        $instruments = [];
        foreach ($system->getParameters() as $systemParameter) {
            $instrument = $systemParameter->getInstrumentParameter()->getInstrument();
            $instrument->findAllRequiredInstruments($instruments);
        }

        return $this->render('system/instruments.html.twig', [
            'system' => $system,
            'instruments' => $instruments,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Mémorise le système karstique dans la base de données.
     *
     * @param System $system
     * @return bool True si des images ont été ajoutées.
     */
    private function storeSystem(System $system, $form, ObjectManager $manager)
    {
        /* Lier les bassins au système */
        foreach ($system->getBasins() as $basin) {
            $basin->setSystem($system);
            $manager->persist($basin);
        }

        /* Lier les photographies au système */
        foreach ($system->getPictures() as $picture) {
            $picture->setSystem($system);
            $manager->persist($picture);
        }

        /* Ajouter les photographies qui viennent d'être chargées */
        $picturesAdded = false;
        foreach ($form['newPictures']->getData() as $uploadedFile)
        {
            $picture = new SystemPicture();
            $picture->setUploadedFile($uploadedFile);
            $system->addPicture($picture);
            $manager->persist($picture);
            $picturesAdded = true;
        }

        /* Lier les paramètres au système */
        foreach ($system->getParameters() as $parameter) {
            $parameter->setSystem($system);
            $manager->persist($parameter);
        }

        /* Lier les rôles au système */
        foreach ($system->getSystemRoles() as $systemRole) {
            $systemRole->setSystem($system);
            $manager->persist($systemRole);
        }

        $manager->persist($system);
        $manager->flush();

        return $picturesAdded;
    }
}
