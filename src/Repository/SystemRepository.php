<?php

namespace App\Repository;

use App\Entity\System;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method System|null find($id, $lockMode = null, $lockVersion = null)
 * @method System|null findOneBy(array $criteria, array $orderBy = null)
 * @method System[]    findAll()
 * @method System[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, System::class);
    }

    /**
     * Trouve tous les systèmes, triés par nom.
     *
     * @return System[]
     */
    public function findAllOrdered()
    {
        return $this->findBy([], ['name' => 'ASC']);
    }
}
