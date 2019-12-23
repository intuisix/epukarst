<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SystemRoleRepository")
 * 
 * @UniqueEntity(fields={"system", "author"}, message="L'autorisation est déjà donnée pour le même système au même utilisateur. Veuillez condenser.")
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
    private $author;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canView;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canEncode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canValidate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canExport;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $canDelete;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCanView(): ?bool
    {
        return $this->canView;
    }

    public function setCanView(?bool $canView): self
    {
        $this->canView = $canView;

        return $this;
    }

    public function getCanEncode(): ?bool
    {
        return $this->canEncode;
    }

    public function setCanEncode(?bool $canEncode): self
    {
        $this->canEncode = $canEncode;

        return $this;
    }

    public function getCanValidate(): ?bool
    {
        return $this->canValidate;
    }

    public function setCanValidate(?bool $canValidate): self
    {
        $this->canValidate = $canValidate;

        return $this;
    }

    public function getCanExport(): ?bool
    {
        return $this->canExport;
    }

    public function setCanExport(?bool $canExport): self
    {
        $this->canExport = $canExport;

        return $this;
    }

    public function getCanDelete(): ?bool
    {
        return $this->canDelete;
    }

    public function setCanDelete(?bool $canDelete): self
    {
        $this->canDelete = $canDelete;

        return $this;
    }
}
