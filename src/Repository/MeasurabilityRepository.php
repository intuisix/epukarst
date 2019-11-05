<?php

namespace App\Repository;

use App\Entity\Measurability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Measurability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Measurability|null findOneBy(array $criteria, array $orderBy = null)
 * @method Measurability[]    findAll()
 * @method Measurability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeasurabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Measurability::class);
    }

    // /**
    //  * @return Measurability[] Returns an array of Measurability objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Measurability
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
