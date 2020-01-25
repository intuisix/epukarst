<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ControlRepository")
 */
class Control
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SystemReading", inversedBy="controls")
     */
    private $systemReading;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Measurability", inversedBy="controls")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instrumentParameter;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateTime;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSystemReading(): ?SystemReading
    {
        return $this->systemReading;
    }

    public function setSystemReading(?SystemReading $systemReading): self
    {
        $this->systemReading = $systemReading;

        return $this;
    }

    public function getInstrumentParameter(): ?Measurability
    {
        return $this->instrumentParameter;
    }

    public function setInstrumentParameter(?Measurability $instrumentParameter): self
    {
        $this->instrumentParameter = $instrumentParameter;

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
}
