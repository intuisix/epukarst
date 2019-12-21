<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\PaginationService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil.
     * 
     * @Route("/", name="home")
     */
    public function home(PostRepository $postRepository)
    {
        return $this->render('home/home.html.twig', [
            'posts' => $postRepository->findHomePosts(),
        ]);
    }

    /**
     * @Route("/home/{slug}/{page<\d+>?1}", name="home_post")
     */
    public function show(Post $post, int $page, PostRepository $repository, PaginationService $pagination)
    {
        $queryBuilder = $repository->createQueryBuilder('p');
        $queryBuilder
            ->select('p')
            ->where('p.parent = :menu')
            ->andWhere('p.publishFromDate IS NOT NULL AND p.publishFromDate <= :today')
            ->andWhere('p.publishToDate IS NULL OR p.publishToDate >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.date', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->setParameter('menu', $post->getId());

        $pagination
            ->setQueryBuilder($queryBuilder)
            ->setPage($page);

        return $this->render('home/post.html.twig', [
            'pagination' => $pagination,
            'post' => $post,
        ]);
    }
}
