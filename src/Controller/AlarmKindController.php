<?php

namespace App\Controller;

use App\Entity\AlarmKind;
use App\Form\AlarmKindType;
use App\Service\Breadcrumbs;
use App\Repository\AlarmKindRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlarmKindController extends AbstractController
{
    /**
     * @Route("/alarm-kind", name="alarm_kind")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(AlarmKindRepository $repository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des types d'alarmes");

        return $this->render('alarm_kind/index.html.twig', [
            'alarmKinds' => $repository->findAll(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/alarm-kind/create", name="alarm_kind_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Créer un type d'alarme");

        /* Instancier un nouveau type d'alarme */
        $alarmKind = new AlarmKind();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(AlarmKindType::class, $alarmKind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($alarmKind);
            $manager->flush();
            
            $this->addFlash('success', "Le type d'alarme <strong>{$alarmKind->getName()}</strong> a été créé avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('alarm_kind/form.html.twig', [
            'alarmKind' => $alarmKind,
            'form' => $form->createView(),
            'title' => "Créer un nouveau type d'alarme",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/alarm-kind/{id}/modify", name="alarm_kind_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(AlarmKind $alarmKind, EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modifier un type d'alarme");

        /* Créer et traiter le formulaire */
        $form = $this->createForm(AlarmKindType::class, $alarmKind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($alarmKind);
            $manager->flush();
            
            $this->addFlash('success', "Le type d'alarme <strong>{$alarmKind->getName()}</strong> a été modifié avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('alarm_kind/form.html.twig', [
            'alarmKind' => $alarmKind,
            'form' => $form->createView(),
            'title' => "Modifier le type d'alarme {$alarmKind->getName()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/alarm-kind/{id}/delete", name="alarm_kind_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(AlarmKind $alarmKind, EntityManagerInterface $manager, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Supprimer un type d'alarme");

        if (count($alarmKind->getAlarms()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer le type d'alarme <strong>{$alarmKind->getName()}</strong> car il est associé à des alarmes.");

            return $this->redirect($breadcrumbs->getPrevious());
        } else {
            $manager->remove($alarmKind);
            $manager->flush();
    
            $this->addFlash('success', "Le type d'alarme <strong>{$alarmKind->getName()}</strong> a été supprimé avec succès.");
            return $this->redirect($breadcrumbs->getPrevious('alarm_kind'));
        }
    }
}
