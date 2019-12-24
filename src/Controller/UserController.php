<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Symfony\Component\Mime\Email;
use App\Service\PaginationService;
use App\Service\PasswordGeneratorService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * Affiche la liste des utilisateurs.
     * 
     * @Route("/user/{page<\d+>?1}", name="user")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(int $page, PaginationService $pagination)
    {
        $pagination
            ->setEntityClass(User::class)
            ->setOrderBy(['displayName' => 'ASC'])
            ->setLimit(25)
        ;

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination ]);
    }

    /**
     * Traite l'ajout d'un utilisateur.
     * 
     * @Route("/user/add", name="user_add")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @param ObjectManager $manager
     * @param Request $request
     * @return Response
     */
    public function add(ObjectManager $manager, Request $request, UserPasswordEncoderInterface $encoder, MailerInterface $mailer, PasswordGeneratorService $generator)
    {
        /* Instancier un nouvel utilisateur */
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Générer un mot de passe */
            $password = $generator->generate();
            $user->setPassword($encoder->encodePassword($user, $password));

            /* Associer les rôles à l'utilisateur */
            foreach ($user->getSystemRoles() as $systemRole) {
                $systemRole->setUserAccount($user);
                $manager->persist($systemRole);
            }

            /* Persister l'utilisateur dans la base de données */
            $manager->persist($user);
            $manager->flush();

            /* Transmettre un e-mail de bienvenue à l'utilisateur */
            $host = $this->getUser();
            $email = (new TemplatedEmail())
                ->from($host->getEmail())
                ->to($user->getEmail())
                ->bcc($host->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject("Bienvenue dans le programme Epu-Karst !")
                ->htmlTemplate('user/emails/welcome.html.twig')
                ->context([
                    'user' => $user,
                    'host' => $host,
                    'password' => $password,
                ]);
            $mailer->send($email);

            $this->addFlash('success', "L'utilisateur <strong>$user</strong> a été ajouté avec succès.");

            return $this->redirectToRoute('user');
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Ajouter un utilisateur",
        ]);
    }

    /**
     * Traite l'activation d'un utilisateur.
     * 
     * @Route("/user/{email}/activate", name="user_activate")
     * 
     * @param ObjectManager $manager
     * @param Request $request
     * @return Response
     */
    public function activate(User $user, ObjectManager $manager)
    {
        return $this->render('user/activate.html.twig', [
        ]);
    }

    /**
     * Traite la modification d'un utilisateur.
     *
     * @Route("/user/{id}/modify", name="user_modify")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @param User $user
     * @param ObjectManager $manager
     * @param Request $request
     * @return Response
     */
    public function modify(User $user, ObjectManager $manager, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Associer les rôles à l'utilisateur */
            foreach ($user->getSystemRoles() as $systemRole) {
                $systemRole->setUserAccount($user);
                $manager->persist($systemRole);
            }
            /* Persister l'utilisateur en base de données */
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur <strong>$user</strong> a été enregistré avec succès.");

            return $this->redirectToRoute('user');
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier l'utilisateur $user",
        ]);
    }

    /**
     * Traite le changement du mot de passe de l'utilisateur.
     *
     * @Route("/user/{id}/password", name="user_password")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param ObjectManager $manager
     * @param Request $request
     * @return void
     */
    public function setPassword(User $user, ObjectManager $manager, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Hacher le mot de passe */
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "Le mot de passe de <strong>$user</strong> a été enregistré avec succès.");

            return $this->redirectToRoute('user');
        }

        return $this->render('user/password.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier le mot de passe de $user",
        ]);
    }

    /**
     * Traite la suppression d'un utilisateur.
     * 
     * @Route("/user/{id}/delete", name="user_delete")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param ObjectManager $manager
     * @param Request $request
     * @return void
     */
    public function delete(User $user, ObjectManager $manager, Request $request)
    {
        if ($user === $this->getUser()) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer vous-même votre compte d'utilisateur.");
        } else {
            $form = $this->createFormBuilder()->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->remove($user);
                $manager->flush();
        
                $this->addFlash('success', "L'utilisateur <strong>$user</strong> a été supprimé avec succès.");
            } else {
                return $this->render('user/delete.html.twig', [
                    'form' => $form->createView(),
                    'user' => $user,
                    'title' => "Supprimer le relevé $user",
                ]);
            }
        }

        return $this->redirectToRoute('user');
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
