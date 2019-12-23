<?php

namespace App\Repository;

use App\Entity\SystemRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SystemRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemRole[]    findAll()
 * @method SystemRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemRole::class);
    }
}
