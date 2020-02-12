<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Service\Breadcrumbs;
use App\Repository\PostRepository;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    /**
     * @Route("/post/{page<\d+>?1}", name="post")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(int $page, PostRepository $repository, PaginationService $pagination, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->reset("Liste des articles");

        $pagination
            ->setEntityClass(Post::class)
            ->setOrderBy(['position' => 'ASC', 'date' => 'DESC', 'id' => 'ASC'])
            ->setLimit(25)
            ->setPage($page);

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/post/create", name="post_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request, PostRepository $postRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Création d'un article");

        /* Créer un tableau des articles ordonnés */
        $orderedPosts = $postRepository->findAllOrdered();
        $positions = $this->getPositions($orderedPosts);

        /* Instancier un nouvel article */
        $post = new Post();

        /* Créer et traiter le formulaire */
        $form = $this->createForm(PostType::class, $post, [
            'positions' => $positions,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les positions des articles */
            $this->updatePositions($orderedPosts, $post);
            /* Persister l'article dans la base de données */
            $manager->persist($post);
            $manager->flush();
            
            $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été créé avec succès.");
    
            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('post/form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'title' => "Ajouter un article",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * @Route("/post/{id}/modify", name="post_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(Post $post, ObjectManager $manager, Request $request, PostRepository $postRepository, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Modification d'un article");

        /* Créer un tableau des articles ordonnés */
        $orderedPosts = $postRepository->findAllOrdered();
        $positions = $this->getPositions($orderedPosts);

        /* Créer et traiter le formulaire */
        $form = $this->createForm(PostType::class, $post, [
            'positions' => $positions,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Mettre à jour les positions des articles */
            $this->updatePositions($orderedPosts, $post);
            /* Persister les modifications dans la base de données */
            $manager->persist($post);
            $manager->flush();
            
            $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été modifié avec succès.");

            return $this->redirect($breadcrumbs->getPrevious());
        }

        return $this->render('post/form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'title' => "Modifier l'article {$post->getTitle()}",
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Traite la demande de publication d'un article.
     *
     * @Route("/post/{id}/publish", name="post_publish")
     * @IsGranted("ROLE_ADMIN")
     */
    public function publish(Post $post, ObjectManager $manager, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Publication d'un article");

        $post->publish();
        $manager->flush();

        $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été publié avec succès.");

        return $this->redirect($breadcrumbs->getPrevious());
    }

    /**
     * Traite la demande de dépublication d'un article.
     *
     * @Route("/post/{id}/unpublish", name="post_unpublish")
     * @IsGranted("ROLE_ADMIN")
     */
    public function unpublish(Post $post, ObjectManager $manager, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Dépublication d'un article");

        $post->unpublish();
        $manager->flush();

        $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été dépublié avec succès.");

        return $this->redirect($breadcrumbs->getPrevious());
    }

    /**
     * Traite la demande de suppression d'un article.
     * 
     * @Route("/post/{id}/delete", name="post_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Post $post, ObjectManager $manager, Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->add("Suppression d'un article");

        $manager->remove($post);
        $manager->flush();

        $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été supprimé avec succès.");

        return $this->redirect($breadcrumbs->getPrevious('post'));
    }

    /**
     * Crée un tableau listant les positions des articles ordonnés, en vue de
     * fournir les choix pour la sélection de position. 
     * 
     * Réassigne aussi la position pour chaque paramètre, dans l'ordre
     * séquentiel (0, 1, 2, ...). Cela est utile lorsque les articles ont été
     * manipulés directement dans la base de données, avec une position nulle,
     * ou avec des trous ou des doublons.
     *
     * @param array $orderedPosts: tableau des articles, dans l'ordre souhaité.
     * @return array Tableau associatif avec, pour clé, le nom de l'article et,
     * pour valeur, la position de celui-ci, sous forme d'entier numérique.
     */
    private function getPositions(array $orderedPosts)
    {
        $positions = [];
        foreach ($orderedPosts as $orderedPosition => $orderedPost) {
            /* Réassigner la position du paramètre */
            $orderedPost->setPosition($orderedPosition);
            /* Ajouter le paramètre au tableau */
            $positions[$orderedPost->getTitle()] = $orderedPosition;
        }
        return $positions;
    }

    /**
     * Met à jour les positions des paramètres d'après la position demandée
     * pour le paramètre ajouté ou modifié.
     *
     * @param array $orderedPosts: tableau des articles, dans l'ordre initial.
     * @param Parameter $post: article ayant été ajouté ou modifié.
     * @return void
     */
    private function updatePositions(array $orderedPosts, Post $post)
    {
        /* Déplacer le paramètre dans le tableau ordonné */
        $initialPosition = array_search($post, $orderedPosts);
        $finalPosition = $post->getPosition() ?? count($orderedPosts);
        if ($initialPosition !== false) {
            array_splice($orderedPosts, $initialPosition, 1);
        }
        array_splice($orderedPosts, $finalPosition, 0, [$post]);

        /* Réassigner toutes les positions d'après le tableau ordonné; pour les
        articles existants, Doctrine détectera la modification éventuelle et
        transfèrera en base de données */
        foreach ($orderedPosts as $orderedPosition => $orderedPost) {
            $orderedPost->setPosition($orderedPosition);
        }
    }
}
