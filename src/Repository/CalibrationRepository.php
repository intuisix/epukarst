<?php

namespace App\Repository;

use App\Entity\Calibration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Calibration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calibration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calibration[]    findAll()
 * @method Calibration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalibrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calibration::class);
    }

    // /**
    //  * @return Calibration[] Returns an array of Calibration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Calibration
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
