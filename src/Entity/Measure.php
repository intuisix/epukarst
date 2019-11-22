<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasureRepository")
 */
class Measure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Reading", inversedBy="measures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reading;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Measurability", inversedBy="measures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $measurability;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tolerance;

    /**
     * @ORM\Column(type="boolean")
     */
    private $stable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $valid;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="measures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $encodingAuthor;

    /**
     * @ORM\Column(type="datetime")
     */
    private $encodingDateTime;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        /* Tester la plausibilité de la mesure seulement si l'utilisateur
        indique que celle-ci est valide. Cela laisse la possibilité pour
        l'utilisateur, d'introduire une valeur invalide tout en indiquant de
        lui-même qu'elle est effectivement invalide... */
        if ($this->valid) {
            $parameter = $this->measurability->getParameter();
            $unit = $parameter->getUnit();
    
            /* Tester la valeur par rapport aux seuils minimum */
            $instrumentMinimum = $this->measurability->getMinimumValue();
            $physicalMinimum = $parameter->getPhysicalMinimum();
            if ((null !== $physicalMinimum) &&
                ($this->value < $physicalMinimum)) {
                $context
                    ->buildViolation("Cette valeur est supérieure à ce qui est physiquement possible ($physicalMinimum $unit).")
                    ->atPath('value')
                    ->addViolation();
            } else if ((null !== $instrumentMinimum) &&
                ($this->value < $instrumentMinimum)) {
                $context
                    ->buildViolation("Cette valeur est inférieure à ce que l'instrument est capable de mesurer ($instrumentMinimum $unit).")
                    ->atPath('value')
                    ->addViolation();
            }
    
            /* Tester la valeur par rapport aux seuils maximum */
            $instrumentMaximum = $this->measurability->getMaximumValue();
            $physicalMaximum = $parameter->getPhysicalMaximum();
            if ((null !== $physicalMaximum) &&
                ($this->value > $physicalMaximum)) {
                $context
                    ->buildViolation("Cette valeur est supérieure à ce qui est physiquement possible ($physicalMaximum $unit).")
                    ->atPath('value')
                    ->addViolation();
            } else if ((null !== $instrumentMaximum) &&
                ($this->value > $instrumentMaximum))
            {
                $context
                    ->buildViolation("Cette valeur est supérieure à ce que l'instrument est capable de mesurer ($instrumentMaximum $unit).")
                    ->atPath('value')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReading(): ?Reading
    {
        return $this->reading;
    }

    public function setReading(?Reading $reading): self
    {
        $this->reading = $reading;

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

    public function getTolerance(): ?float
    {
        return $this->tolerance;
    }

    public function setTolerance(?float $tolerance): self
    {
        $this->tolerance = $tolerance;

        return $this;
    }

    public function getStable(): ?bool
    {
        return $this->stable;
    }

    public function setStable(bool $stable): self
    {
        $this->stable = $stable;

        return $this;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

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

    public function getEncodingAuthor(): ?User
    {
        return $this->encodingAuthor;
    }

    public function setEncodingAuthor(?User $encodingAuthor): self
    {
        $this->encodingAuthor = $encodingAuthor;

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

    public function getMeasurability(): ?Measurability
    {
        return $this->measurability;
    }

    public function setMeasurability(?Measurability $measurability): self
    {
        $this->measurability = $measurability;

        return $this;
    }

    public function getParameter()
    {
        return $this->measurability->getParameter();
    }
}
