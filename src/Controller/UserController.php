<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController' ]);
    }

  /**
     * Gère le formulaire de connexion.
     * 
     * @Route("/login", name="user_login")
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $utils) {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('user/login.html.twig', [
                'hasError' => $error != null,
                'username' => $username ]);
    }

    /**
     * Déconnecte l'utilisateur.
     *
     * @Route("/logout", name="user_logout")
     * 
     * @return void
     */
    public function logout() {
        /* Rien, car Symfony s'occupe de tout */
    }
}
