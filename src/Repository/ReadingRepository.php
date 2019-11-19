<?php

namespace App\Repository;

use App\Entity\Filter;
use App\Entity\Reading;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Reading|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reading|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reading[]    findAll()
 * @method Reading[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reading::class);
    }

    /**
     * Retourne un constructeur de requêtes, permettant de rechercher tous les
     * relevés correspondant aux données du filtre spécifié. 
     *
     * @param Filter $filter
     * @return QueryBuilder
     */
    public function getQueryBuilder(Filter $filter) : QueryBuilder
    {
        $stations = $filter->getStations();
        $minimumDateTime = $filter->getMinimumDate();
        $maximumDateTime = $filter->getMaximumDate();

        $queryBuilder = $this->createQueryBuilder('r')
            ->addSelect('r')
            ->orderBy('r.fieldDateTime', 'DESC')
            ->setParameter('stations', $filter->getStations());

        if ($stations !== null) {
            $queryBuilder
                ->where('r.station IN (:stations)');
        }

        if ($minimumDateTime !== null) {
            $queryBuilder
                ->andWhere('r.fieldDateTime >= :minimumDateTime')
                ->setParameter('minimumDateTime', $minimumDateTime);
        }

        if ($maximumDateTime !== null) {
            $queryBuilder
                ->andWhere('r.fieldDateTime <= :maximumDateTime')
                ->setParameter('maximumDateTime', $maximumDateTime);
        }

        return $queryBuilder;
    }
}
