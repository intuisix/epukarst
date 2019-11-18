<?php

namespace App\Service;

use Twig\Environment;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationService {
    private $entityClass;
    private $criteria = [];
    private $limit = 10;
    private $currentPage = 1;
    private $manager;
    private $route;
    private $templatePath;

    public function __construct(ObjectManager $manager, Environment $twig, RequestStack $requestStack, $templatePath) {
        $this->templatePath = $templatePath;
        $this->manager = $manager;
        $this->twig = $twig;
        $this->route = $requestStack->getCurrentRequest()->attributes->get('_route');
    }
    
    public function display() {
        $this->twig->display($this->templatePath, [
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route,
            ]);
    }
    
    public function getData() {
        if (empty($this->entityClass)) {
            throw new \Exception("Entité non spécifiée. Utilisez setEntityClass");
        }

        /* Calculer l'offset */
        $offset = ($this->currentPage - 1) * $this->limit;
        
        /* Demander au repository de trouver les éléments */
        $repo = $this->manager->getRepository($this->entityClass);
        $data = $repo->findBy($this->criteria, [], $this->limit, $offset);
        
        /* Renvoyer les éléments */
        return $data;
    }
    
    public function getPages() {
        if (empty($this->entityClass)) {
            throw new \Exception("Entité non spécifiée. Utilisez setEntityClass");
        }
        
        /* Total des enregistrements de la page */
        $repo = $this->manager->getRepository($this->entityClass);
        $total = count($repo->findBy($this->criteria));
        
        $pages = ceil($total / $this->limit);
        return $pages;
    }
    
    public function getEntityClass() {
        return $this->entityClass;
    }

    public function getCriteria() {
        return $this->criteria;
    }
    
    public function getLimit() {
        return $this->limit;
    }
    
    public function getPage() {
        return $this->currentPage;
    }
    
    public function getRoute() {
        return $this->route;
    }

    public function getTemplatePath() {
        return $this->templatePath;
    }
    
    public function setEntityClass($entityClass) {
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
    public function setCriteria(array $criteria) {
        $this->criteria = $criteria;
        return $this;
    }
    
    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }    

    public function setPage($page) {
        $this->currentPage = $page;
        return $this;
    }    

    public function setRoute($route) {
        $this->route = $route;
        return $this;
    }

    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
        return $this;
    }
}