<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\UserPassword;
use App\Service\Breadcrumbs;
use App\Form\UserPasswordType;
use Symfony\Component\Mime\Email;
use App\Service\PaginationService;
use Symfony\Component\Mime\Address;
use Symfony\Component\Form\FormError;
use App\Service\PasswordGeneratorService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(int $page, PaginationService $pagination, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des utilisateurs");

        if (empty($_ENV['MAILER_NAME']) || empty($_ENV['MAILER_EMAIL'])) {
            $this->addFlash('warning', "L'envoi d'e-mails ne fonctionnera pas parce que les variables MAILER_NAME et MAILER_EMAIL ne sont pas définies dans votre environnement.");
            dump($_ENV);
        }

        $pagination
            ->setEntityClass(User::class)
            ->setOrderBy(['displayName' => 'ASC'])
            ->setLimit(25)
        ;

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite l'ajout d'un utilisateur.
     * 
     * @Route("/user/add", name="user_add")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    public function add(EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder, MailerInterface $mailer, PasswordGeneratorService $generator, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Création d'un utilisateur");

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
            $creator = $this->getUser();
            $mailerName = $_ENV['MAILER_NAME'];
            $mailerEmail = $_ENV['MAILER_EMAIL'];
            if (!empty($mailerName) && !empty($mailerEmail)) {
                $sender = new Address($mailerEmail, $mailerName);
                $email = (new TemplatedEmail())
                    ->from($sender)
                    ->to($user->getEmail())
                    ->bcc($sender, $creator->getEmail())
                    ->subject("Bienvenue dans le programme Epu-Karst !")
                    ->htmlTemplate('user/emails/welcome.html.twig')
                    ->context([
                        'user' => $user,
                        'host' => $creator,
                        'password' => $password,
                        'mailerName' => $mailerName,
                    ]);
                $mailer->send($email);
            }

            $this->addFlash('success', "L'utilisateur <strong>$user</strong> a été ajouté avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Ajouter un utilisateur",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la modification d'un utilisateur.
     *
     * @Route("/user/{id}/modify", name="user_modify")
     * @IsGranted("ROLE_ADMIN")
     * 
     * @param User $account
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    public function modify(User $account, EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'un utilisateur");

        if (!$this->isGranted($account->getMainRole())) {
            $this->addFlash('danger', "Vous ne pouvez pas modifier l'utilisateur <strong>$account</strong> car son rôle est plus élevé que le vôtre.");
            return $this->redirect($breadcrumbs->getPrevious());
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

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'title' => "Modifier l'utilisateur $account",
            'breadcrumbs' => $breadcrumbs,
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
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return void
     */
    public function setPassword(User $account, EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder, MailerInterface $mailer, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Mot de passe d'un utilisateur");

        if (!$this->isGranted($account->getMainRole())) {
            $this->addFlash('danger', "Vous ne pouvez pas changer le mot de passe l'utilisateur <strong>$account</strong> car son rôle est plus élevé que le vôtre.");
            return $this->redirect($breadcrumbs->getPrevious());
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
                if (!empty($mailerName) && !empty($mailerEmail)) {
                    $sender = new Address($mailerEmail, $mailerName);
                    $email = (new TemplatedEmail())
                        ->from($sender)
                        ->to($account->getEmail())
                        ->bcc($sender)
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
                }

                if ($onBehalf) {
                    $this->addFlash('success', "Le mot de passe de <strong>$account</strong> a été enregistré avec succès.");
                } else {
                    $this->addFlash('success', "Votre mot de passe a été enregistré avec succès.");
                }

                return $this->redirect($breadcrumbs->getPrevious());
            } else {
                $form->get('currentPassword')->addError(
                    new FormError("Le mot de passe que vous avez introduit n'est pas votre mot de passe actuel."));
            }
        }

        return $this->render('user/password.html.twig', [
            'form' => $form->createView(),
            'title' => ($account !== $currentUser) ? "Modifier le mot de passe de $account" : "Modifier votre mot de passe",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la suppression d'un utilisateur.
     * 
     * @Route("/user/{id}/delete", name="user_delete")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $account
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return void
     */
    public function delete(User $account, EntityManagerInterface $manager, Request $request, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'un utilisateur");

        if ($account === $this->getUser()) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer votre propre compte d'utilisateur.");
        } else if (!$this->isGranted($account->getMainRole())) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer l'utilisateur <strong>$account</strong> car son rôle est plus élevé que le vôtre.");
        } else if (count($account->getMeasures()) || count($account->getSystemReadings()) || count($account->getSystemValidations()) || count($account->getEncodedReadings()) || count($account->getValidatedReadings())) {
            $this->addFlash('danger', "Vous ne pouvez pas supprimer l'utilisateur <strong>$account</strong> car il est associé à des fiches, des relevés ou des mesures.");
        } else {
            $form = $this->createFormBuilder()->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->remove($account);
                $manager->flush();
        
                $this->addFlash('success', "L'utilisateur <strong>$account</strong> a été supprimé avec succès.");

                return $this->redirect($breadcrumbs->getPrevious('user'));
            } else {
                return $this->render('user/delete.html.twig', [
                    'form' => $form->createView(),
                    'user' => $account,
                    'title' => "Supprimer l'utilisateur $account",
                    'breadcrumbs' => $breadcrumbs,
                ]);
            }
        }
        return $this->redirect($breadcrumbs->getPrevious());
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
            'username' => $username,
        ]);
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
