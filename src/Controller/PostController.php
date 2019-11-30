<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
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
    public function index(int $page, PostRepository $repository, PaginationService $pagination)
    {
        $pagination
            ->setEntityClass(Post::class)
            ->setOrderBy(['orderNumber' => 'ASC', 'date' => 'DESC', 'id' => 'ASC'])
            ->setLimit(25)
            ->setPage($page);

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/post/create", name="post_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(ObjectManager $manager, Request $request)
    {
        /* Instancier un nouvel article */
        $post = new Post();
        /* Créer et traiter le formulaire */
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($post);
            $manager->flush();
            
            $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été créé avec succès.");
    
            return $this->redirectToRoute('post');
        }

        return $this->render('post/form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'title' => "Ajouter un article",
        ]);
    }

    /**
     * @Route("/post/{id}/modify", name="post_modify")
     * @IsGranted("ROLE_ADMIN")
     */
    public function modify(Post $post, ObjectManager $manager, Request $request)
    {
        /* Créer et traiter le formulaire */
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($post);
            $manager->flush();
            
            $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été modifié avec succès.");
    
            return $this->redirectToRoute('post');
        }

        return $this->render('post/form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'title' => "Modifier l'article {$post->getTitle()}",
        ]);
    }

    /**
     * Traite la demande de publication d'un article.
     *
     * @Route("/post/{id}/publish", name="post_publish")
     * @IsGranted("ROLE_ADMIN")
     */
    public function publish(Post $post, ObjectManager $manager)
    {
        $post->publish();
        $manager->flush();

        $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été publié avec succès.");

        return $this->redirectToRoute('post');
    }

    /**
     * Traite la demande de dépublication d'un article.
     *
     * @Route("/post/{id}/unpublish", name="post_unpublish")
     * @IsGranted("ROLE_ADMIN")
     */
    public function unpublish(Post $post, ObjectManager $manager)
    {
        $post->unpublish();
        $manager->flush();

        $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été dépublié avec succès.");

        return $this->redirectToRoute('post');
    }

    /**
     * Traite la demande de suppression d'un article.
     * 
     * @Route("/post/{id}/delete", name="post_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Post $post, ObjectManager $manager)
    {
        $manager->remove($post);
        $manager->flush();

        $this->addFlash('success', "L'article <strong>{$post->getTitle()}</strong> a été supprimé avec succès.");

        return $this->redirectToRoute('post');
    }
}
