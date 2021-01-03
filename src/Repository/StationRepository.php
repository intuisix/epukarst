<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\System;
use App\Entity\Station;
use App\Security\SystemVoter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Station|null find($id, $lockMode = null, $lockVersion = null)
 * @method Station|null findOneBy(array $criteria, array $orderBy = null)
 * @method Station[]    findAll()
 * @method Station[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Station::class);
    }

    /**
     * Trouve les stations appartenant à un système.
     *
     * @param System $system
     * @return Station[]
     */
    public function findBySystem(System $system)
    {
        return $this
            ->createQueryBuilder('s')
            ->innerJoin('s.basin', 'b')
            ->where('b.system = :system')
            ->setParameter('system', $system)
            ->orderBy('s.code')
            ->getQuery()->getResult();
    }

    /**
     * Crée un constructeur de requête énumérant les stations pour lequelles un
     * utilisateur donné possède l'un des rôles donnés.
     * 
     * @param User $user
     * @param string[] $roles
     * @return QueryBuilder
     */
    public function createQueryBuilderGranted(User $user, array $roles = [ SystemVoter::CONTRIBUTOR, SystemVoter::MANAGER ])
    {
        return $this->createQueryBuilder('station')
            ->setParameter('user', $user)
            ->setParameter('roles', $roles)
            ->addSelect('basin')
            ->addSelect('system')
            ->innerJoin('station.basin', 'basin')
            ->innerJoin('basin.system', 'system')
            /* Joindre les rôles propres au système, attribués à l'utilisateur
            et par défaut à tous les utilisateurs */
            ->leftJoin('system.systemRoles', 'sr', 'WITH',
                '((sr.userAccount IS NULL) OR (sr.userAccount = :user)) AND (sr.role IN (:roles))')
            /* Joindre les rôles propres à l'utilisateur, attribués par défaut
            pour tous les systèmes */
            ->leftJoin('App\Entity\SystemRole', 'ur', 'WITH',
                '(ur.system IS NULL) AND (ur.userAccount = :user) AND (ur.role IN (:roles))')
            ->andWhere('(sr IS NOT NULL) OR (ur IS NOT NULL)')
            /* Trier par nom de système */
            ->orderBy('system.name');
    }

    /**
     * Trouve les stations appartenant à un système donné.
     * 
     * @param System $system
     * @return Station[]
     */
    public function findSystemStations(System $system)
    {
        return $this
            ->createQueryBuilder('s')
            ->innerJoin('s.basin', 'b')
            ->where('b.system = :system')
            ->setParameter('system', $system)
            ->orderBy('s.code', 'ASC')
            ->getQuery()->getResult();
    }
}
