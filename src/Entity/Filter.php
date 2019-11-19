<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilterRepository")
 */
class Filter
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\System")
     */
    private $systems;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Basin")
     */
    private $basins;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Station")
     */
    private $stations;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $minimumDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $maximumDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FilterMeasure", mappedBy="filter", orphanRemoval=true)
     */
    private $measures;

    public function __construct()
    {
        $this->systems = new ArrayCollection();
        $this->basins = new ArrayCollection();
        $this->stations = new ArrayCollection();
        $this->measures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|System[]
     */
    public function getSystems(): Collection
    {
        return $this->systems;
    }

    public function addSystem(System $system): self
    {
        if (!$this->systems->contains($system)) {
            $this->systems[] = $system;
        }

        return $this;
    }

    public function removeSystem(System $system): self
    {
        if ($this->systems->contains($system)) {
            $this->systems->removeElement($system);
        }

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
        }

        return $this;
    }

    public function removeBasin(Basin $basin): self
    {
        if ($this->basins->contains($basin)) {
            $this->basins->removeElement($basin);
        }

        return $this;
    }

    /**
     * @return Collection|Station[]
     */
    public function getStations(): Collection
    {
        return $this->stations;
    }

    public function addStation(Station $station): self
    {
        if (!$this->stations->contains($station)) {
            $this->stations[] = $station;
        }

        return $this;
    }

    public function removeStation(Station $station): self
    {
        if ($this->stations->contains($station)) {
            $this->stations->removeElement($station);
        }

        return $this;
    }

    public function getMinimumDate(): ?\DateTimeInterface
    {
        return $this->minimumDate;
    }

    public function setMinimumDate(?\DateTimeInterface $minimumDate): self
    {
        $this->minimumDate = $minimumDate;

        return $this;
    }

    public function getMaximumDate(): ?\DateTimeInterface
    {
        return $this->maximumDate;
    }

    public function setMaximumDate(?\DateTimeInterface $maximumDate): self
    {
        $this->maximumDate = $maximumDate;

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

    /**
     * @return Collection|FilterMeasure[]
     */
    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function addMeasure(FilterMeasure $measure): self
    {
        if (!$this->measures->contains($measure)) {
            $this->measures[] = $measure;
            $measure->setFilter($this);
        }

        return $this;
    }

    public function removeMeasure(FilterMeasure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            // set the owning side to null (unless already changed)
            if ($measure->getFilter() === $this) {
                $measure->setFilter(null);
            }
        }

        return $this;
    }
}
