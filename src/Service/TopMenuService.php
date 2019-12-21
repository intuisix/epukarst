<?php

namespace App\Service;

use App\Repository\PostRepository;

/**
 * Service mettant à disposition du template de base du site, la liste des
 * articles à placer sur la barre de navigation.
 */
class TopMenuService
{
    private $repository;

    public function __construct(PostRepository $postRepository)
    {
        $this->repository = $postRepository;
    }

    public function getPosts()
    {
        /* Sélectionner les articles à paraître sur la barre de navigation */
        return $this->repository->findTopMenuPosts();
    }
}
