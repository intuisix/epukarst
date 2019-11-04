<?php

namespace App\Controller;

use App\Repository\ParameterRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParameterController extends AbstractController
{
    /**
     * @Route("/parameter", name="parameter")
     */
    public function index(ParameterRepository $parameterRepository)
    {
        return $this->render('parameter/index.html.twig', [
            'parameters' => $parameterRepository->findAll()
        ]);
    }
}
