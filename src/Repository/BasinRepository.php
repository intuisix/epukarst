<?php

namespace App\Repository;

use App\Entity\Basin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Basin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Basin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Basin[]    findAll()
 * @method Basin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BasinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Basin::class);
    }

    // /**
    //  * @return Basin[] Returns an array of Basin objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Basin
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
