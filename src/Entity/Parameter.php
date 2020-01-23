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
     * 
     * @Assert\NotBlank(message="Veuillez définir le nom.")
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
     * 
     * @Assert\NotBlank(message="Veuillez définir la description.")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir le text d'introduction.")
     */
    private $introduction;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir le titre.")
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measurability", mappedBy="parameter", orphanRemoval=true)
     */
    private $measurabilities;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ParameterChoice", mappedBy="parameter", orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $choices;

    public function __construct()
    {
        $this->measures = new ArrayCollection();
        $this->measurabilities = new ArrayCollection();
        $this->choices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameWithUnit(): ?string
    {
        return $this->name .
            (empty($this->unit) ? "" : " [$this->unit]");
    }

    public function setName(?string $name): self
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(?string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection|ParameterChoice[]
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(ParameterChoice $choice): self
    {
        if (!$this->choices->contains($choice)) {
            $this->choices[] = $choice;
            $choice->setParameter($this);
        }

        return $this;
    }

    public function removeChoice(ParameterChoice $choice): self
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
            // set the owning side to null (unless already changed)
            if ($choice->getParameter() === $this) {
                $choice->setParameter(null);
            }
        }

        return $this;
    }

    /**
     * Retourne un tableau contenant les choix à disposer dans une liste
     * déroulante.
     *
     * @return array|null
     */
    public function getChoicesArray()
    {
        if (count($this->choices)) {
            $choices = [];
            foreach ($this->choices as $choice) {
                $choices[$choice->getLabel()] = $choice->getValue();
            }
            return $choices;
        } else {
            return null;
        }
    }

    /**
     * Formate une valeur du paramètre, en utilisant de préférence les
     * étiquettes figurant dans la liste de choix de celui-ci.
     *
     * @param float $value
     * @return string
     */
    public function formatValue(float $value)
    {
        foreach ($this->choices as $choice) {
            $choiceValue = $choice->getValue();
            $epsilon = 0.00001;
            if (($value >= ($choiceValue - $epsilon) &&
                ($value <= ($choiceValue + $epsilon)))) {
                /* Retourner l'étiquette */
                return $choice->getLabel();
            }
        }
        return number_format($value, 1, ',', ' ');
    }
}
