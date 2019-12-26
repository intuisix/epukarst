<?php

namespace App\Controller;

use App\Entity\Alarm;
use App\Form\AlarmType;
use App\Service\PaginationService;
use App\Repository\AlarmRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/alarm")
 */
class AlarmController extends AbstractController
{
    /**
     * @Route("/", name="alarm_index", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function index(AlarmRepository $alarmRepository, PaginationService $pagination): Response
    {
        $pagination
            ->setEntityClass(Alarm::class)
            ->setOrderBy(['reportingDate' => 'DESC'])
            ->setLimit(25);

        return $this->render('alarm/index.html.twig', [
            'alarms' => $alarmRepository->findAll(),
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="alarm_new", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request): Response
    {
        /* Créer une nouvelle alarme */
        $alarm = new Alarm();
        $alarm
            ->setReportingAuthor($this->getUser())
            ->setReportingDate(new \DateTime('now'));

        /* Créer et traiter le formulaire */
        $form = $this->createForm(AlarmType::class, $alarm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($alarm);
            $entityManager->flush();

            return $this->redirectToRoute('alarm_index');
        }

        return $this->render('alarm/new.html.twig', [
            'alarm' => $alarm,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="alarm_show", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function show(Alarm $alarm): Response
    {
        return $this->render('alarm/show.html.twig', [
            'alarm' => $alarm,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="alarm_edit", methods={"GET","POST"})
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="alarm")
     */
    public function edit(Request $request, Alarm $alarm): Response
    {
        $form = $this->createForm(AlarmType::class, $alarm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('alarm_index');
        }

        return $this->render('alarm/edit.html.twig', [
            'alarm' => $alarm,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="alarm_delete")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="alarm")
     */
    public function delete(Alarm $alarm, Request $request, ObjectManager $manager): Response
    {
        /* Créer et traiter le formulaire de confirmation */
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* Supprimer l'alarme */
            $manager->remove($alarm);
            $manager->flush();
            $this->addFlash('success', "L'alarme relative à <strong>{$alarm->getSystem()->getName()}</strong> a été supprimée avec succès.");    
            return $this->redirectToRoute('alarm_index');
        }

        return $this->render('alarm/delete.html.twig', [
            'form' => $form->createView(),
            'alarm' => $alarm,
        ]);
    }
}
