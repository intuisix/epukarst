<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilterMeasureRepository")
 */
class FilterMeasure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minimumValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maximumValue;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Filter", inversedBy="measures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $filter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parameter")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parameter;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinimumValue(): ?float
    {
        return $this->minimumValue;
    }

    public function setMinimumValue(?float $minimumValue): self
    {
        $this->minimumValue = $minimumValue;

        return $this;
    }

    public function getMaximumValue(): ?float
    {
        return $this->maximumValue;
    }

    public function setMaximumValue(?float $maximumValue): self
    {
        $this->maximumValue = $maximumValue;

        return $this;
    }

    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    public function setFilter(?Filter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getParameter(): ?Parameter
    {
        return $this->parameter;
    }

    public function setParameter(?Parameter $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }
}
