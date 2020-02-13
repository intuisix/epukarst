<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CalibrationRepository")
 */
class Calibration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Instrument", inversedBy="calibrations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instrument;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="La date du contrôle n'est pas au bon format.")
     */
    private $doneDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date(message="La date du prochain contrôle n'est pas au bon format.")
     * @Assert\GreaterThanOrEqual(propertyPath="doneDate", message="La date du prochain contrôle doit être ultérieure à la date du contrôle actuel.")
     */
    private $dueDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $operatorName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDoneDate(): ?\DateTimeInterface
    {
        return $this->doneDate;
    }

    public function setDoneDate(\DateTimeInterface $doneDate): self
    {
        $this->doneDate = $doneDate;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getOperatorName(): ?string
    {
        return $this->operatorName;
    }

    public function setOperatorName(string $operatorName): self
    {
        $this->operatorName = $operatorName;

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

    /**
     * Duplique l'étalonnage.
     *
     * @return Calibration
     */    
    public function duplicate(): Calibration
    {
        $copy = new Calibration();

        $copy->instrument = $this->instrument;
        $copy->doneDate = $this->doneDate;
        $copy->dueDate = $this->dueDate;
        $copy->operatorName = $this->operatorName;
        $copy->notes = $this->notes;

        return $copy;
    }
}
