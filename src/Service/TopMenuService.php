<?php

namespace App\Service;

use App\Repository\PostRepository;

/**
 * Service mettant à disposition du template de base du site, la liste des
 * articles à placer sur la barre de navigation.
 */
class TopMenuService
{
    private $repository;

    public function __construct(PostRepository $postRepository)
    {
        $this->repository = $postRepository;
    }

    public function getPosts()
    {
        /* Sélectionner les articles à paraître sur la barre de navigation */
        $queryBuilder = $this->repository->createQueryBuilder('p');
        $queryBuilder
            ->select('p')
            ->where('p.topMenu = TRUE')
            ->andWhere('p.publishFromDate IS NOT NULL AND p.publishFromDate <= :today')
            ->andWhere('p.publishToDate IS NULL OR p.publishToDate >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('p.orderNumber', 'ASC')
            ->addOrderBy('p.date', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}