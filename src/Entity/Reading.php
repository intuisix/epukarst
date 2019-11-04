<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReadingRepository")
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
     * Code attribué au relevé.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * Date et heure de l'encodage.
     * 
     * @ORM\Column(type="datetime")
     */
    private $encodingDateTime;

    /**
     * Date et heure de la validation.
     * 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validationDateTime;

    /**
     * Remarques ajoutées lors de l'encodage.
     * 
     * @ORM\Column(type="text")
     */
    private $encodingNotes;

    /**
     * Remarques ajoutées lors de la validation.
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationNotes;

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
}
