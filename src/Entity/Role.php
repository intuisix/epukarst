<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 */
class Role
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserRole", mappedBy="linkedRole")
     */
    private $userRoles;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canManageUsers;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canManageSystems;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canCoordinateSystem;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canContributeSystem;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canObserveSystem;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|UserRole[]
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(UserRole $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles[] = $userRole;
            $userRole->setLinkedRole($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
            // set the owning side to null (unless already changed)
            if ($userRole->getLinkedRole() === $this) {
                $userRole->setLinkedRole(null);
            }
        }

        return $this;
    }

    public function getCanManageUsers(): ?bool
    {
        return $this->canManageUsers;
    }

    public function setCanManageUsers(?bool $canManageUsers): self
    {
        $this->canManageUsers = $canManageUsers;

        return $this;
    }

    public function getCanManageSystems(): ?bool
    {
        return $this->canManageSystems;
    }

    public function setCanManageSystems(?bool $canManageSystems): self
    {
        $this->canManageSystems = $canManageSystems;

        return $this;
    }

    public function getCanCoordinateSystem(): ?bool
    {
        return $this->canCoordinateSystem;
    }

    public function setCanCoordinateSystem(?bool $canCoordinateSystem): self
    {
        $this->canCoordinateSystem = $canCoordinateSystem;

        return $this;
    }

    public function getCanContributeSystem(): ?bool
    {
        return $this->canContributeSystem;
    }

    public function setCanContributeSystem(?bool $canContributeSystem): self
    {
        $this->canContributeSystem = $canContributeSystem;

        return $this;
    }

    public function getCanObserveSystem(): ?bool
    {
        return $this->canObserveSystem;
    }

    public function setCanObserveSystem(?bool $canObserveSystem): self
    {
        $this->canObserveSystem = $canObserveSystem;

        return $this;
    }
}
