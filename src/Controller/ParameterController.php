<?php

namespace App\Controller;

use App\Entity\Parameter;
use App\Form\ParameterType;
use App\Service\Breadcrumbs;
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
    public function index(ParameterRepository $parameterRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des paramètres");

        return $this->render('parameter/index.html.twig', [
            'parameters' => $parameterRepository->findAllOrdered(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite le formulaire de création d'un paramètre.
     * 
     * @Route("/parameter/create", name="parameter_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request, ParameterRepository $parameterRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Création d'un paramètre");

        /* Créer un tableau des paramètres ordonnés */
        $orderedParameters = $parameterRepository->findAllOrdered();
        $positions = $this->getPositions($orderedParameters);

        /* Instancier un nouveau paramètre */
        $parameter = new Parameter();

        /* Créer et traiter le formulaire */
        $form = $this->createForm(ParameterType::class, $parameter, [
            'positions' => $positions,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les positions des paramètres */
            $this->updatePositions($orderedParameters, $parameter);
            $this->updateChoices($parameter, $manager);

            /* Persister le paramètre en base de données */
            $manager->persist($parameter);
            $manager->flush();

            $this->addFlash('success', "Le paramètre <strong>{$parameter->getName()}</strong> a été créé avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('parameter/form.html.twig', [
            'parameter' => $parameter,
            'form' => $form->createView(),
            'title' => "Ajouter un nouveau paramètre",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite le formulaire de modification d'un paramètre.
     * 
     * @Route("/parameter/{id}/modify", name="parameter_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(Parameter $parameter, ObjectManager $manager, Request $request, ParameterRepository $parameterRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'un paramètre");

        /* Créer un tableau des paramètres ordonnés */
        $orderedParameters = $parameterRepository->findAllOrdered();
        $positions = $this->getPositions($orderedParameters);

        /* Créer et traiter le formulaire */
        $form = $this->createForm(ParameterType::class, $parameter, [
            'positions' => $positions,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les positions des paramètres */
            $this->updatePositions($orderedParameters, $parameter);
            $this->updateChoices($parameter, $manager);

            /* Persister les modifications dans la base de données */
            $manager->persist($parameter);
            $manager->flush();

            $this->addFlash('success', "Le paramètre <strong>{$parameter->getName()}</strong> a été modifié avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('parameter/form.html.twig', [
            'parameter' => $parameter,
            'form' => $form->createView(),
            'title' => "Modifier le paramètre {$parameter->getName()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la demande de suppression d'un paramètre.
     *
     * @Route("/parameter/{id}/delete", name="parameter_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Parameter $parameter, ObjectManager $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'un paramètre");

        /* Créer et traiter le formulaire de confirmation */
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* Supprimer le paramètre */
            $manager->remove($parameter);
            $manager->flush();
    
            $this->addFlash('success', "Le paramètre <strong>{$parameter->getName()}</strong> a été supprimé avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious('parameter'));
        }

        return $this->render('parameter/delete.html.twig', [
            'form' => $form->createView(),
            'parameter' => $parameter,
            'title' => "Supprimer le paramètre {$parameter->getName()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Crée un tableau listant les positions des paramètres ordonnés, en vue de
     * fournir les choix pour la sélection de position. 
     * 
     * Réassigne aussi la position pour chaque paramètre, dans l'ordre
     * séquentiel (0, 1, 2, ...). Cela est utile lorsque les paramètres ont été
     * manipulés directement dans la base de données, avec une position nulle,
     * ou avec des trous ou des doublons.
     *
     * @param array $orderedParameters: tableau des paramètres, dans l'ordre
     * souhaité.
     * @return array Tableau associatif avec, pour clé, le nom du paramètre et,
     * pour valeur, la position de celui-ci, sous forme d'entier numérique.
     */
    private function getPositions(array $orderedParameters)
    {
        $positions = [];
        foreach ($orderedParameters as $orderedPosition => $orderedParameter) {
            /* Réassigner la position du paramètre */
            $orderedParameter->setPosition($orderedPosition);
            /* Ajouter le paramètre au tableau */
            $positions[$orderedParameter->getName()] = $orderedPosition;
        }
        return $positions;
    }

    /**
     * Met à jour les positions des paramètres d'après la position demandée
     * pour le paramètre ajouté ou modifié.
     *
     * @param array $orderedParameters: tableau des paramètres, dans l'ordre
     * initial.
     * @param Parameter $parameter: paramètre ayant été ajouté ou modifié.
     * @return void
     */
    private function updatePositions(array $orderedParameters, Parameter $parameter)
    {
        /* Déplacer le paramètre dans le tableau ordonné */
        $initialPosition = array_search($parameter, $orderedParameters);
        $finalPosition = $parameter->getPosition() ?? count($orderedParameters);
        if ($initialPosition !== false) {
            array_splice($orderedParameters, $initialPosition, 1);
        }
        array_splice($orderedParameters, $finalPosition, 0, [$parameter]);

        /* Réassigner toutes les positions d'après le tableau ordonné; pour les
        paramètres existants, Doctrine détectera la modification éventuelle et
        transfèrera en base de données */
        foreach ($orderedParameters as $orderedPosition => $orderedParameter) {
            $orderedParameter->setPosition($orderedPosition);
        }
    }

    private function updateChoices(Parameter $parameter, ObjectManager $manager)
    {
        /* Persister les choix du paramètre */
        foreach ($parameter->getChoices() as $choice) {
            $choice->setParameter($parameter);
            $manager->persist($choice);
        }
    }
}
