<?php

namespace App\Repository;

use App\Entity\ParameterChoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ParameterChoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParameterChoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParameterChoice[]    findAll()
 * @method ParameterChoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParameterChoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParameterChoice::class);
    }

    // /**
    //  * @return ParameterChoice[] Returns an array of ParameterChoice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ParameterChoice
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
