<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasurabilityRepository")
 */
class Measurability
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parameter", inversedBy="measurabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parameter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Instrument", inversedBy="measurabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instrument;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maximumValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minimumValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tolerance;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measure", mappedBy="measurability")
     */
    private $measures;

    public function __construct()
    {
        $this->measures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParameter(): ?Parameter
    {
        return $this->parameter;
    }

    public function setParameter(?Parameter $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(?Instrument $instrument): self
    {
        $this->instrument = $instrument;

        return $this;
    }

    public function getMaximumValue(): ?float
    {
        return $this->maximumValue;
    }

    public function setMaximumValue(float $maximumValue): self
    {
        $this->maximumValue = $maximumValue;

        return $this;
    }

    public function getMinimumValue(): ?float
    {
        return $this->minimumValue;
    }

    public function setMinimumValue(float $minimumValue): self
    {
        $this->minimumValue = $minimumValue;

        return $this;
    }

    public function getTolerance(): ?float
    {
        return $this->tolerance;
    }

    public function setTolerance(float $tolerance): self
    {
        $this->tolerance = $tolerance;

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
            $measure->setMeasurability($this);
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            // set the owning side to null (unless already changed)
            if ($measure->getMeasurability() === $this) {
                $measure->setMeasurability(null);
            }
        }

        return $this;
    }
}
