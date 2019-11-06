<?php

namespace App\Controller;

use App\Repository\ParameterRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParameterController extends AbstractController
{
    /**
     * @Route("/parameter", name="parameter")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(ParameterRepository $parameterRepository)
    {
        return $this->render('parameter/index.html.twig', [
            'parameters' => $parameterRepository->findAll()
        ]);
    }
}
