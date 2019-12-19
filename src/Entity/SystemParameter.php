<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Définit l'instrument avec lequel un paramètre sera mesuré sur un système.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\SystemParameterRepository")
 */
class SystemParameter
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\System", inversedBy="parameters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $system;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Measurability", inversedBy="systemParameters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instrumentParameter;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

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

    public function getInstrumentParameter(): ?Measurability
    {
        return $this->instrumentParameter;
    }

    public function setInstrumentParameter(?Measurability $instrumentParameter): self
    {
        $this->instrumentParameter = $instrumentParameter;

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

    public function getName(): ?string
    {
        return $this->instrumentParameter ?
            $this->instrumentParameter->getName() : null;
    }
}
