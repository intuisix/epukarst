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
        $measures = $filter->getMeasures();

        /* Créer le constructeur de requêtes. Utilisons des noms complets pour
        faciliter la compréhension. */
        $queryBuilder = $this->createQueryBuilder('reading')
            ->select('reading, station, basin, system, measure, measurability')
            ->leftJoin('reading.measures', 'measure')
            ->join('measure.measurability', 'measurability')
            ->join('reading.station', 'station')
            ->join('station.basin', 'basin')
            ->join('basin.system', 'system')
            ->orderBy('reading.fieldDateTime', 'DESC');

        if (null !== $stations) {
            $queryBuilder
                ->where('reading.station IN (:stations)')
                ->setParameter('stations', $filter->getStations());
        }

        if (null !== $minimumDateTime) {
            $queryBuilder
                ->andWhere('reading.fieldDateTime >= :minimumDateTime')
                ->setParameter('minimumDateTime', $minimumDateTime);
        }

        if (null !== $maximumDateTime) {
            $queryBuilder
                ->andWhere('reading.fieldDateTime <= :maximumDateTime')
                ->setParameter('maximumDateTime', $maximumDateTime);
        }

        if (null !== $measures) {
            foreach ($measures as $measure) {
                /* L'identifiant de paramètre est le pivot sur lequel on peut
                faire le rapprochement entre la mesure issue du filtre et les
                mesures issues des relevés */
                $parameterId = $measure->getParameter()->getId();

                /* Dans les appels à QueryBuilder ci-dessous, des valeurs sont
                insérées directement dans la requête avec des forçage de type
                pour seule protection contre l'injection DQL. Cela évite de
                devoir générer des noms de paramètres QueryBuilder, dans le cas
                où le filtre contient plusieurs mesures. */

                /* Ajouter la valeur minimum à la requête */
                $minimumValue = $measure->getMinimumValue();
                if (null !== $minimumValue) {
                    $queryBuilder
                        ->andWhere('measurability.parameter = ' . (int)$parameterId)
                        ->andWhere('measure.value >= ' . (float)$minimumValue);
                }

                /* Ajouter la valeur maximum à la requête */
                $maximumValue = $measure->getMaximumValue();
                if (null !== $maximumValue) {
                    $queryBuilder
                        ->andWhere('measurability.parameter = ' . (int)$parameterId)
                        ->andWhere('measure.value <= ' . (float)$maximumValue);
                }
            }
        }

        return $queryBuilder;
    }
}
