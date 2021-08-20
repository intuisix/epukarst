<?php

namespace App\Repository;

use App\Entity\AlarmKind;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AlarmKind|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlarmKind|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlarmKind[]    findAll()
 * @method AlarmKind[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlarmKindRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlarmKind::class);
    }
}
