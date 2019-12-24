<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SystemRoleRepository")
 * 
 * @UniqueEntity(fields={"system", "userAccount"}, message="L'autorisation est déjà donnée pour le même système au même utilisateur. Veuillez condenser.")
 */
class SystemRole
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\System", inversedBy="systemRoles")
     * @ORM\JoinColumn(nullable=true)
     */
    private $system;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="systemRoles")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userAccount;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSystem(): ?System
    {
        return $this->system;
    }

    public function setSystem(?System $system): self
    {
        $this->system = $system;

        return $this;
    }

    public function getUserAccount(): ?User
    {
        return $this->userAccount;
    }

    public function setUserAccount(?User $userAccount): self
    {
        $this->userAccount = $userAccount;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }
}
