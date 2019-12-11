<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SystemReadingRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class SystemReading
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\System", inversedBy="systemReadings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $system;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fieldDateTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="systemReadings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $encodingAuthor;

    /**
     * @ORM\Column(type="datetime")
     */
    private $encodingDateTime;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $encodingNotes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="systemValidations")
     */
    private $validationAuthor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validationDateTime;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationNotes;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $validationStatus;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Reading",
     *     mappedBy="systemReading",
     *     orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $stationReadings;

    /**
     * Construit une instance de relevé.
     */
    public function __construct()
    {
        $this->stationReadings = new ArrayCollection();
    }

    /**
     * Retourne le code du relevé.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }

    /**
     * Met à jour les propriétés du relevé de système avant la mémorisation en
     * base de données.
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

    public function getSystem(): ?System
    {
        return $this->system;
    }

    public function setSystem(?System $system): self
    {
        $this->system = $system;

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

    public function getEncodingAuthor(): ?User
    {
        return $this->encodingAuthor;
    }

    public function setEncodingAuthor(?User $encodingAuthor): self
    {
        $this->encodingAuthor = $encodingAuthor;

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

    public function getEncodingNotes(): ?string
    {
        return $this->encodingNotes;
    }

    public function setEncodingNotes(?string $encodingNotes): self
    {
        $this->encodingNotes = $encodingNotes;

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

    public function getValidationDateTime(): ?\DateTimeInterface
    {
        return $this->validationDateTime;
    }

    public function setValidationDateTime(?\DateTimeInterface $validationDateTime): self
    {
        $this->validationDateTime = $validationDateTime;

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

    public function getValidationStatus(): ?bool
    {
        return $this->validationStatus;
    }

    public function setValidationStatus(?bool $validationStatus): self
    {
        $this->validationStatus = $validationStatus;

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

    /**
     * @return Collection|Reading[]
     */
    public function getStationReadings(): Collection
    {
        return $this->stationReadings;
    }

    public function addStationReading(Reading $stationReading): self
    {
        if (!$this->stationReadings->contains($stationReading)) {
            $this->stationReadings[] = $stationReading;
            $stationReading->setSystemReading($this);
        }

        return $this;
    }

    public function removeStationReading(Reading $stationReading): self
    {
        if ($this->stationReadings->contains($stationReading)) {
            $this->stationReadings->removeElement($stationReading);
            // set the owning side to null (unless already changed)
            if ($stationReading->getSystemReading() === $this) {
                $stationReading->setSystemReading(null);
            }
        }

        return $this;
    }
}
