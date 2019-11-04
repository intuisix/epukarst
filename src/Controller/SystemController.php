<?php

namespace App\Controller;

use App\Entity\System;
use App\Repository\SystemRepository;
use Symfony\Component\Routing\Annotation\Route;
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
     * Affiche l'article d'un système karstique.
     * 
     * @Route("/system/{slug}", name="system_show")
     */
    public function show(System $system)
    {
        return $this->render('system/show.html.twig', [
            'system' => $system
        ]);
    }
}
