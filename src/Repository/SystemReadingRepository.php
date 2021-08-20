<?php

namespace App\Repository;

use App\Entity\SystemReading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SystemReading|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemReading|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemReading[]    findAll()
 * @method SystemReading[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemReadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemReading::class);
    }

    // /**
    //  * @return SystemReading[] Returns an array of SystemReading objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SystemReading
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
