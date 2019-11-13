<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Behat\Transliterator\Transliterator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PictureRepository")
 */
class SystemPicture
{
    const PICTURES_PATH = 'images/systems';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\System", inversedBy="pictures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $system;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $caption;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploadDateTime;

    public function setUploadedFile(UploadedFile $file)
    {
        if ($file) {
            /* Extraire le nom de fichier sans chemin ni extension, le rendre sain, puis lui ajouter un identifiant unique et une extension adéquate */
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = Transliterator::transliterate($originalName);
            $newName = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

            /* Déplacer le fichier */
            $file->move(self::PICTURES_PATH, $newName);

            /* Surcharger le nom de fichier existant */
            $this->fileName = '/' . self::PICTURES_PATH . '/' . $newName;
            $this->uploadDateTime = new \DateTimeImmutable();
        }

        return $this;
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

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
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

    public function getUploadDateTime(): ?\DateTimeInterface
    {
        return $this->uploadDateTime;
    }

    public function setUploadDateTime(\DateTimeInterface $uploadDateTime): self
    {
        $this->uploadDateTime = $uploadDateTime;

        return $this;
    }
}
