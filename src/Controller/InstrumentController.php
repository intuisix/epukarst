<?php

namespace App\Controller;

use App\Repository\InstrumentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InstrumentController extends AbstractController
{
    /**
     * @Route("/instrument", name="instrument")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(InstrumentRepository $instrumentRepository)
    {
        return $this->render('instrument/index.html.twig', [
            'instruments' => $instrumentRepository->findAll()
        ]);
    }
}
