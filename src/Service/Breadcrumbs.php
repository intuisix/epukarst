<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service gérant le fil d'ariane, qui suit les différents affichages et
 * formulaires parcourus par l'utilisateur, et permet à celui-ci de revenir en
 * arrière. Le fil d'ariane est mémorisé dans la session de l'utilisateur.
 */
class Breadcrumbs
{
    private $request;
    private $session;
    private $uri;
    private $stack;
    private $twig;
    private $templatePath;
    private $backTemplatePath;

    /**
     * Construit une instance du service.
     *
     * @param RequestStack $requestStack permet au service de connaître la page
     * en cours, à l'aide de l'URI figurant dans la requête, ainsi que
     * d'accéder à la session pour mémoriser et relire le fil d'ariane.
     * @param Environment $twig permet au service d'injecter des données dans
     * l'environnement Twig.
     * @param String $templatePath indique où se trouve le template Twig
     * permettant d'afficher le fil d'ariane complet.
     * @param String $backTemplatePath indique où se trouve le template Twig
     * permettant d'afficher un bouton de retour en arrière.
     */
    public function __construct(RequestStack $requestStack, Environment $twig, String $templatePath, String $backTemplatePath)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $this->request->getSession();
        $this->uri = $this->request->getRequestUri();
        $this->twig = $twig;
        $this->templatePath = $templatePath;
        $this->backTemplatePath = $backTemplatePath;
    }

    /**
     * Initialise le fil d'ariane sur la page en cours.
     *
     * @param String $title définit le titre de la page.
     * @return void
     */
    public function reset(String $title)
    {
        /* Recréer le fil d'ariane avec seulement la page en cours */
        $this->stack = array([
            'uri' => $this->uri,
            'title' => $title,
            'scope' => null,
        ]);

        /* Mémoriser le fil d'ariane dans la session */
        $this->session->set('breadcrumbs', $this->stack);
    }

    /**
     * Ajoute la page en cours au fil d'ariane, et gère les retours en arrière
     * sur des pages figurant déjà dans le fil d'ariane.
     * 
     * @param String $title définit le titre de la page.
     * @param $scope identifie un contexte indiquant que cette page est
     * susceptible de disparaître par une action de suppression demandée par
     * l'utilisateur.
     * Le cas d'utilisation typique où on définit ce paramètre est sur une page
     * de visualisation de ressource qui donne accès à un formulaire supprimant
     * la ressource: si la suppression est confirmée, le fil d'ariane ne devra
     * pas revenir à la page de visualisation, puisque celle-ci est devenue
     * indisponible, mais à la dernière page précédant l'arrivée dans le
     * contexte.
     * Le contexte peut prendre n'importe quel type, mais on utilisera
     * généralement une chaîne de caractères.
     * @return void
     */
    public function add(String $title, $scope = null)
    {
        /* Lire le fil d'ariane dans la session */
        if ($this->session->has('breadcrumbs')) {
            $this->stack = $this->session->get('breadcrumbs');
        } else {
            $this->stack = array();
        }

        /* Lorsqu'on repasse sur une page figurant déjà dans le fil d'ariane,
        considérer qu'il y a retour en arrière; ce mécanisme a également pour
        avantage de ne pas mémoriser de bouclages */
        foreach ($this->stack as $offset => $item) {
            if ($item['uri'] === $this->uri) {
                /* Tronquer le fil d'ariane */
                array_splice($this->stack, $offset);
            }
        }

        /* Ajouter la page à la fin du fil d'ariane */
        $this->stack[] = [
            'uri' => $this->uri,
            'title' => $title,
            'scope' => $scope,
        ];

        /* Mémoriser le fil d'ariane dans la session */
        $this->session->set('breadcrumbs', $this->stack);
    }

    public function removeLast()
    {
        /* Contrôler l'existence du fil d'ariane */
        if (null === $this->stack) {
            throw new \Exception("Breadcrumbs are not initialized.");
        } else {
            /* Supprimer la dernière page */
            array_splice($this->stack, count($this->stack) - 1);
        }

        /* Mémoriser le fil d'ariane dans la session */
        $this->session->set('breadcrumbs', $this->stack);
    }

    /**
     * Retourne l'URI de la page précédente.
     * 
     * @param $scope identifie le contexte afin de traiter le cas d'un
     * retour à une page précédente après qu'une ressource a été supprimée: le
     * fil d'ariane reviendra à la dernière page qui n'est pas dans le contexte.
     * @return String|null
     */
    public function getPrevious($scope = null)
    {
        /* Contrôler l'existence du fil d'ariane */
        if (null === $this->stack) {
            throw new \Exception("Breadcrumbs are not initialized.");
        } else {
            $count = count($this->stack);
    
            /* Pour pouvoir revenir en arrière, il faut qu'au moins deux pages
            soient présentes dans le fil d'ariane: la page en cours et la page
            précédente */
            if ($count < 2) {
                throw new \Exception("Breadcrumbs do not contain at least two pages.");
            } else {
                $offset = $count - 2;
    
                /* Traiter le cas de suppression de ressource */
                if (null !== $scope) {
                    /* Rechercher la page qui précède au contexte */
                    while (($offset > 0) && ($this->stack[$offset]['scope'] === $scope)) {
                        $offset--;
                    }
                }
    
                /* Retourner l'URI de la page sélectionnée */
                return $this->stack[$offset]['uri'];
            }
        }
    }

    public function display()
    {
        $this->twig->display($this->templatePath, [
            'pages' => $this->stack,
        ]);
    }

    /**
     * Affiche, dans le générateur Twig, un bouton permettant à l'utilisateur
     * de retourner à la page précédente.
     *
     * @return void
     */
    public function displayBackButton()
    {
        /* Afficher le template du bouton de retour */
        $this->twig->display($this->backTemplatePath, [
            'uri' => $this->getPrevious(),
        ]);
    }
}
