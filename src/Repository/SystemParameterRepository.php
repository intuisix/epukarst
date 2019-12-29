<?php

namespace App\Repository;

use App\Entity\System;
use App\Entity\SystemParameter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    /**
     * Trouve les paramètres d'un système donné et les trie par ordre de
     * position.
     *
     * @param System $system
     * @return SystemParameter[]
     */
    public function findSystemParameters(System $system)
    {
        return $this
            ->createQueryBuilder('sp')
            ->addSelect('ip')
            ->addSelect('p')
            ->innerJoin('sp.instrumentParameter', 'ip')
            ->innerJoin('ip.parameter', 'p')
            ->where('sp.system = :system')
            ->setParameter('system', $system)
            ->orderBy('p.position', 'ASC')
            ->getQuery()->getResult();
    }
}
