<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SystemRepository")
 * @ORM\HasLifecycleCallbacks()
 * 
 * @UniqueEntity(fields={"name"}, message="Un autre système possède déjà ce nom. Veuillez en choisir un autre.")
 * @UniqueEntity(fields={"code"}, message="Un autre système possède déjà ce code. Veuillez en choisir un autre.")
 */
class System
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Code identifiant le système.
     * 
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir le code.")
     * @Assert\Regex("/^[A-Za-z0-9\-]+$/", message="Le code ne peut contenir que des lettres, des chiffres et des tirets.")
     */
    private $code;

    /**
     * Dénomination du système.
     * 
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir le nom.")
     */
    private $name;

    /**
     * Texte introductif du système.
     * 
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir le texte d'introduction.")
     */
    private $introduction;

    /**
     * Nom de la commune sur laquelle se situe le système.
     * 
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir la commune.")
     */
    private $commune;

    /**
     * Nom du bassin dans lequel le système se verse.
     * 
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir le bassin versant.")
     */
    private $basin;

    /**
     * Description détaillée du système.
     * 
     * @ORM\Column(type="text")
     * 
     * @Assert\NotBlank(message="Veuillez définir la description.")
     */
    private $description;

    /**
     * Slogan utilisé dans l'URL qui permet d'afficher les informations à propos du système.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * Photos présentant le système.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\SystemPicture", mappedBy="system", orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $pictures;

    /**
     * Code de masse d'eau.
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $waterMass;

    /**
     * Bassins constituant le système.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Basin", mappedBy="system", orphanRemoval=true)
     * @ORM\OrderBy({"name": "ASC"})
     * 
     * @Assert\Valid()
     */
    private $basins;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemParameter", mappedBy="system", orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $parameters;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemReading", mappedBy="system", orphanRemoval=true)
     */
    private $systemReadings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemRole", mappedBy="system", orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $systemRoles;

    /**
     * Construit un nouveau système.
     */
    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->basins = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->parameters = new ArrayCollection();
        $this->systemReadings = new ArrayCollection();
        $this->systemRoles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Met à jour les propriétés du système avant qu'il ne soit mémorisé dans
     * la base de données.
     * 
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function update() {
        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->Slugify($this->name);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(string $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    public function getBasin(): ?string
    {
        return $this->basin;
    }

    public function setBasin(string $basin): self
    {
        $this->basin = $basin;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|SystemPicture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(SystemPicture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setSystem($this);
        }

        return $this;
    }

    public function removePicture(SystemPicture $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            // set the owning side to null (unless already changed)
            if ($picture->getSystem() === $this) {
                $picture->setSystem(null);
            }
        }

        return $this;
    }

    public function getWaterMass(): ?string
    {
        return $this->waterMass;
    }

    public function setWaterMass(?string $waterMass): self
    {
        $this->waterMass = $waterMass;

        return $this;
    }

    /**
     * @return Collection|Basin[]
     */
    public function getBasins(): Collection
    {
        return $this->basins;
    }

    public function addBasin(Basin $basin): self
    {
        if (!$this->basins->contains($basin)) {
            $this->basins[] = $basin;
            $basin->setSystem($this);
        }

        return $this;
    }

    public function removeBasin(Basin $basin): self
    {
        if ($this->basins->contains($basin)) {
            $this->basins->removeElement($basin);
            // set the owning side to null (unless already changed)
            if ($basin->getSystem() === $this) {
                $basin->setSystem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SystemParameter[]
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function addParameter(SystemParameter $parameter): self
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
            $parameter->setSystem($this);
        }

        return $this;
    }

    public function removeParameter(SystemParameter $parameter): self
    {
        if ($this->parameters->contains($parameter)) {
            $this->parameters->removeElement($parameter);
            // set the owning side to null (unless already changed)
            if ($parameter->getSystem() === $this) {
                $parameter->setSystem(null);
            }
        }

        return $this;
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
            $systemReading->setSystem($this);
        }

        return $this;
    }

    public function removeSystemReading(SystemReading $systemReading): self
    {
        if ($this->systemReadings->contains($systemReading)) {
            $this->systemReadings->removeElement($systemReading);
            // set the owning side to null (unless already changed)
            if ($systemReading->getSystem() === $this) {
                $systemReading->setSystem(null);
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
            $systemRole->setSystem($this);
        }

        return $this;
    }

    public function removeSystemRole(SystemRole $systemRole): self
    {
        if ($this->systemRoles->contains($systemRole)) {
            $this->systemRoles->removeElement($systemRole);
            // set the owning side to null (unless already changed)
            if ($systemRole->getSystem() === $this) {
                $systemRole->setSystem(null);
            }
        }

        return $this;
    }
}
