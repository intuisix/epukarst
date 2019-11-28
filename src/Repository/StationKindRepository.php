<?php

namespace App\Repository;

use App\Entity\StationKind;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method StationKind|null find($id, $lockMode = null, $lockVersion = null)
 * @method StationKind|null findOneBy(array $criteria, array $orderBy = null)
 * @method StationKind[]    findAll()
 * @method StationKind[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StationKindRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StationKind::class);
    }

    // /**
    //  * @return StationKind[] Returns an array of StationKind objects
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
    public function findOneBySomeField($value): ?StationKind
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
