<?php

namespace App\Entity;

use App\Entity\Measure;
use App\Entity\SystemParameter;
use Doctrine\ORM\Mapping as ORM;
use FormulaParser\FormulaParser;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasurabilityRepository")
 */
class Measurability
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parameter", inversedBy="measurabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parameter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Instrument", inversedBy="measurabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $instrument;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maximumValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minimumValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tolerance;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measure", mappedBy="measurability")
     */
    private $measures;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SystemParameter", mappedBy="instrumentParameter", orphanRemoval=true)
     */
    private $systemParameters;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $inputUnit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $inputConversion;

    /**
     * Construit l'objet.
     */
    public function __construct()
    {
        $this->measures = new ArrayCollection();
        $this->systemParameters = new ArrayCollection();
    }

    /**
     * Contrôle la validité des propriétés de l'objet.
     * 
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $unit = $this->parameter->getUnit();
        $physicalMaximum = $this->parameter->getPhysicalMaximum();
        $physicalMinimum = $this->parameter->getPhysicalMinimum();

        /* Tester la formule de conversion d'unité */
        if (null !== $this->inputConversion) {
            try {
                $this->convert(1.0);
            }
            catch (\Exception $exception) {
                $context
                    ->buildViolation("La formule est invalide : \"{$exception->getMessage()}\".")
                    ->atPath('inputConversion')
                    ->addViolation();
            }
        }

        /* Tester la valeur minimum */
        if (null !== $this->minimumValue) {
            if ((null !== $physicalMinimum) &&
                ($this->minimumValue < $physicalMinimum)) {
                $context
                    ->buildViolation("Cette valeur est inférieure à ce qui est physiquement possible (≥ $physicalMinimum $unit).")
                    ->atPath('minimumValue')
                    ->addViolation();
            } else if ((null !== $physicalMaximum) &&
                ($this->minimumValue > $physicalMaximum)) {
                $context
                    ->buildViolation("Cette valeur est supérieure à ce qui est physiquement possible (≤ $physicalMaximum $unit).")
                    ->atPath('minimumValue')
                    ->addViolation();
            }
        } else {
            /* Utiliser le minimum physique */
            $this->minimumValue = $physicalMinimum;
        }

        /* Tester la valeur maximum */
        if (null !== $this->maximumValue) {
            if ((null !== $physicalMaximum) &&
                ($this->maximumValue > $physicalMaximum)) {
                $context
                    ->buildViolation("Cette valeur est supérieure à ce qui est physiquement possible (≤ $physicalMaximum $unit).")
                    ->atPath('maximumValue')
                    ->addViolation();
            } else if ((null !== $physicalMinimum) &&
                ($this->maximumValue < $physicalMinimum)) {
                $context
                    ->buildViolation("Cette valeur est inférieure à ce qui est physiquement possible (≥ $physicalMinimum $unit).")
                    ->atPath('maximumValue')
                    ->addViolation();
            }
        } else {
            /* Utiliser le maximum physique */
            $this->maximumValue = $physicalMaximum;
        }
    }

    /**
     * Convertit une valeur en exploitant la formule de conversion éventuelle.
     *
     * @param float $value
     * @return float
     */
    public function convert(float $value): float
    {
        /* Exécuter la formule de conversion */
        if (null !== $this->inputConversion) {
            $parser = new FormulaParser($this->inputConversion);
            $parser->setVariables(['x' => $value]);
            $result = $parser->getResult();
            /* Lever une exception en cas de problème */
            if ($result[0] != 'done') {
                throw new \Exception($result[1]);
            }
            /* Sinon, retourner la valeur résultant de la formule */
            return $result[1];
        }
        /* S'il n'y a pas de formule, retourner la valeur reçue */
        return $value;
    }

    /**
     * Retourne un nom destiné à l'affichage en le composant du nom du
     * paramètre et du nom de l'instrument.
     *
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->parameter->getName() . " : " .
            $this->instrument->getName();
    }

    /**
     * Retourne un nom destiné à l'affichage en le composant du nom du
     * paramètre, du nom de l'instrument et de l'unité de mesure.
     *
     * @return string|null
     */
    public function getNameWithUnit(): ?string
    {
        $unit = $this->parameter->getUnit();
        return
            $this->parameter->getName() . " : " .
            $this->instrument->getName() .
            (empty($unit) ? "" : " [$unit]");
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(?Instrument $instrument): self
    {
        $this->instrument = $instrument;

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

    public function getMinimumValue(): ?float
    {
        return $this->minimumValue;
    }

    public function setMinimumValue(?float $minimumValue): self
    {
        $this->minimumValue = $minimumValue;

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

    /**
     * @return Collection|Measure[]
     */
    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function addMeasure(Measure $measure): self
    {
        if (!$this->measures->contains($measure)) {
            $this->measures[] = $measure;
            $measure->setMeasurability($this);
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            // set the owning side to null (unless already changed)
            if ($measure->getMeasurability() === $this) {
                $measure->setMeasurability(null);
            }
        }

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

    /**
     * @return Collection|SystemParameter[]
     */
    public function getSystemParameters(): Collection
    {
        return $this->systemParameters;
    }

    public function addSystemParameter(SystemParameter $systemParameter): self
    {
        if (!$this->systemParameters->contains($systemParameter)) {
            $this->systemParameters[] = $systemParameter;
            $systemParameter->setInstrumentParameter($this);
        }

        return $this;
    }

    public function removeSystemParameter(SystemParameter $systemParameter): self
    {
        if ($this->systemParameters->contains($systemParameter)) {
            $this->systemParameters->removeElement($systemParameter);
            // set the owning side to null (unless already changed)
            if ($systemParameter->getInstrumentParameter() === $this) {
                $systemParameter->setInstrumentParameter(null);
            }
        }

        return $this;
    }

    public function getInputUnit(): ?string
    {
        return $this->inputUnit;
    }

    public function setInputUnit(?string $inputUnit): self
    {
        $this->inputUnit = $inputUnit;

        return $this;
    }

    public function getInputConversion(): ?string
    {
        return $this->inputConversion;
    }

    public function setInputConversion(?string $inputConversion): self
    {
        $this->inputConversion = $inputConversion;

        return $this;
    }
}
