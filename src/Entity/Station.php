<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
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
     * @Assert\Regex("/^[A-Za-z0-9\-]+$/", message="Le code ne peut contenir que des lettres, des chiffres et des tirets")
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * Type de la station.
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
     * 
     * @Assert\NotBlank(message="Veuillez définir la description.")
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
            /* Trouver le numéro (suffixe de code) le plus élevé parmi toutes les stations, dans tous les bassins du système */
            $system = $this->basin->getSystem();
            $highestNumber = 0;
            foreach ($system->getBasins() as $basin) {
                foreach ($basin->getStations() as $station) {
                    $code = $station->getCode();
                    /* Rechercher le début du nombre situé en fin de chaîne */
                    $i = strlen($code);
                    while ($i > 0 && is_numeric($code[$i - 1])) {
                        $i--;
                    }
                    /* Déterminer si ce nombre est le plus élevé rencontré */
                    if (($i > 0) && ($code[$i - 1] == '-')) {
                        $number = (int)substr($code, $i);
                        if ($number > $highestNumber) {
                            $highestNumber = $number;
                        }
                    }
                }
            }
            /* Formater le code de station en utilisant celui du système comme préfixe (peu importe si d'autres préfixes ont pu être rencontrés durant l'examen des autres stations) */
            $this->code = $system->getCode() . '-' .
                sprintf("%02u", $highestNumber + 1);
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

    public function setCode(?string $code): self
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

    public function setDescription(?string $description): self
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
