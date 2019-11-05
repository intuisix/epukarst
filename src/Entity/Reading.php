<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReadingRepository")
 * @UniqueEntity(fields={"code"}, message="Un autre relevé possède déjà ce code. Veuillez définir un code unique.")
 * @HasLifecycleCallbacks()
 */
class Reading
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Station", inversedBy="readings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $station;

    /**
     * Code attribué au relevé.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * Date et heure des mesures sur le terrain.
     * 
     * @ORM\Column(type="datetime")
     */
    private $fieldDateTime;

    /**
     * Auteur de l'encodage.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="readings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $encodingAuthor;

    /**
     * Date et heure de l'encodage.
     * 
     * @ORM\Column(type="datetime")
     */
    private $encodingDateTime;

    /**
     * Remarques ajoutées lors de l'encodage.
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $encodingNotes;

    /**
     * Auteur de la validation.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="validatedReadings")
     */
    private $validationAuthor;

    /**
     * Date et heure de la validation.
     * 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validationDateTime;

    /**
     * Remarques ajoutées lors de la validation.
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationNotes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measure", mappedBy="reading", orphanRemoval=true)
     */
    private $measures;

    public function __construct()
    {
        $this->measures = new ArrayCollection();
    }

    /**
     * Met à jour les propriétés du relevé avant qu'il ne soit mémorisé dans la
     * base de données.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function update() {
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

    public function getEncodingDateTime(): ?\DateTimeInterface
    {
        return $this->encodingDateTime;
    }

    public function setEncodingDateTime(\DateTimeInterface $encodingDateTime): self
    {
        $this->encodingDateTime = $encodingDateTime;

        return $this;
    }

    public function getValidationDateTime(): ?\DateTimeInterface
    {
        return $this->validationDateTime;
    }

    public function setValidationDateTime(?\DateTimeInterface $validationDateTime): self
    {
        $this->validationDateTime = $validationDateTime;

        return $this;
    }

    public function getEncodingNotes(): ?string
    {
        return $this->encodingNotes;
    }

    public function setEncodingNotes(string $encodingNotes): self
    {
        $this->encodingNotes = $encodingNotes;

        return $this;
    }

    public function getValidationNotes(): ?string
    {
        return $this->validationNotes;
    }

    public function setValidationNotes(?string $validationNotes): self
    {
        $this->validationNotes = $validationNotes;

        return $this;
    }

    public function getEncodingAuthor(): ?User
    {
        return $this->encodingAuthor;
    }

    public function setEncodingAuthor(?User $encodingAuthor): self
    {
        $this->encodingAuthor = $encodingAuthor;

        return $this;
    }

    public function getValidationAuthor(): ?User
    {
        return $this->validationAuthor;
    }

    public function setValidationAuthor(?User $validationAuthor): self
    {
        $this->validationAuthor = $validationAuthor;

        return $this;
    }

    public function getStation(): ?Station
    {
        return $this->station;
    }

    public function setStation(?Station $station): self
    {
        $this->station = $station;

        return $this;
    }

    public function getFieldDateTime(): ?\DateTimeInterface
    {
        return $this->fieldDateTime;
    }

    public function setFieldDateTime(\DateTimeInterface $fieldDateTime): self
    {
        $this->fieldDateTime = $fieldDateTime;

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
            $measure->setReading($this);
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            // set the owning side to null (unless already changed)
            if ($measure->getReading() === $this) {
                $measure->setReading(null);
            }
        }

        return $this;
    }
}
