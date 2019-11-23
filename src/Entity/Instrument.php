<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstrumentRepository")
 * @UniqueEntity(fields={"code"}, message="Un autre instrument possède déjà ce code. Veuillez en choisir un autre.")
 * @UniqueEntity(fields={"name"}, message="Un autre instrument possède déjà cette dénomination. Veuillez en choisir un autre.")
 */
class Instrument
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Code identifiant l'instrument.
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=2, minMessage="Le code doit faire au moins 2 caractères")
     */
    private $code;

    /**
     * Dénomination de l'instrument.
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, minMessage="La dénomination doit faire au moins 5 caractères")
     */
    private $name;

    /**
     * Marque et nom ou numéro du modèle de l'instrument.
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $model;

    /**
     * Numéro de série de l'instrument.
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serialNumber;

    /**
     * Description détaillée de l'instrument.
     * 
     * @ORM\Column(type="text")
     * @Assert\Length(min=50, minMessage="La description détaillée doit faire au moins 50 caractères")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measurability", mappedBy="instrument", orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $measurabilities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Calibration", mappedBy="instrument", orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $calibrations;

    public function __construct()
    {
        $this->measures = new ArrayCollection();
        $this->measurabilities = new ArrayCollection();
        $this->calibrations = new ArrayCollection();
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

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

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
            $measurability->setInstrument($this);
        }

        return $this;
    }

    public function removeMeasurability(Measurability $measurability): self
    {
        if ($this->measurabilities->contains($measurability)) {
            $this->measurabilities->removeElement($measurability);
            // set the owning side to null (unless already changed)
            if ($measurability->getInstrument() === $this) {
                $measurability->setInstrument(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Calibration[]
     */
    public function getCalibrations(): Collection
    {
        return $this->calibrations;
    }

    public function addCalibration(Calibration $calibration): self
    {
        if (!$this->calibrations->contains($calibration)) {
            $this->calibrations[] = $calibration;
            $calibration->setInstrument($this);
        }

        return $this;
    }

    public function removeCalibration(Calibration $calibration): self
    {
        if ($this->calibrations->contains($calibration)) {
            $this->calibrations->removeElement($calibration);
            // set the owning side to null (unless already changed)
            if ($calibration->getInstrument() === $this) {
                $calibration->setInstrument(null);
            }
        }

        return $this;
    }

    /**
     * Détermine la date limite de validité de l'instrument, d'après son
     * étalonnage le plus récent.
     *
     * @return DateTime|null
     */
    public function getCalibrationDueDate(): ?DateTime
    {
        $calibrationDueDate = null;
        foreach ($this->calibrations as $calibration) {
            $dueDate = $calibration->getDueDate();
            if ($dueDate > $calibrationDueDate) {
                $calibrationDueDate = $dueDate;
            }
        }
        return $calibrationDueDate;
    }
}
