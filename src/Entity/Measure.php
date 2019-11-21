<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasureRepository")
 */
class Measure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Reading", inversedBy="measures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reading;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Measurability", inversedBy="measures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $measurability;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tolerance;

    /**
     * @ORM\Column(type="boolean")
     */
    private $stable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $valid;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="measures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $encodingAuthor;

    /**
     * @ORM\Column(type="datetime")
     */
    private $encodingDateTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReading(): ?Reading
    {
        return $this->reading;
    }

    public function setReading(?Reading $reading): self
    {
        $this->reading = $reading;

        return $this;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeInterface $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

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

    public function getStable(): ?bool
    {
        return $this->stable;
    }

    public function setStable(bool $stable): self
    {
        $this->stable = $stable;

        return $this;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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

    public function getMeasurability(): ?Measurability
    {
        return $this->measurability;
    }

    public function setMeasurability(?Measurability $measurability): self
    {
        $this->measurability = $measurability;

        return $this;
    }

    public function getParameter()
    {
        return $this->measurability->getParameter();
    }
}
