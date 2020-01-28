<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\UserPassword;
use App\Form\UserPasswordType;
use Symfony\Component\Mime\Email;
use App\Service\PaginationService;
use Symfony\Component\Mime\Address;
use Symfony\Component\Form\FormError;
use App\Service\PasswordGeneratorService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Contrôleur permettant la gestion des comptes d'utilisateurs: création, liste,
 * modification, activation, changement ou réinitialisation de mot de passe, et
 * suppression.
 */
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
     * @param User $account
     * @param ObjectManager $manager
     * @param Request $request
     * @return Response
     */
    public function modify(User $account, ObjectManager $manager, Request $request)
    {
        if (!$this->isGranted($account->getMainRole())) {
            $this->addFlash('danger', "Vous ne pouvez pas modifier l'utilisateur <strong>$account</strong> car son rôle est plus élevé que le vôtre.");
            return $this->redirectToRoute('user');
        }

        $form = $this->createForm(UserType::class, $account, [
            'superAdmin' => $this->isGranted('ROLE_SUPER_ADMIN'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Associer les rôles à l'utilisateur */
            foreach ($account->getSystemRoles() as $systemRole) {
                $systemRole->setUserAccount($account);
                $manager->persist($systemRole);
            }
            /* Persister l'utilisateur en base de données */
            $manager->persist($account);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur <strong>$account</strong> a été enregistré avec succès.");

            return $this->redirectToRoute('user');
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier l'utilisateur $account",
        ]);
    }

    /**
     * Traite le changement du mot de passe de l'utilisateur.
     * 
     * Cette requête est autorisée:
     * - aux administrateurs, qui peuvent modifier les mots de passe de tous
     *   utilisateurs,
     * - aux non-administrateurs, qui peuvent modifier seulement leur propre
     *   mot de passe.
     *
     * @Route("/user/{id}/password", name="user_password")
     * @Security("is_granted('ROLE_ADMIN') or user === account")
     *
     * @param User $account
     * @param ObjectManager $manager
     * @param Request $request
     * @return void
     */
    public function setPassword(User $account, ObjectManager $manager, Request $request, UserPasswordEncoderInterface $encoder, MailerInterface $mailer)
    {
        if (!$this->isGranted($account->getMainRole())) {
            $this->addFlash('danger', "Vous ne pouvez pas changer le mot de passe l'utilisateur <strong>$account</strong> car son rôle est plus élevé que le vôtre.");
            return $this->redirectToRoute('user');
        }

        /* Déterminer l'utilisateur réalisant le changement de mot de passe: ce peut être soit un administrateur, soitl'utilisateur lui-même */
        $currentUser = $this->getUser();
        $onBehalf = $account !== $currentUser;

        /* Créer les données du formulaire */
        $passwordData = new UserPassword();

        /* Créer le formulaire, en spécifiant si l'utilisateur est en train de changer son propre mot de passe ou celui d'un autre */
        $form = $this->createForm(UserPasswordType::class, $passwordData, [
            'onBehalf' => $onBehalf]);

        /* Traiter le formulaire */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* Vérifier le mot de passe actuel */
            if (password_verify($passwordData->getCurrentPassword(), $currentUser->getPassword())) {
                /* Hacher le nouveau mot de passe */
                $account->setPassword($encoder->encodePassword($account, $passwordData->getWishedPassword()));
                $manager->persist($account);
                $manager->flush();

                /* Transmettre un e-mail à l'utilisateur */
                $mailerName = $_ENV['MAILER_NAME'];
                $mailerEmail = $_ENV['MAILER_EMAIL'];
                $email = (new TemplatedEmail())
                    ->from(new Address($mailerEmail, $mailerName))
                    ->to($account->getEmail())
                    ->bcc(new Address($mailerEmail, $mailerName))
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject("Votre mot de passe Epu-Karst a été changé")
                    ->htmlTemplate('user/emails/passwordChanged.html.twig')
                    ->context([
                        'user' => $account,
                        'host' => $currentUser,
                        'mailerName' => $mailerName,
                        'password' => $passwordData->getRevealInEmail() ? $passwordData->getWishedPassword() : null,
                    ]);
                $mailer->send($email);

                /* Ajouter un flash de succès et rediriger soit vers la gestion des utilisateurs, soit vers la page d'accueil */
                if ($onBehalf) {
                    $this->addFlash('success', "Le mot de passe de <strong>$account</strong> a été enregistré avec succès.");
                    return $this->redirectToRoute('user');
                } else {
                    $this->addFlash('success', "Votre mot de passe a été enregistré avec succès.");
                    return $this->redirectToRoute('home');
                }
            } else {
                $form->get('currentPassword')->addError(
                    new FormError("Le mot de passe que vous avez introduit n'est pas votre mot de passe actuel."));
            }
        }

        return $this->render('user/password.html.twig', [
            'form' => $form->createView(),
            'title' => ($account !== $currentUser) ? "Modifier le mot de passe de $account" : "Modifier votre mot de passe",
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
