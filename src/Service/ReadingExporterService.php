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

    public function __construct()
    {
        $this->withMinMax = (bool)($_ENV['EXPORT_MIN_MAX'] ?? false);
    }

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

    public function getSpreadsheet()
    {
        $spreadsheet = new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();

        $column = 1;
        $row = 1;

        /* Créer la rangée des en-têtes */

        $dateTimeColumn = $column;
        $sheet->setCellValueByColumnAndRow($dateTimeColumn, $row, "Date de terrain");
        $sheet->getColumnDimensionByColumn($dateTimeColumn)->setAutoSize(true);
        $column++;

        $codeColumn = $column;
        $sheet->setCellValueByColumnAndRow($codeColumn, $row, "Code");
        $sheet->getColumnDimensionByColumn($codeColumn)->setAutoSize(true);
        $column++;

        $systemColumn = $column;
        $sheet->setCellValueByColumnAndRow($systemColumn, $row, "Système");
        $sheet->getColumnDimensionByColumn($systemColumn)->setAutoSize(true);
        $column++;

        $basinColumn = $column;
        $sheet->setCellValueByColumnAndRow($basinColumn, $row, "Bassin");
        $sheet->getColumnDimensionByColumn($basinColumn)->setAutoSize(true);
        $column++;

        $stationColumn = $column;
        $sheet->setCellValueByColumnAndRow($stationColumn, $row, "Station");
        $sheet->getColumnDimensionByColumn($stationColumn)->setAutoSize(true);
        $column++;

        $stateColumn = $column;
        $sheet->setCellValueByColumnAndRow($stateColumn, $row, "Etat");
        $sheet->getColumnDimensionByColumn($stateColumn)->setAutoSize(true);
        $column++;

        $firstValueColumn = $column;
        foreach ($this->parameters as $parameter) {
            $name = $parameter->getNameWithUnit();
            $firstColumn = $column;
            if ($this->withMinMax) {
                /* Nombre */
                $sheet->setCellValueByColumnAndRow($column, $row, "$name NB");
                $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
                $column++;
                /* Minimum */
                $sheet->setCellValueByColumnAndRow($column, $row, "$name MIN");
                $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
                $column++;
                /* Moyenne */
                $sheet->setCellValueByColumnAndRow($column, $row, "$name MOY");
                $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
                $column++;
                /* Maximum */
                $sheet->setCellValueByColumnAndRow($column, $row, "$name MAX");
                $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
                $column++;
            } else {
                /* Moyenne */
                $sheet->setCellValueByColumnAndRow($column, $row, $name);
                $sheet->getStyleByColumnAndRow($column, $row)->getAlignment()->setWrapText(true);
                $column++;
            }
        }

        /* Geler la rangée des en-têtes de colonnes */
        $sheet->freezePane('A2');

        /* Créer une rangée pour chaque relevé */
        foreach ($this->readings as $reading) {
            $row++;

            $station = $reading->getStation();
            $basin = $station->getBasin();
            $system = $basin->getSystem();
            $fieldDateTime = $reading->getFieldDateTime();
            $validated = $reading->getValidated();

            /* Date de terrain */
            $sheet->setCellValueByColumnAndRow($dateTimeColumn, $row,
                \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fieldDateTime));
            $sheet->getStyleByColumnAndRow($dateTimeColumn, $row)
                ->getNumberFormat()
                ->setFormatCode('d/mm/yyyy hh:mm');

            /* Code */
            $sheet->setCellValueExplicitByColumnAndRow($codeColumn, $row,
                $reading->getCode(),
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            /* Système */
            $sheet->setCellValueByColumnAndRow($systemColumn, $row,
                $system->getName());

            /* Bassin */
            $sheet->setCellValueByColumnAndRow($basinColumn, $row,
                $basin->getName());

            /* Station */
            $sheet->setCellValueByColumnAndRow($stationColumn, $row,
                $station->getName());

            /* Etat */
            $sheet->setCellValueByColumnAndRow($stateColumn, $row,
                (null === $validated) ? "Soumis" :
                ($validated ? "Validé" : "Invalidé"));

            /* Paramètres */
            $column = $firstValueColumn;
            foreach ($this->parameters as $parameter) {
                $stats = $reading->getValueStats($parameter);
                if ($this->withMinMax) {
                    /* Nombre */
                    $count = $stats['count'];
                    $sheet->setCellValueByColumnAndRow($column, $row,
                        (0 != $count) ? $count : null);
                    $column++;
                    /* Minimum */
                    $sheet->setCellValueByColumnAndRow($column, $row,
                        $parameter->formatValue($stats['min'], true));
                    $column++;
                    /* Moyenne */
                    $sheet->setCellValueByColumnAndRow($column, $row,
                        $parameter->formatValue($stats['avg'], true));
                    $column++;
                    /* Maximum */
                    $sheet->setCellValueByColumnAndRow($column, $row,
                        $parameter->formatValue($stats['max'], true));
                    $column++;
                } else {
                    /* Moyenne */
                    $sheet->setCellValueByColumnAndRow($column, $row,
                        $parameter->formatValue($stats['avg'], true));
                    $column++;
                }
            }
        }

        /* Définir un filtre automatique sur l'entièreté de la feuille */
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return $spreadsheet;
    }
}