<?php

namespace App\Repository;

use App\Entity\Parameter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Parameter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parameter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parameter[]    findAll()
 * @method Parameter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParameterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parameter::class);
    }

    /**
     * Retourne un tableau contenant tous les paramètres ordonnés par position.
     *
     * @return Parameter[]
     */
    public function findAllOrdered()
    {
        return $this
            ->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne un tableau contenant tous les paramètres favoris ordonnés par position.
     *
     * @param boolean $favorite
     * @return void
     */
    public function findFavorites(bool $favorite = true)
    {
        return $this
            ->createQueryBuilder('p')
            ->andWhere('p.favorite = :fav')
            ->setParameter('fav', $favorite)
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
