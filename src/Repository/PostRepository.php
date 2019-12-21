<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Retourne un tableau contenant tous les articles ordonnés par position.
     *
     * @return Post[]
     */
    public function findAllOrdered()
    {
        return $this
            ->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.date', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * Trouve les articles destinés au menu supérieur, en tenant compte de
     * leurs dates de publication.
     *
     * @return Post[]
     */
    public function findTopMenuPosts()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.topMenu = TRUE')
            ->andWhere('p.publishFromDate IS NOT NULL AND p.publishFromDate <= :today')
            ->andWhere('p.publishToDate IS NULL OR p.publishToDate >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.date', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * Trouve les articles à afficher sur la page d'accueil, en tenant compte de
     * leurs dates de publication.
     *
     * @return Post[]
     */
    public function findHomePosts()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.home = TRUE')
            ->andWhere('p.publishFromDate IS NOT NULL AND p.publishFromDate <= :today')
            ->andWhere('p.publishToDate IS NULL OR p.publishToDate >= :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.date', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()->getResult();
    }
}
