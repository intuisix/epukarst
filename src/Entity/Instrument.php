<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstrumentRepository")
 * @ORM\HasLifecycleCallbacks()
 * 
 * @UniqueEntity(fields={"code"}, message="Un autre instrument possède déjà ce code. Veuillez en choisir un autre.")
 * @UniqueEntity(fields={"name"}, message="Un autre instrument possède déjà cette dénomination. Veuillez en choisir une autre.")
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
     * 
     * @Assert\NotBlank(message="Veuillez définir le code.")
     */
    private $code;

    /**
     * Dénomination de l'instrument.
     * 
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Veuillez définir la dénomination.")
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
     * Ceci peut être également un numéro de lot, raison pour laquelle on ne
     * met pas de contrainte d'unicité.
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serialNumber;

    /**
     * Description détaillée de l'instrument.
     * 
     * @ORM\Column(type="text")
     * 
     * @Assert\NotBlank(message="Veuillez définir la description.")
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

    /**
     * Liste des instruments requis par cet instrument pour que les mesures
     * puissent effectivement être réalisées avec lui. Ces instruments
     * rassemblés récursivement forment la chaîne de mesure.
     * 
     * Par exemple:
     * - pour une sonde, un instrument lié peut être le multimètre permettant
     *   de calculer la mesure;
     * - pour un réactif, un instrument lié peut être le spectrophotomètre
     *   permettant de calculer la mesure, mais aussi la pipette permettant de
     *   mélanger un échantillon dans le réactif.
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Instrument", inversedBy="requiringInstruments")
     * 
     * @Assert\Valid()
     */
    private $requiredInstruments;

    /**
     * Rôle inverse de $requiredInstruments.
     * 
     * @ORM\ManyToMany(targetEntity="App\Entity\Instrument", mappedBy="requiredInstruments")
     * 
     * @Assert\Valid()
     */
    private $requiringInstruments;

    /**
     * Définit l'instrument servant de modèle.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\Instrument", inversedBy="derivedInstruments")
     * 
     * @Assert\Expression("this.getModelInstrument() !== this", message="L'instrument ne peut pas être son propre modèle")
     */
    private $modelInstrument;

    /**
     * Liste des instruments basés sur celui-ci comme modèle.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Instrument", mappedBy="modelInstrument")
     */
    private $derivedInstruments;

    /**
     * Construit un instrument.
     */
    public function __construct()
    {
        $this->measures = new ArrayCollection();
        $this->measurabilities = new ArrayCollection();
        $this->calibrations = new ArrayCollection();
        $this->requiredInstruments = new ArrayCollection();
        $this->requiringInstruments = new ArrayCollection();
        $this->derivedInstruments = new ArrayCollection();
    }

    /**
     * Nettoie un instrument avant qu'il ne soit mémorisé dans la base de
     * données.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function cleanupRequiredInstruments()
    {
        /* Eliminer les instruments liés redondants */
        $found = [];
        foreach ($this->requiredInstruments as $instrument) {
            if ($instrument === $this) {
                /* Enlever l'instrument lié à lui-même */
                $this->requiredInstruments->removeElement($instrument);
            } else if (in_array($instrument, $found, true)) {
                /* Enlever l'instrument lié présent plusieurs fois */
                $this->requiredInstruments->removeElement($instrument);
            } else {
                /* Conserver cet instrument */
                $instrument->findAllRequiredInstruments($found);
            }
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

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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

    public function setDescription(?string $description): self
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

    /**
     * Crée une copie de l'instrument.
     *
     * Contraintes exportées: Les propriétés uniques, qui sont copiées
     * également, devront être modifiées individuellement avant que la copie
     * d'instrument puisse être mémorisée.
     * 
     * @param bool $batch spécifie si le numéro de série et les étalonnages doivent
     * également être copiés; à défaut, ils seront vides sur la copie.
     * @return Instrument
     */
    public function duplicate(bool $batch = true): Instrument
    {
        $copy = new Instrument();

        /* Copier les propriétés générales */
        $copy->code = $this->code;
        $copy->name = $this->name;
        $copy->model = $this->model;
        $copy->description = $this->description;

        /* Copier les paramètres */
        foreach ($this->measurabilities as $original) {
            $measurability = $original->duplicate();
            $measurability->setInstrument($copy);
            $copy->measurabilities[] = $measurability;
        }

        if ($batch) {
            /* Copier le numéro de lot */
            $copy->serialNumber = $this->serialNumber;

            /* Copier les étalonnages */
            foreach ($this->calibrations as $original) {
                $calibration = $original->duplicate();
                $calibration->setInstrument($copy);
                $copy->calibrations[] = $calibration;
            }
        }

        return $copy;
    }

    /**
     * @return Collection|self[]
     */
    public function getRequiredInstruments(): Collection
    {
        return $this->requiredInstruments;
    }

    public function addRequiredInstrument(self $requiredInstrument): self
    {
        if (!$this->requiredInstruments->contains($requiredInstrument)) {
            $this->requiredInstruments[] = $requiredInstrument;
        }

        return $this;
    }

    public function removeRequiredInstrument(self $requiredInstrument): self
    {
        if ($this->requiredInstruments->contains($requiredInstrument)) {
            $this->requiredInstruments->removeElement($requiredInstrument);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getRequiringInstruments(): Collection
    {
        return $this->requiringInstruments;
    }

    public function addRequiringInstrument(self $requiringInstrument): self
    {
        if (!$this->requiringInstruments->contains($requiringInstrument)) {
            $this->requiringInstruments[] = $requiringInstrument;
            $requiringInstrument->addRequiredInstrument($this);
        }

        return $this;
    }

    public function removeRequiringInstrument(self $requiringInstrument): self
    {
        if ($this->requiringInstruments->contains($requiringInstrument)) {
            $this->requiringInstruments->removeElement($requiringInstrument);
            $requiringInstrument->removeRequiredInstrument($this);
        }

        return $this;
    }

    /**
     * Trouve récursivement tous les instruments liés.
     */
    public function findAllRequiredInstruments(&$found)
    {
        if (!in_array($this, $found)) {
            $found[] = $this;
            foreach ($this->requiredInstruments as $instrument) {
                $instrument->findAllRequiredInstruments($found);
            }
        }
    }

    public function getModelInstrument(): ?self
    {
        return $this->modelInstrument;
    }

    public function setModelInstrument(?self $modelInstrument): self
    {
        $this->modelInstrument = $modelInstrument;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getDerivedInstruments(): Collection
    {
        return $this->derivedInstruments;
    }

    public function addDerivedInstrument(self $derivedInstrument): self
    {
        if (!$this->derivedInstruments->contains($derivedInstrument)) {
            $this->derivedInstruments[] = $derivedInstrument;
            $derivedInstrument->setModelInstrument($this);
        }

        return $this;
    }

    public function removeDerivedInstrument(self $derivedInstrument): self
    {
        if ($this->derivedInstruments->contains($derivedInstrument)) {
            $this->derivedInstruments->removeElement($derivedInstrument);
            // set the owning side to null (unless already changed)
            if ($derivedInstrument->getModelInstrument() === $this) {
                $derivedInstrument->setModelInstrument(null);
            }
        }

        return $this;
    }
}
