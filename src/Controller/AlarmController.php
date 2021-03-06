<?php

namespace App\Controller;

use App\Entity\Alarm;
use App\Form\AlarmType;
use App\Service\Breadcrumbs;
use App\Service\PaginationService;
use App\Repository\AlarmRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(AlarmRepository $alarmRepository, PaginationService $pagination, Breadcrumbs $breadcrumbs): Response
    {
        $breadcrumbs->reset("Liste des alarmes");

        $pagination
            ->setEntityClass(Alarm::class)
            ->setOrderBy(['reportingDate' => 'DESC'])
            ->setLimit(25);

        return $this->render('alarm/index.html.twig', [
            'alarms' => $alarmRepository->findAll(),
            'pagination' => $pagination,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/new", name="alarm_new", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request, Breadcrumbs $breadcrumbs): Response
    {
        $breadcrumbs->add("Créer une alarme");

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

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('alarm/new.html.twig', [
            'alarm' => $alarm,
            'form' => $form->createView(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/{id}", name="alarm_show", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function show(Alarm $alarm, Breadcrumbs $breadcrumbs): Response
    {
        $breadcrumbs->add("Visualiser une alarme", 'alarm');

        return $this->render('alarm/show.html.twig', [
            'alarm' => $alarm,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="alarm_edit", methods={"GET","POST"})
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="alarm")
     */
    public function edit(Request $request, Alarm $alarm, Breadcrumbs $breadcrumbs): Response
    {
        $breadcrumbs->add("Modifier une alarme");

        $form = $this->createForm(AlarmType::class, $alarm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', "L'alarme du <strong>{$alarm->getReportingDate()->format('d/m/Y')}</strong> relative à <strong>{$alarm->getSystem()->getName()}</strong> a été modifiée avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('alarm/edit.html.twig', [
            'alarm' => $alarm,
            'form' => $form->createView(),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="alarm_delete")
     * @IsGranted("SYSTEM_CONTRIBUTOR", subject="alarm")
     */
    public function delete(Alarm $alarm, Request $request, EntityManagerInterface $manager, Breadcrumbs $breadcrumbs): Response
    {
        $breadcrumbs->add("Supprimer une alarme");

        if ((0 !== count($alarm->getMeasures())) || (0 !== count($alarm->getSystemReadings()))) {
            $this->addFlash('danger', "L'alarme du <strong>{$alarm->getReportingDate()->format('d/m/Y')}</strong> relative à <strong>{$alarm->getSystem()->getName()}</strong> ne peut pas être supprimée car elle est liée à des fiches ou à des mesures.");
            return $this->redirect($breadcrumbs->getPrevious());
        }

        /* Créer et traiter le formulaire de confirmation */
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* Supprimer l'alarme */
            $manager->remove($alarm);
            $manager->flush();
            $this->addFlash('success', "L'alarme du <strong>{$alarm->getReportingDate()->format('d/m/Y')}</strong> relative à <strong>{$alarm->getSystem()->getName()}</strong> a été supprimée avec succès.");
            return $this->redirect($breadcrumbs->getPrevious('alarm'));
        }

        return $this->render('alarm/delete.html.twig', [
            'form' => $form->createView(),
            'alarm' => $alarm,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
