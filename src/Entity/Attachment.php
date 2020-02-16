<?php

namespace App\Entity;

use App\Entity\User;
use Behat\Transliterator\Transliterator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttachmentRepository")
 */
class Attachment
{
    const ATTACHMENTS_PATH = 'attachments';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploadDateTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SystemReading", inversedBy="attachments")
     */
    private $systemReading;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $uploadAuthor;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank()
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUploadDateTime(): ?\DateTimeInterface
    {
        return $this->uploadDateTime;
    }

    public function setUploadDateTime(\DateTimeInterface $uploadDateTime): self
    {
        $this->uploadDateTime = $uploadDateTime;

        return $this;
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

    public function getUploadAuthor(): ?User
    {
        return $this->uploadAuthor;
    }

    public function setUploadAuthor(?User $uploadAuthor): self
    {
        $this->uploadAuthor = $uploadAuthor;

        return $this;
    }

    /**
     * Stocke une pièce jointe qui vient d'être transférée.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function setUploadedFile(UploadedFile $file, User $user)
    {
        if ($file) {
            /* Extraire le nom de fichier sans chemin ni extension, le rendre sain, puis lui ajouter un identifiant unique et une extension adéquate */
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = Transliterator::transliterate($originalName);
            $newName = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

            /* Déplacer le fichier */
            $file->move(self::ATTACHMENTS_PATH, $newName);

            /* Surcharger le nom de fichier existant */
            $this->fileName = self::ATTACHMENTS_PATH . DIRECTORY_SEPARATOR . $newName;
            $this->name = $originalName;
            $this->mimeType = $file->getClientMimeType();
            $this->uploadAuthor = $user;
            $this->uploadDateTime = new \DateTimeImmutable();
        }

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
}
