<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParameterRepository")
 * @UniqueEntity(fields={"name"}, message="Un autre paramètre possède déjà ce nom. Veuillez en choisir un autre.")
 * @UniqueEntity(fields={"title"}, message="Un autre paramètre possède déjà ce titre. Veuillez en choisir un autre.")
 */
class Parameter
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Nom du paramètre.
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=2, minMessage="Le nom doit faire au moins 2 caractères")
     */
    private $name;

    /**
     * Indique si le paramètre est favori ou non.
     * 
     * @ORM\Column(type="boolean")
     */
    private $favorite;

    /**
     * Valeur minimum issue des normes sanitaires.
     * 
     * @ORM\Column(type="float", nullable=true)
     */
    private $normativeMinimum;

    /**
     * Valeur maximum issue des normes sanitaires.
     * 
     * @ORM\Column(type="float", nullable=true)
     */
    private $normativeMaximum;

    /**
     * Valeur minimum mesurable physiquement.
     * 
     * @ORM\Column(type="float", nullable=true)
     */
    private $physicalMinimum;

    /**
     * Valeur maximum mesurable physiquement.
     * 
     * @ORM\Column(type="float", nullable=true)
     */
    private $physicalMaximum;

    /**
     * Unité de mesure et d'encodage.
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $unit;

    /**
     * Description du paramètre.
     * 
     * @ORM\Column(type="text")
     * @Assert\Length(min=20, minMessage="La description doit faire au moins 20 caractères")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $introduction;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measurability", mappedBy="parameter", orphanRemoval=true)
     */
    private $measurabilities;

    public function __construct()
    {
        $this->measures = new ArrayCollection();
        $this->measurabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFavorite(): ?bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): self
    {
        $this->favorite = $favorite;

        return $this;
    }

    public function getNormativeMinimum(): ?float
    {
        return $this->normativeMinimum;
    }

    public function setNormativeMinimum(?float $normativeMinimum): self
    {
        $this->normativeMinimum = $normativeMinimum;

        return $this;
    }

    public function getNormativeMaximum(): ?float
    {
        return $this->normativeMaximum;
    }

    public function setNormativeMaximum(?float $normativeMaximum): self
    {
        $this->normativeMaximum = $normativeMaximum;

        return $this;
    }

    public function getPhysicalMinimum(): ?float
    {
        return $this->physicalMinimum;
    }

    public function setPhysicalMinimum(?float $physicalMinimum): self
    {
        $this->physicalMinimum = $physicalMinimum;

        return $this;
    }

    public function getPhysicalMaximum(): ?float
    {
        return $this->physicalMaximum;
    }

    public function setPhysicalMaximum(?float $physicalMaximum): self
    {
        $this->physicalMaximum = $physicalMaximum;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Measurability[]
     */
    public function getMeasurabilities(): Collection
    {
        return $this->measurabilities;
    }

    public function addMeasurability(Measurability $measurability): self
    {
        if (!$this->measurabilities->contains($measurability)) {
            $this->measurabilities[] = $measurability;
            $measurability->setParameter($this);
        }

        return $this;
    }

    public function removeMeasurability(Measurability $measurability): self
    {
        if ($this->measurabilities->contains($measurability)) {
            $this->measurabilities->removeElement($measurability);
            // set the owning side to null (unless already changed)
            if ($measurability->getParameter() === $this) {
                $measurability->setParameter(null);
            }
        }

        return $this;
    }
}
