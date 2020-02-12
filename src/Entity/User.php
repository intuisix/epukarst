<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Utilisateur.
 * 
 * Dans la base de données, la table a dû être renommée en raison de problèmes
 * rencontrés avec PostgreSQL, pour lequel 'user' est un mot réservé.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user_account")
 * @ORM\HasLifecycleCallbacks()
 * 
 * @UniqueEntity(fields={"displayName"}, message="Un autre utilisateur possède déjà le même pseudonyme. Veuillez en choisir un autre.")
 * @UniqueEntity(fields={"email"}, message="Un autre utilisateur possède déjà la même adresse e-mail. Veuillez en choisir une autre.")
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
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reading", mappedBy="encodingAuthor")
     */
    private $encodedReadings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reading", mappedBy="validationAuthor")
     */
    private $validatedReadings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measure", mappedBy="encodingAuthor")
     */
    private $measures;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemReading", mappedBy="encodingAuthor")
     */
    private $systemReadings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemReading", mappedBy="validationAuthor")
     */
    private $systemValidations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemRole", mappedBy="userAccount", orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $systemRoles;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $mainRole;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->readings = new ArrayCollection();
        $this->validatedReadings = new ArrayCollection();
        $this->measures = new ArrayCollection();
        $this->systemReadings = new ArrayCollection();
        $this->systemValidations = new ArrayCollection();
        $this->systemRoles = new ArrayCollection();
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

    public function setLastName(?string $lastName): self
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
     * Pour UserInterface, retourne les rôles de l'utilisateur.
     */
    public function getRoles()
    {
        /* Retourner uniquement le rôle principal de l'utilisateur */
        $roles[] = $this->mainRole;
        return $roles;
    }

    /**
     * Pour UserInterface, retourne le sel de l'utilisateur.
     * 
     * @return string
     */
    public function getSalt()
    {
        /* Ce sel est vide car il est géré par BCrypt */
        return null;
    }

    /**
     * Pour UserInterface, retourne le nom de l'utilisateur.
     *
     * @return void
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Efface les informations personnelles de l'utilisateur.
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }

    public function getPicture(): ?string
    {
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
        return $this->encodedReadings;
    }

    public function addEncodedReading(Reading $reading): self
    {
        if (!$this->encodedReadings->contains($reading)) {
            $this->encodedReadings[] = $reading;
            $reading->setAuthor($this);
        }

        return $this;
    }

    public function removeEncodedReading(Reading $reading): self
    {
        if ($this->encodedReadings->contains($reading)) {
            $this->encodedReadings->removeElement($reading);
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

    public function __toString() : string
    {
        return $this->displayName;
    }

    /**
     * @return Collection|SystemReading[]
     */
    public function getSystemReadings(): Collection
    {
        return $this->systemReadings;
    }

    public function addSystemReading(SystemReading $systemReading): self
    {
        if (!$this->systemReadings->contains($systemReading)) {
            $this->systemReadings[] = $systemReading;
            $systemReading->setEncodingAuthor($this);
        }

        return $this;
    }

    public function removeSystemReading(SystemReading $systemReading): self
    {
        if ($this->systemReadings->contains($systemReading)) {
            $this->systemReadings->removeElement($systemReading);
            // set the owning side to null (unless already changed)
            if ($systemReading->getEncodingAuthor() === $this) {
                $systemReading->setEncodingAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SystemReading[]
     */
    public function getSystemValidations(): Collection
    {
        return $this->systemValidations;
    }

    public function addSystemValidation(SystemReading $systemValidation): self
    {
        if (!$this->systemValidations->contains($systemValidation)) {
            $this->systemValidations[] = $systemValidation;
            $systemValidation->setValidationAuthor($this);
        }

        return $this;
    }

    public function removeSystemValidation(SystemReading $systemValidation): self
    {
        if ($this->systemValidations->contains($systemValidation)) {
            $this->systemValidations->removeElement($systemValidation);
            // set the owning side to null (unless already changed)
            if ($systemValidation->getValidationAuthor() === $this) {
                $systemValidation->setValidationAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SystemRole[]
     */
    public function getSystemRoles(): Collection
    {
        return $this->systemRoles;
    }

    public function addSystemRole(SystemRole $systemRole): self
    {
        if (!$this->systemRoles->contains($systemRole)) {
            $this->systemRoles[] = $systemRole;
            $systemRole->setAuthor($this);
        }

        return $this;
    }

    public function removeSystemRole(SystemRole $systemRole): self
    {
        if ($this->systemRoles->contains($systemRole)) {
            $this->systemRoles->removeElement($systemRole);
            // set the owning side to null (unless already changed)
            if ($systemRole->getAuthor() === $this) {
                $systemRole->setAuthor(null);
            }
        }

        return $this;
    }

    public function getMainRole(): ?string
    {
        return $this->mainRole;
    }

    public function setMainRole(?string $mainRole): self
    {
        $this->mainRole = $mainRole;

        return $this;
    }
}
