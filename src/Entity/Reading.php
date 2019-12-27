<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReadingRepository")
 * 
 * @UniqueEntity(fields={"code"}, message="Un autre relevé possède déjà ce code. Veuillez définir un code unique.")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Station", inversedBy="readings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $station;

    /**
     * Code attribué au relevé.
     * 
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/^[A-Za-z0-9\-]+$/", message="Le code ne peut contenir que des lettres, des chiffres et des tirets")
     */
    private $code;

    /**
     * Date et heure des mesures sur le terrain.
     * 
     * @ORM\Column(type="datetime")
     */
    private $fieldDateTime;

    /**
     * Auteur de l'encodage.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="encodedReadings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $encodingAuthor;

    /**
     * Date et heure de l'encodage.
     * 
     * @ORM\Column(type="datetime")
     */
    private $encodingDateTime;

    /**
     * Remarques ajoutées lors de l'encodage.
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $encodingNotes;

    /**
     * Auteur de la validation.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="validatedReadings")
     */
    private $validationAuthor;

    /**
     * Date et heure de la validation.
     * 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validationDateTime;

    /**
     * Remarques ajoutées lors de la validation.
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $validationNotes;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Measure",
     *     mappedBy="reading",
     *     orphanRemoval=true)
     * 
     * @Assert\Valid()
     */
    private $measures;

    /**
     * Etat du relevé, avec trois valeurs possibles:
     *     - "Soumis"   => null,
     *     - "Validé"   => true,
     *     - "Invalidé" => false.
     * 
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $validated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SystemReading", inversedBy="stationReadings")
     */
    private $systemReading;

    /**
     * Construit une instance de relevé.
     */
    public function __construct()
    {
        $this->measures = new ArrayCollection();
    }

    /**
     * Retourne le code du relevé.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }

    /**
     * Met à jour les propriétés du relevé avant qu'il ne soit mémorisé dans la
     * base de données.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function update() {
        if (empty($this->code)) {
            /* Calculer le code suivant dans la séquence en parcourant les autres relevés */
            $highestNumber = 0;
            foreach ($this->station->getReadings() as $reading) {
                $code = $reading->getCode();
                /* Rechercher le début du nombre situé en fin de chaîne */
                $i = strlen($code);
                while ($i > 0 && is_numeric($code[$i - 1])) {
                    $i--;
                }
                /* Déterminer si ce nombre est le plus élevé rencontré */
                if (($i > 0) && ($code[$i - 1] == '-')) {
                    $number = (int)substr($code, $i);
                    if ($number > $highestNumber) {
                        $highestNumber = $number;
                    }
                }
            }
            /* Formater le code en utilisant celui de la station comme préfixe (même si d'autres préfixes ont été rencontrés durant l'examen des autres relevés) */
            $this->code = $this->station->getCode() . '-' .
                sprintf("%04u", $highestNumber + 1);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
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

    public function getEncodingAuthor(): ?User
    {
        return $this->encodingAuthor;
    }

    public function setEncodingAuthor(?User $encodingAuthor): self
    {
        $this->encodingAuthor = $encodingAuthor;

        return $this;
    }

    public function getValidationAuthor(): ?User
    {
        return $this->validationAuthor;
    }

    public function setValidationAuthor(?User $validationAuthor): self
    {
        $this->validationAuthor = $validationAuthor;

        return $this;
    }

    public function getStation(): ?Station
    {
        return $this->station;
    }

    public function setStation(?Station $station): self
    {
        $this->station = $station;

        return $this;
    }

    public function getFieldDateTime(): ?\DateTimeInterface
    {
        return $this->fieldDateTime;
    }

    public function setFieldDateTime(\DateTimeInterface $fieldDateTime): self
    {
        $this->fieldDateTime = $fieldDateTime;

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
            $measure->setReading($this);
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            // set the owning side to null (unless already changed)
            if ($measure->getReading() === $this) {
                $measure->setReading(null);
            }
        }

        return $this;
    }

    /**
     * Retourne les statistiques des mesures correspondant à un paramètre
     * défini.
     * 
     * @param Parameter $parameter
     * @return array Un tableau associatif contenant les statistiques.
     * 
     * Le tableau contient les statistiques ci-dessous concernant les valeurs
     * connues et renseignées comme valides:
     *     - 'count': quantité de valeurs,
     *     - 'min': plus petite valeur,
     *     - 'max': plus grande valeur,
     *     - 'avg': valeur moyenne,
     *     - 'sum': somme des valeurs.
     * 
     * Le tableau contient les statistiques suivantes concernant les valeurs
     * nulles ou invalides:
     *     - 'inval': quantité de valeurs.
     * 
     * Le fait que les mesures soient stabilisées ou non n'a pas d'incidence
     * parce que les valeurs non stabilisées ont tout de même été appréciées
     * par le contributeur de la mesure.
     *
     * TODO: Les statistiques pourraient prendre en compte la tolérance
     * éventuellement associée à chaque valeur. Elles ne le font actuellement
     * pas.
     */
    public function getValueStats(Parameter $parameter)
    {
        $count = 0;
        $avg = null;
        $min = null;
        $max = null;
        $sum = null;
        $alarms = null;
        $inval = null;

        /* Parcourir toutes les mesures */
        foreach ($this->measures as $measure) {
            if ($measure->getParameter() === $parameter) {
                /* Ajouter aux statistiques, sur base des critères définis dans
                la description de la fonction */
                $value = $measure->getValue();
                if (!is_null($value) && $measure->getValid()) {
                    $count++;
                    $sum += $value;
                    if ((null === $min) || ($value < $min)) {
                        $min = $value;
                    }
                    if ((null === $max) || ($value > $max)) {
                        $max = $value;
                    }
                    $avg = $sum / $count;
                    if (null !== $measure->getAlarm()) {
                        $alarms++;
                    }
                } else {
                    $inval++;
                }
            }
        }

        return [
            'count' => $count,      /* Nombre de valeurs valides */
            'min' => $min,          /* Valeur minimum */
            'max' => $max,          /* Valeur maximum */
            'avg' => $avg,          /* Valeur moyenne */
            'sum' => $sum,          /* Somme des valeurs */
            'alarms' => $alarms,    /* Nombre d'alarmes */
            'inval' => $inval,      /* Nombre de valeurs invalides */
        ];
    }

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(?bool $validated): self
    {
        $this->validated = $validated;

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
}
