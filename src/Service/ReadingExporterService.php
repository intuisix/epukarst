<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReadingExporterService
{
    /**
     * Liste des relevés à exporter.
     *
     * @var ArrayCollection
     */
    private $readings;

    /**
     * Liste des paramètres à exporter.
     *
     * @var ArrayCollection
     */
    private $parameters;

    /**
     * Définit si les valeurs minimum et maximum, ainsi que le nombre de valeurs seront exportées.
     *
     * @var bool
     */
    private $withMinMax;

    /**
     * Document.
     *
     * @var Spreadsheet
     */
    private $spreadsheet;

    /**
     * Classeur.
     *
     * @var \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet
     */
    private $worksheet;

    public function getReadings()
    {
        return $this->readings;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setReadings($readings)
    {
        $this->readings = $readings;
        return $this;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Génère le document.
     *
     * @return Spreadsheet
     */
    public function getSpreadsheet(): Spreadsheet
    {
        $withMinMax = (bool)($_ENV['EXPORT_MIN_MAX'] ?? false);

        $this->spreadsheet = new Spreadsheet();
        $this->worksheet = $this->spreadsheet->getActiveSheet();

        /* Créer la rangée des en-têtes */
        $row = $column = 1;
        $this->setHeader($dateTimeColumn = $column++, $row, "Terrain", true);
        $this->setHeader($codeColumn = $column++, $row, "Code", true);
        $this->setHeader($systemColumn = $column++, $row, "Système", true);
        $this->setHeader($basinColumn = $column++, $row, "Bassin", true);
        $this->setHeader($stationColumn = $column++, $row, "Station", true);
        $this->setHeader($stateColumn = $column++, $row, "Etat", true);

        $firstValueColumn = $column;
        foreach ($this->parameters as $parameter) {
            $name = $parameter->getNameWithUnit();
            $firstColumn = $column;
            if ($withMinMax) {
                /* Nombre, minimum, moyenne et maximum */
                $this->setHeader($column++, $row, "$name\nnombre", false);
                $this->setHeader($column++, $row, "$name\nminimum", false);
                $this->setHeader($column++, $row, "$name\nmoyen", false);
                $this->setHeader($column++, $row, "$name\nmaximum", false);
            } else {
                /* Moyenne seule */
                $this->setHeader($column++, $row, $name, false);
            }
        }

        /* Geler la rangée des en-têtes de colonnes */
        $this->worksheet->freezePane('A2');

        /* Créer une rangée pour chaque relevé */
        foreach ($this->readings as $reading) {
            $row++;
            $station = $reading->getStation();
            $basin = $station->getBasin();
            $system = $basin->getSystem();
            $fieldDateTime = $reading->getFieldDateTime();
            $validated = $reading->getValidated();

            /* Volet */
            $this->setDateTime($dateTimeColumn, $row, $fieldDateTime);
            $this->setString($codeColumn, $row, $reading->getCode());
            $this->setString($systemColumn, $row, $system->getName());
            $this->setString($basinColumn, $row, $basin->getName());
            $this->setString($stationColumn, $row, $station->getName());
            $this->setString($stateColumn, $row,
                (null === $validated) ? "Soumis" :
                ($validated ? "Validé" : "Invalidé"));

            /* Paramètres */
            $column = $firstValueColumn;
            foreach ($this->parameters as $parameter) {
                $stats = $reading->getValueStats($parameter);
                if ($withMinMax) {
                    /* Nombre, minimum, moyenne et maximum */
                    $this->setCell($column++, $row,
                        (0 != $stats['count']) ? $stats['count'] : null);
                    $this->setCell($column++, $row,
                        $parameter->formatValue($stats['min'], true));
                    $this->setCell($column++, $row,
                        $parameter->formatValue($stats['avg'], true));
                    $this->setCell($column++, $row,
                        $parameter->formatValue($stats['max'], true));
                } else {
                    /* Moyenne seule */
                    $this->setCell($column++, $row,
                        $parameter->formatValue($stats['avg'], true));
                }
            }
        }

        /* Définir un filtre automatique sur l'entièreté de la feuille */
        $this->worksheet->setAutoFilter($this->worksheet->calculateWorksheetDimension());

        return $this->spreadsheet;
    }

    /**
     * Remplit le contenu d'un en-tête de colonne.
     *
     * @param int $column
     * @param int $row
     * @param string $text
     * @param boolean $autoSize
     * @return void
     */
    private function setHeader(int $column, int $row, string $text, bool $autoSize)
    {
        $this->worksheet->setCellValueByColumnAndRow($column, $row, $text);
        $this->worksheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
        $this->worksheet->getColumnDimensionByColumn($column)->setAutoSize($autoSize);
    }

    /**
     * Remplit une cellule avec le type "standard".
     *
     * @param integer $column
     * @param integer $row
     * @param [type] $value
     * @return void
     */
    private function setCell(int $column, int $row, $value)
    {
        $this->worksheet->setCellValueByColumnAndRow($column, $row, $value);
    }

    /**
     * Remplit le contenu d'une cellule avec le type "texte".
     *
     * @param integer $column
     * @param integer $row
     * @param [type] $value
     * @return void
     */
    private function setString(int $column, int $row, $value)
    {
        $this->worksheet->setCellValueExplicitByColumnAndRow($column, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }

    /**
     * Remplit le contenu d'une cellule avec le type "date et heure".
     *
     * @param integer $column
     * @param integer $row
     * @param [type] $value
     * @return void
     */
    private function setDateTime(int $column, int $row, $value)
    {
        $this->worksheet->setCellValueByColumnAndRow($column, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value));
        $this->worksheet->getStyleByColumnAndRow($column, $row)
            ->getNumberFormat()
            ->setFormatCode('d/mm/yyyy hh:mm');
    }
}