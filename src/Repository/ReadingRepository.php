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
        $validated = $filter->getValidated();
        $invalidated = $filter->getInvalidated();
        $submitted = $filter->getSubmitted();
        $measures = $filter->getMeasures();

        /* Créer le constructeur de requêtes. Utilisons des noms complets pour
        faciliter la compréhension des éléments de la requête. */
        $queryBuilder = $this->createQueryBuilder('reading')
            ->select('reading, station, basin, system, measure, measurability')
            ->leftJoin('reading.measures', 'measure')
            ->join('measure.measurability', 'measurability')
            ->join('reading.station', 'station')
            ->join('station.basin', 'basin')
            ->join('basin.system', 'system')
            ->orderBy('reading.fieldDateTime', 'DESC');

        /* Filtrer par stations */
        if (null !== $stations) {
            $queryBuilder
                ->where('reading.station IN (:stations)')
                ->setParameter('stations', $filter->getStations());
        }

        /* Filtrer par date minimum */
        if (null !== $minimumDateTime) {
            $queryBuilder
                ->andWhere('reading.fieldDateTime >= :minimumDateTime')
                ->setParameter('minimumDateTime', $minimumDateTime);
        }

        /* Filtrer par date maximum */
        if (null !== $maximumDateTime) {
            $queryBuilder
                ->andWhere('reading.fieldDateTime <= :maximumDateTime')
                ->setParameter('maximumDateTime', $maximumDateTime);
        }

        /* Filtrer par état */
        if (empty($validated)) {
            $queryBuilder->andWhere('((reading.validated = FALSE) OR (reading.validated IS NULL))');
        }
        if (empty($invalidated)) {
            $queryBuilder->andWhere('((reading.validated = TRUE) OR (reading.validated IS NULL))');
        }
        if (empty($submitted)) {
            $queryBuilder->andWhere('reading.validated IS NOT NULL');
        }

        /* Filtrer par mesures */
        if (null !== $measures) {
            $queryBuilder
                ->andWhere('reading.id IN (:ids)')
                ->setParameter('ids', $this->findIdsByMeasures($measures));
        }

        return $queryBuilder;
    }

    /**
     * Retourne les identifiants des relevés qui comportent parmi leurs mesures,
     * pour chaque paramètre de filtre, au moins une valeur mesurée valide
     * comprise entre les valeurs minimum et le maximum du filtre.
     *
     * @param FilterMeasure[] $measures
     * @return array
     */
    private function findIdsByMeasures($measures)
    {
        /* Créer un constructeur de requêtes. L'utilisation de noms longs est
        préféré afin de faciliter la compréhension de la requête. Le paramètre
        concerné par une mesure de relevé est accessible par le biais de la
        mesurabilité (ici, l'instrument utilisé n'a aucune importance). */
        $queryBuilder = $this->createQueryBuilder('reading')
            ->select('DISTINCT(reading.id)')
            ->join('reading.measures', 'measure')
            ->join('measure.measurability', 'measurability');
        
        /* Ne prendre en compte que les valeurs valides */
        $queryBuilder->where('measure.valid = true');

        /* Au moins une mesure correspondante est suffisante => OU logique */
        $orX = $queryBuilder->expr()->orX();
        foreach ($measures as $measure) {
            /* La mesure doit satisfaire à tous les critères => ET logique */
            $andX = $queryBuilder->expr()->andX();

            /* L'identifiant de paramètre est le pivot sur lequel on peut
            faire le rapprochement entre la mesure issue du filtre et les
            mesures issues des relevés */
            $parameterId = $measure->getParameter()->getId();
            $andX->add('measurability.parameter = ' . (int)$parameterId);

            /* Dans les appels à QueryBuilder ci-dessous, des valeurs sont
            insérées directement dans la requête avec des forçage de type
            pour seule protection contre l'injection DQL: dans le cas où le
            filtre contient plusieurs mesures, cela évite de devoir générer des
            noms de paramètres QueryBuilder */

            /* Ajouter la valeur minimum à la requête */
            $minimumValue = $measure->getMinimumValue();
            if (null !== $minimumValue) {
                $andX->add('measure.value >= ' . (float)$minimumValue);
            }

            /* Ajouter la valeur maximum à la requête */
            $maximumValue = $measure->getMaximumValue();
            if (null !== $maximumValue) {
                $andX->add('measure.value <= ' . (float)$maximumValue);
            }

            $orX->add($andX);
        }
        $queryBuilder->andWhere($orX);

        return $queryBuilder->getQuery()->getResult();
    }
}
