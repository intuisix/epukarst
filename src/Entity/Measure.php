<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="datetime")
     */
    private $fieldDateTime;

    /**
     * Indique si la valeur doit être convertie.
     */
    private $conversionRequired;

    /**
     * Indique si la valeur a été convertie.
     */
    private $conversionDone;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Alarm", inversedBy="measures")
     * @ORM\JoinColumn(nullable=true)
     */
    private $alarm;

    /**
     * Construit une mesure.
     */
    public function __construct(bool $conversionRequired = false)
    {
        $this->conversionRequired = $conversionRequired;
    }

    /**
     * Convertit la valeur et valide la mesure:
     * 
     * Réalise la conversion si celle-ci est demandée et n'a pas encore été
     * effectuée et, en même temps, la valeur est renseignée comme étant valide.
     * 
     * Teste la valeur par rapport aux limites physiques du paramètre, ainsi
     * que par rapport aux limites de mesure de l'instrument.
     * 
     * Teste que la date limite de l'instrument n'est pas dépassée.
     * 
     * Teste que l'alarme renseignée est bien une alarme appartenant au même
     * système.
     * 
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        /* Tester la plausibilité de la mesure seulement si l'utilisateur a
        spécifié la valeur et a indiqué qu'elle est valide:
        1) une valeur non introduite dans le tableau du formulaire d'encodage
           de système ne donne pas lieu à la création d'une mesure;
        2) il est possible d'introduire une valeur pour laquelle il existe un
           doute mentionné explicitement */
        if ((null !== $this->value) && (null != $this->measurability) && (true === $this->valid)) {
            $parameter = $this->measurability->getParameter();
            $instrument = $this->measurability->getInstrument();

            /* Si une conversion est prévue, la réaliser et afficher une erreur en cas de problème d'interprétation de la formule */
            if ($this->conversionRequired && !$this->conversionDone) {
                /* Tenter de convertir la valeur */
                try {
                    $this->value = $this->measurability->convert($this->value);
                    $this->conversionDone = true;
                } catch (\Exception $exception) {
                    $context
                        ->buildViolation("Erreur pendant la conversion: \"{$exception->getMessage()}\".")
                        ->atPath('value')
                        ->addViolation();
                }
            }

            if (null !== $parameter) {
                /* Comparer la valeur aux seuils minimum */
                $instrumentMinimum = $this->measurability->getMinimumValue();
                $physicalMinimum = $parameter->getPhysicalMinimum();
                if ((null !== $instrumentMinimum) && ($this->value < $instrumentMinimum)) {
                    $context
                        ->buildViolation("Cette valeur est inférieure à ce que l'instrument est capable de mesurer (≥{$parameter->formatValue($instrumentMinimum)} {$parameter->getUnit()}).")
                        ->atPath('value')
                        ->addViolation();
                } else if ((null !== $physicalMinimum) && ($this->value < $physicalMinimum)) {
                    $context
                        ->buildViolation("Cette valeur est inférieure à la limite physique (≥ {$parameter->formatValue($physicalMinimum)} {$parameter->getUnit()}).")
                        ->atPath('value')
                        ->addViolation();
                }

                /* Comparer la valeur aux seuils maximum */
                $instrumentMaximum = $this->measurability->getMaximumValue();
                $physicalMaximum = $parameter->getPhysicalMaximum();
                if ((null !== $instrumentMaximum) && ($this->value > $instrumentMaximum))
                {
                    $context
                        ->buildViolation("Cette valeur est supérieure à ce que l'instrument est capable de mesurer (≤{$parameter->formatValue($instrumentMaximum)} {$parameter->getUnit()}).")
                        ->atPath('value')
                        ->addViolation();
                } else if ((null !== $physicalMaximum) && ($this->value > $physicalMaximum)) {
                    $context
                        ->buildViolation("Cette valeur est supérieure à la limite physique (≤ {$parameter->formatValue($physicalMaximum)} {$parameter->getUnit()}).")
                        ->atPath('value')
                        ->addViolation();
                }
            }

            /* Comparer la date de terrain à la date de validité de tous les
            instruments requis dans la chaîne de mesure */
            if ((null !== $instrument)) {
                /* Déterminer la liste des instruments */
                $requiredInstruments = [];
                $instrument->findAllRequiredInstruments($requiredInstruments);
                /* Vérifier pour chaque instrument */
                foreach ($requiredInstruments as $requiredInstrument) {
                    $calibrationDueDate = $requiredInstrument->getCalibrationDueDate();
                    if ((null !== $calibrationDueDate) &&
                        ($this->fieldDateTime > $calibrationDueDate)) {
                        /* Erreur */
                        $context
                            ->buildViolation("Cette valeur a été mesurée à l'aide de {$requiredInstrument->getName()} non contrôlé depuis le {$calibrationDueDate->format('d/m/Y')}.")
                            ->atPath('value')
                            ->addViolation();
                    }
                }
            }
        }

        /* Si une alarme est spécifiée, vérifier que celle-ci concerne le même système que le relevé */
        if (null !== $this->alarm) {
            if ($this->alarm->getSystem() !== $this->reading->getStation()->getBasin()->getSystem()) {
                $context
                    ->buildViolation("L'alarme doit concerner le même système que le relevé.")
                    ->atPath('alarm')
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

    public function setValue(?float $value): self
    {
        $this->value = $value;
        $this->conversionDone = false;

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

    public function setStable(?bool $stable): self
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

    public function getFieldDateTime(): ?\DateTimeInterface
    {
        return $this->fieldDateTime;
    }

    public function setFieldDateTime(?\DateTimeInterface $fieldDateTime): self
    {
        $this->fieldDateTime = $fieldDateTime;

        return $this;
    }

    public function getAlarm(): ?Alarm
    {
        return $this->alarm;
    }

    public function setAlarm(?Alarm $alarm): self
    {
        $this->alarm = $alarm;

        return $this;
    }
}
