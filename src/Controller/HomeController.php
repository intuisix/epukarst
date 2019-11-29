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
    public function home(PostRepository $repository)
    {
        $queryBuilder = $repository->createQueryBuilder('p');
        $queryBuilder
            ->select('p')
            ->where('p.home = TRUE')
            ->orderBy('p.orderNumber', 'ASC')
            ->addOrderBy('p.date', 'DESC');

        return $this->render('home.html.twig', [
            'posts' => $queryBuilder->getQuery()->getResult(),
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
            ->orderBy('p.orderNumber', 'ASC')
            ->addOrderBy('p.date', 'DESC')
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
