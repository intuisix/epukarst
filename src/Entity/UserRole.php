<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRoleRepository")
 */
class UserRole
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userRoles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $linkedUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Role", inversedBy="userRoles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $linkedRole;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\System", inversedBy="userRoles")
     */
    private $linkedSystem;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLinkedUser(): ?User
    {
        return $this->linkedUser;
    }

    public function setLinkedUser(?User $linkedUser): self
    {
        $this->linkedUser = $linkedUser;

        return $this;
    }

    public function getLinkedRole(): ?Role
    {
        return $this->linkedRole;
    }

    public function setLinkedRole(?Role $linkedRole): self
    {
        $this->linkedRole = $linkedRole;

        return $this;
    }

    public function getLinkedSystem(): ?System
    {
        return $this->linkedSystem;
    }

    public function setLinkedSystem(?System $linkedSystem): self
    {
        $this->linkedSystem = $linkedSystem;

        return $this;
    }
}
