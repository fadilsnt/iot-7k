<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MachineHistoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $data;
    protected $machine_id;
    protected $range;

    public function __construct($data, $machine_id, $range)
    {
        $this->data = collect($data);
        $this->machine_id = $machine_id;
        $this->range = $range;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['RIWAYAT PERFORMA MESIN'],
            ['Mesin: ' . $this->machine_id],
            ['Periode: ' . $this->range['start'] . ' - ' . $this->range['end']],
            [''],
            ['TIMESTAMP', 'AMP (A)', 'HM (JAM)', 'TEMP (°C)', 'MOIST (%)']
        ];
    }

    public function map($row): array
    {
        return [
            $row['Timestamp'],
            $row['AMP'],
            $row['HM'],
            $row['TEMP'],
            $row['MOIST'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title Styling
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info Styling
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => ['italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Table Header Styling (Row 5)
        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => Fill::FILL_NONE,
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Content Styling
        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 6) {
            $sheet->getStyle('A6:E' . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 12,
        ];
    }
}
