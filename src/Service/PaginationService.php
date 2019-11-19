<?php

namespace App\Service;

use Twig\Environment;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Gestionnaire de pagination permettant de présenter des tableaux de données
 * sur plusieurs pages pour faciliter l'utilisation et limiter les problèmes de
 * performances lorsque les données sont nombreuses.
 * 
 * L'objet est capable de retourner un ensemble de données correspondant à une
 * page active.
 * 
 * En utilisant Twig, l'objet est capable d'afficher le paginateur,
 * c'est-à-dire le contrôle dans lequel l'utilisateur sélectionne le numéro de
 * la page active. A cette fin, un template Twig à utiliser peut être spécifié
 * dans le fichier "services.yaml", par exemple:
 * 
 *     App\Service\PaginationService:
 *         $templatePath: 'pagination.html.twig'
 *
 * Le nom du template peut également être spécifié après la construction du
 * gestionnaire.
 * 
 * En vue de générer les liens vers les différentes pages, le gestionnaire
 * détermine la route active d'après la requête HTTP. Il passe transmet au
 * template le nom de cette route, le nombre total de pages et le numéro de la
 * page active.
 * 
 * Actuellement, le paginateur doit être invoqué après l'obtention des données.
 */
class PaginationService
{
    private $entityClass;
    private $queryBuilder;
    private $criteria = [];
    private $limit = 10;
    private $currentPage = 1;
    private $totalPages = 0;
    private $manager;
    private $route;
    private $templatePath;

    /**
     * Construit un gestionnaire de pagination.
     * 
     * @param ObjectManager $manager
     * @param Environment $twig
     * @param RequestStack $requestStack
     * @param string $templatePath
     */
    public function __construct(ObjectManager $manager, Environment $twig, RequestStack $requestStack, string $templatePath)
    {
        $this->templatePath = $templatePath;
        $this->manager = $manager;
        $this->twig = $twig;
        $this->route = $requestStack->getCurrentRequest()->attributes->get('_route');
    }
    
    /**
     * Retourne les éléments à afficher sur la page.
     *
     * @return array
     */
    public function getData() : array
    {
        /* Actualiser le nombre total de pages */
        $this->update();

        /* Calculer l'offset de la page active */
        $offset = ($this->currentPage - 1) * $this->limit;
        
        /* Obtenir les éléments de la page */
        if ($this->queryBuilder) {
            $query = $this->queryBuilder->getQuery()
                ->setFirstResult($offset)
                ->setMaxResults($this->limit);
            $data = $query->getResult();
        } else {
            $repo = $this->manager->getRepository($this->entityClass);
            $data = $repo->findBy($this->criteria, [], $this->limit, $offset);
        }
        
        /* Renvoyer les éléments */
        return $data;
    }
    
    /**
     * Affiche le paginateur à l'aide du template, sauf si le nombre de pages
     * est nul.
     *
     * @return void
     */
    public function display() : void
    {
        if ($this->totalPages > 0) {
            $this->twig->display($this->templatePath, [
                'page'  => $this->currentPage,
                'pages' => $this->totalPages,
                'route' => $this->route,
            ]);
        }
    }
    
    /**
     * Détermine le nombre de pages total et ramène le numéro de page en cours
     * dans la gamme possible.
     *
     * TODO: Voir s'il est possible d'optimiser le comptage, étant donné
     * que l'on n'utilise pas les éléments du résultat.
     *
     * @return void
     */
    private function update() : void
    {
        if (empty($this->entityClass) && empty($this->queryBuilder)) {
            throw new \Exception("Entité ou constructeur de requêtes non spécifiés : veuillez appeler setEntityClass ou setQueryBuilder.");
        }

        /* Déterminer le nombre d'éléments */
        if ($this->queryBuilder) {
            $total = count($this->queryBuilder->getQuery()->getResult());
        } else {
            $repo = $this->manager->getRepository($this->entityClass);
            $total = count($repo->findBy($this->criteria));
        }
        
        /* Déterminer le nombre de pages, y compris la dernière qui peut
        contenir moins d'éléments que les autres */
        $this->totalPages = ceil($total / $this->limit);

        /* Contraindre l'indice de la page en cours */
        $this->currentPage = max(1, min($this->currentPage, $this->totalPages));
    }
    
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }
    
    public function getLimit()
    {
        return $this->limit;
    }
    
    public function getPage()
    {
        return $this->currentPage;
    }
    
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getTemplatePath()
    {
        return $this->templatePath;
    }
    
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * Définit les critères simples de sélection, sous forme d'un tableau qui
     * sera transmis à la méthode findBy() de Doctrine au moment d'effectuer
     * la sélection.
     *
     * Exemples:
     *      setCriteria(['age' => 20]);
     *      setCriteria(['age' => 20, 'surname' => 'Miller'])
     *      setCriteria(['phone'] => $number->getId())
     *      setCriteria(['age' => 20, 30, 40])
     * 
     * @param array $criteria.
     * @return self
     */
    public function setCriteria(array $criteria)
    {
        $this->criteria = $criteria;
        return $this;
    }
    
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }    

    public function setPage($page)
    {
        $this->currentPage = $page;
        return $this;
    }    

    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
        return $this;
    }
}