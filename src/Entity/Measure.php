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
        indique qu'elle est valide: ainsi, il est possible d'introduire une
        valeur pour laquelle il existe un doute mentionné explicitement */
        if ($this->valid) {
            $parameter = $this->measurability->getParameter();
            $instrument = $this->measurability->getInstrument();
    
            if (null !== $parameter) {
                /* Comparer la valeur au seuil minimum de l'instrument  */
                $instrumentMinimum = $this->measurability->getMinimumValue();
                if ((null !== $instrumentMinimum) &&
                    ($this->value < $instrumentMinimum)) {
                    $context
                        ->buildViolation("Cette valeur est inférieure à ce que l'instrument est capable de mesurer (au minimum $instrumentMinimum {$parameter->getUnit()}).")
                        ->atPath('value')
                        ->addViolation();
                }
        
                /* Comparer la valeur au seuil maximum de l'instrument */
                $instrumentMaximum = $this->measurability->getMaximumValue();
                if ((null !== $instrumentMaximum) &&
                    ($this->value > $instrumentMaximum))
                {
                    $context
                        ->buildViolation("Cette valeur est supérieure à ce que l'instrument est capable de mesurer (au maximum $instrumentMaximum {$parameter->getUnit()}).")
                        ->atPath('value')
                        ->addViolation();
                }
            }

            /* Comparer la date de terrain à l'étalonnage de l'instrument.
            TODO: Cette comparaison devrait être réalisée lorsque la mesure
            vient d'être saisie, or elle n'est pas encore rattachée au relevé.
            La comparaison est bien réalisée lors de la modification et aussi
            lors la validation du relevé. */
            if ((null != $instrument) && (null != $this->reading)) {
                $calibrationDueDate = $instrument->getCalibrationDueDate();
                if ((null != $calibrationDueDate) && ($this->reading->getFieldDateTime() > $calibrationDueDate)) {
                    $context
                        ->buildViolation("Cette valeur a été mesurée à l'aide d'un instrument non contrôlé depuis le {$calibrationDueDate->format('d/m/Y')}.")
                        ->atPath('value')
                        ->addViolation();
                }
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
