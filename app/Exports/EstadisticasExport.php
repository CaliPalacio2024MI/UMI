<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstadisticasExport implements FromArray, WithColumnWidths, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 25,
            'C' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ===== TITULOS PRINCIPALES =====
        $titulos = [1, 5, 11];

        foreach ($titulos as $fila) {
            $sheet->getStyle("A{$fila}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => 'D9E1F2'] // azul claro
                ],
            ]);
        }

        // ===== ENCABEZADOS =====
        $encabezados = [1, 5, 11];

        foreach ($encabezados as $fila) {
            $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ],
            ]);
        }

        // ===== CENTRAR COLUMNAS NUMERICAS =====
        $sheet->getStyle('B:C')->getAlignment()->setHorizontal('center');

    }
}