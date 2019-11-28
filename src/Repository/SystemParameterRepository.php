<?php

namespace App\Repository;

use App\Entity\SystemParameter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SystemParameter|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemParameter|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemParameter[]    findAll()
 * @method SystemParameter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemParameterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemParameter::class);
    }

    // /**
    //  * @return SystemParameter[] Returns an array of SystemParameter objects
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
    public function findOneBySomeField($value): ?SystemParameter
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
