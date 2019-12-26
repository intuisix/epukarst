<?php

namespace App\Controller;

use App\Entity\AlarmKind;
use App\Form\AlarmKindType;
use App\Repository\AlarmKindRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlarmKindController extends AbstractController
{
    /**
     * @Route("/alarm-kind", name="alarm_kind")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(AlarmKindRepository $repository)
    {
        return $this->render('alarm_kind/index.html.twig', [
            'alarmKinds' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/alarm-kind/create", name="alarm_kind_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request)
    {
        /* Instancier un nouveau genre d'alarme */
        $alarmKind = new AlarmKind();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(AlarmKindType::class, $alarmKind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($alarmKind);
            $manager->flush();
            
            $this->addFlash('success', "Le genre d'alarme <strong>{$alarmKind->getName()}</strong> a été créé avec succès.");
    
            return $this->redirectToRoute('alarm_kind');
        }

        return $this->render('alarm_kind/form.html.twig', [
            'alarmKind' => $alarmKind,
            'form' => $form->createView(),
            'title' => "Créer un nouveau genre d'alarme",
        ]);
    }

    /**
     * @Route("/alarm-kind/{id}/modify", name="alarm_kind_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(AlarmKind $alarmKind, ObjectManager $manager, Request $request)
    {
        /* Créer et traiter le formulaire */
        $form = $this->createForm(AlarmKindType::class, $alarmKind);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($alarmKind);
            $manager->flush();
            
            $this->addFlash('success', "Le genre d'alarme <strong>{$alarmKind->getName()}</strong> a été créé avec succès.");
    
            return $this->redirectToRoute('alarm_kind');
        }

        return $this->render('alarm_kind/form.html.twig', [
            'alarmKind' => $alarmKind,
            'form' => $form->createView(),
            'title' => "Modifier le genre d'alarme {$alarmKind->getName()}",
        ]);
    }

    /**
     * @Route("/alarm-kind/{id}/delete", name="alarm_kind_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(AlarmKind $alarmKind, ObjectManager $manager)
    {
        if (count($alarmKind->getAlarms()) > 0) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer le genre d'alarme <strong>{$alarmKind->getName()}</strong> car il est associé à des alarmes.");
        } else {
            $manager->remove($alarmKind);
            $manager->flush();

            $this->addFlash('success', "Le genre d'alarme <strong>{$alarmKind->getName()}</strong> a été supprimé avec succès.");
        }

        return $this->redirectToRoute('alarm_kind');
    }
}
