<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StationRepository")
 * @ORM\HasLifecycleCallbacks()
 * 
 * @UniqueEntity(fields={"code"}, message="Une autre station possède déjà ce code. Veuillez définir un code unique.")
 * @UniqueEntity(fields={"name", "basin"}, message="Une autre station possède déjà ce nom dans le même bassin. Veuillez en choisir un autre.")
 */
class Station
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Basin", inversedBy="stations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $basin;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * Genre de la station.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\StationKind", inversedBy="stations")
     */
    private $kind;

    /**
     * Code identifiant le phénomène karstique dans l'Atlas du Karst Wallon,
     * (référence documentaire).
     * 
     * Etant donné que plusieurs stations peuvent faire partie d'un même
     * phénomène karstique, ce code peut ne pas être unique.
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $atlasCode;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reading", mappedBy="station")
     */
    private $readings;

    public function __construct()
    {
        $this->readings = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }
    
    /**
     * Met à jour les propriétés avant la mémorisation en base de données.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * 
     * @return void
     */
    public function update()
    {
        if (empty($this->code)) {
            $this->code = uniqid();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBasin(): ?Basin
    {
        return $this->basin;
    }

    public function setBasin(?Basin $basin): self
    {
        $this->basin = $basin;

        return $this;
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

    public function getKind(): ?StationKind
    {
        return $this->kind;
    }

    public function setKind(?StationKind $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getAtlasCode(): ?string
    {
        return $this->atlasCode;
    }

    public function setAtlasCode(?string $atlasCode): self
    {
        $this->atlasCode = $atlasCode;

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
     * @return Collection|Reading[]
     */
    public function getReadings(): Collection
    {
        return $this->readings;
    }

    public function addReading(Reading $reading): self
    {
        if (!$this->readings->contains($reading)) {
            $this->readings[] = $reading;
            $reading->setStation($this);
        }

        return $this;
    }

    public function removeReading(Reading $reading): self
    {
        if ($this->readings->contains($reading)) {
            $this->readings->removeElement($reading);
            // set the owning side to null (unless already changed)
            if ($reading->getStation() === $this) {
                $reading->setStation(null);
            }
        }

        return $this;
    }
}
