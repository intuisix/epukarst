<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

/**
 * Données du formulaire de changement de mot de passe d'un utilisateur.
 */
class UserPassword
{
    /**
     * Mode de passe actuel.
     * 
     * @SecurityAssert\UserPassword(message="Ce mot de passe n'est pas votre mot de passe actuel")
     */
    private $currentPassword;

    /**
     * Mot de passe souhaité.
     * 
     * @Assert\Length(min="8", minMessage="Le mot de passe souhaité doit faire au moins 8 caractères")
     * @Assert\NotCompromisedPassword(message="D'après haveibeenpwned.com, le mot de passe souhaité est compromis")
     */
    private $wishedPassword;

    /**
     * Demande de révéler en clair le mot de passe dans un e-mail.
     */
    private $revealInEmail;

    function getCurrentPassword(): ?string {
        return $this->currentPassword;
    }

    function getWishedPassword(): ?string {
        return $this->wishedPassword;
    }

    function getRevealInEmail(): ?bool {
        return $this->revealInEmail;
    }

    function setCurrentPassword(string $currentPassword) {
        $this->currentPassword = $currentPassword;
        return $this;
    }

    function setWishedPassword(string $wishedPassword) {
        $this->wishedPassword = $wishedPassword;
        return $this;
    }

    function setRevealInEmail(bool $revealInEmail) {
        $this->revealInEmail = $revealInEmail;
        return $this;
    }
}
