<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AlarmRepository")
 */
class Alarm
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\System", inversedBy="alarms")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\NotBlank(message="Veuillez préciser le système.")
     */
    private $system;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AlarmKind", inversedBy="alarms")
     */
    private $kind;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @Assert\LessThanOrEqual("today", message="La date de début doit être antérieure à la date d'aujourd'hui.")
     */
    private $beginningDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @Assert\GreaterThan(propertyPath="beginningDate", message="La date de fin doit être postérieure à la date de début.")
     */
    private $endingDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reportingAuthor;

    /**
     * @ORM\Column(type="datetime")
     */
    private $reportingDate;

    /**
     * @ORM\Column(type="text")
     * 
     * @Assert\NotBlank(message="Veuillez commenter l'alarme.")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measure", mappedBy="alarm", orphanRemoval=true)
     * 
     * @Assert\Valid()
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

    public function getSystem(): ?System
    {
        return $this->system;
    }

    public function setSystem(?System $system): self
    {
        $this->system = $system;

        return $this;
    }

    public function getKind(): ?AlarmKind
    {
        return $this->kind;
    }

    public function setKind(?AlarmKind $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getBeginningDate(): ?\DateTimeInterface
    {
        return $this->beginningDate;
    }

    public function setBeginningDate(?\DateTimeInterface $beginningDate): self
    {
        $this->beginningDate = $beginningDate;

        return $this;
    }

    public function getEndingDate(): ?\DateTimeInterface
    {
        return $this->endingDate;
    }

    public function setEndingDate(?\DateTimeInterface $endingDate): self
    {
        $this->endingDate = $endingDate;

        return $this;
    }

    public function getReportingAuthor(): ?User
    {
        return $this->reportingAuthor;
    }

    public function setReportingAuthor(?User $reportingAuthor): self
    {
        $this->reportingAuthor = $reportingAuthor;

        return $this;
    }

    public function getReportingDate(): ?\DateTimeInterface
    {
        return $this->reportingDate;
    }

    public function setReportingDate(\DateTimeInterface $reportingDate): self
    {
        $this->reportingDate = $reportingDate;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

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
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
        }

        return $this;
    }
}
