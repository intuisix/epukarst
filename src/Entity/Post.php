<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Article pour le site public.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks()
 * 
 * @UniqueEntity(fields={"slug"}, message="Un autre article possède déjà ce slug. Veuillez en choisir un autre.")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishFromDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishToDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $summary;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $home;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orderNumber;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $topMenu;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="parent")
     */
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Met à jour les propriétés de l'article avant qu'il ne soit mémorisé dans
     * la base de données.
     * 
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function update()
    {
        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->Slugify($this->title);
        }
        dump($this);
    }

    /**
     * Publie un article à partir de maintenant.
     *
     * @return void
     */
    public function publish()
    {
        $now = new \DateTime();

        /* Publier à partir de maintenant, si la publication n'était pas encore programmée ou si elle l'était dans le futur */
        if ((null == $this->publishFromDate) || ($this->publishFromDate > $now)) {
            $this->setPublishFromDate($now);
        }

        /* Déprogrammer la /dépublication/ si elle l'est dans le passé, parce qu'elle empêcherait la publication à partir de maintenant */
        if ((null != $this->publishToDate) && ($this->publishToDate <= $now)) {
            $this->setPublishToDate(null);
        }
    }

    /**
     * Dépublie un article à partir de maintenant.
     *
     * @return void
     */
    public function unpublish()
    {
        $now = new \DateTime();

        /* Dépublier à partir de maintenant, si la dépublication n'était pas encore programmée ou si elle l'était dans le futur */
        if ((null == $this->publishToDate) || ($this->publishToDate > $now)) {
            $this->setPublishToDate($now);
        }

        /* Déprogrammer la /publication/ si elle l'est dans le futur */
        if ((null != $this->publishFromDate) && ($this->publishFromDate >= $now)) {
            $this->setPublishFromDate(null);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublishFromDate(): ?\DateTimeInterface
    {
        return $this->publishFromDate;
    }

    public function setPublishFromDate(?\DateTimeInterface $publishFromDate): self
    {
        $this->publishFromDate = $publishFromDate;

        return $this;
    }

    public function getPublishToDate(): ?\DateTimeInterface
    {
        return $this->publishToDate;
    }

    public function setPublishToDate(?\DateTimeInterface $publishToDate): self
    {
        $this->publishToDate = $publishToDate;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getHome(): ?bool
    {
        return $this->home;
    }

    public function setHome(?bool $home): self
    {
        $this->home = $home;

        return $this;
    }

    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?int $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getTopMenu(): ?bool
    {
        return $this->topMenu;
    }

    public function setTopMenu(?bool $topMenu): self
    {
        $this->topMenu = $topMenu;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }
}
