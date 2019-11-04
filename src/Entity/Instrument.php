<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstrumentRepository")
 * @UniqueEntity(fields={"code"}, message="Un autre instrument possède déjà ce code. Veuillez en choisir un autre.")
 */
class Instrument
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Code identifiant l'instrument.
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=2, minMessage="Le code doit faire au moins 2 caractères")
     */
    private $code;

    /**
     * Dénomination de l'instrument.
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, minMessage="La dénomination doit faire au moins 5 caractères")
     */
    private $name;

    /**
     * Marque et nom ou numéro du modèle de l'instrument.
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, minMessage="Le nom du modèle doit faire au moins 5 caractères")
     */
    private $model;

    /**
     * Numéro de série de l'instrument.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $serialNumber;

    /**
     * Description détaillée de l'instrument.
     * 
     * @ORM\Column(type="text")
     * @Assert\Length(min=50, minMessage="La description détaillée doit faire au moins 50 caractères")
     */
    private $description;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

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
}
