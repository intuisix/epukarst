<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Utilisateur.
 * 
 * Dans la base de données, la table a dû être renommée en raison de problèmes
 * rencontrés avec PostgreSQL, pour lequel 'user' est un mot réservé.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user_account")
 * @HasLifecycleCallbacks()
 */
class User implements UserInterface
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
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $organization;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $displayName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserRole", mappedBy="linkedUser", orphanRemoval=true)
     */
    private $userRoles;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reading", mappedBy="author")
     */
    private $encodedReadings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reading", mappedBy="auditor")
     */
    private $validatedReadings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measure", mappedBy="creationAuthor")
     */
    private $measures;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->readings = new ArrayCollection();
        $this->validatedReadings = new ArrayCollection();
        $this->measures = new ArrayCollection();
    }

    /**
     * Indique si l'utilisateur est administrateur.
     */
    public function isAdmin() : bool {
        foreach ($this->userRoles as $userRole) {
            if ($userRole->getLinkedRole()->getRole() === 'ROLE_ADMIN') {
                return true;
            }
        }
        return false;
    }

    /**
     * Met à jour les propriétés de l'utilisateur avant la mémorisation dans la base de données.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function update() {
        if (empty($this->displayName)) {
            $this->displayName = trim($this->firstName . " " . strtoupper($this->lastName));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(?string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Retourne le mot de passe encrypté de l'utilisateur.
     * 
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
            $userRole->setLinkedUser($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
            // set the owning side to null (unless already changed)
            if ($userRole->getLinkedUser() === $this) {
                $userRole->setLinkedUser(null);
            }
        }

        return $this;
    }

    /**
     * Pour UserInterface, retourne les rôles de l'utilisateur.
     */
    public function getRoles() {
        /* Tous les utilisateurs connectés ont un premier rôle */
        $roles[] = 'ROLE_USER';

        /* Les autres rôles sont attribués par la base de données */
        foreach ($this->userRoles as $role) {
            $roles[] = $role->getLinkedRole()->getRole();
        }

        return $roles;
    }

    /**
     * Pour UserInterface, retourne le sel de l'utilisateur.
     * 
     * @return string
     */
    public function getSalt() {
        /* Ce sel est vide car il est géré par BCrypt */
        return null;
    }

    /**
     * Pour UserInterface, retourne le nom de l'utilisateur.
     *
     * @return void
     */
    public function getUsername() {
        return $this->email;
    }

    /**
     * Efface les informations personnelles de l'utilisateur.
     *
     * @return void
     */
    public function eraseCredentials() {
    }

    public function getPicture(): ?string
    {
        if (empty($this->picture))
            return 'https://avatars.io/user';

        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Collection|Reading[]
     */
    public function getEncodedReadings(): Collection
    {
        return $this->readings;
    }

    public function addEncodedReading(Reading $reading): self
    {
        if (!$this->readings->contains($reading)) {
            $this->readings[] = $reading;
            $reading->setAuthor($this);
        }

        return $this;
    }

    public function removeEncodedReading(Reading $reading): self
    {
        if ($this->readings->contains($reading)) {
            $this->readings->removeElement($reading);
            // set the owning side to null (unless already changed)
            if ($reading->getAuthor() === $this) {
                $reading->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reading[]
     */
    public function getValidatedReadings(): Collection
    {
        return $this->validatedReadings;
    }

    public function addValidatedReading(Reading $validatedReading): self
    {
        if (!$this->validatedReadings->contains($validatedReading)) {
            $this->validatedReadings[] = $validatedReading;
            $validatedReading->setAuditor($this);
        }

        return $this;
    }

    public function removeValidatedReading(Reading $validatedReading): self
    {
        if ($this->validatedReadings->contains($validatedReading)) {
            $this->validatedReadings->removeElement($validatedReading);
            // set the owning side to null (unless already changed)
            if ($validatedReading->getAuditor() === $this) {
                $validatedReading->setAuditor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Measure[]
     */
    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function addMeasure(Measure $measure): self
    {
        if (!$this->measures->contains($measure)) {
            $this->measures[] = $measure;
            $measure->setCreationAuthor($this);
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            // set the owning side to null (unless already changed)
            if ($measure->getCreationAuthor() === $this) {
                $measure->setCreationAuthor(null);
            }
        }

        return $this;
    }
}
