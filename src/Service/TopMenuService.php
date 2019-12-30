<?php

namespace App\Service;

use App\Repository\PostRepository;
use App\Repository\SystemRepository;

/**
 * Service mettant à disposition du template de base du site, la liste des
 * articles et des systèmes à placer sur la barre de navigation.
 */
class TopMenuService
{
    private $postRepository;
    private $systemRepository;

    /**
     * Construit une instance.
     *
     * @param PostRepository $postRepository
     * @param SystemRepository $systemRepository
     */
    public function __construct(PostRepository $postRepository, SystemRepository $systemRepository)
    {
        $this->postRepository = $postRepository;
        $this->systemRepository = $systemRepository;
    }

    /**
     * Fournit les articles à afficher sur la barre de menus.
     *
     * @return Post[]
     */
    public function getPosts()
    {
        return $this->postRepository->findTopMenuPosts();
    }

    /**
     * Fournit les systèmes à afficher sur la barre de menus.
     *
     * @return System[]
     */
    public function getSystems()
    {
        return $this->systemRepository->findAllOrdered();
    }
}
