<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class MachineReportExport implements FromCollection, WithEvents
{
    protected $machines;
    protected $params;
    private const EXCLUDED_MACHINE_IDS = ['SCREW-T', 'SCREW-T BLOWER'];

    public function __construct($machines, $params)
    {
        $this->machines = collect($machines)
            ->filter(function ($machine) {
                $machineId = strtoupper(trim((string) ($machine['name'] ?? '')));
                return !in_array($machineId, self::EXCLUDED_MACHINE_IDS, true);
            })
            ->values();
        $this->params = $params;
    }

    public function collection()
    {
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // HEADER
                $sheet->setCellValue('A1', 'REKAP HM MESIN PRESS');
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->setCellValue('A2', 'PABRIK MENGANTI');
                $sheet->mergeCells('A2:J2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->setCellValue('A3', 'PERIODE : ' . $this->params['start'] . ' - ' . $this->params['end']);
                $sheet->mergeCells('A3:J3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // TABLE HEADERS (Row 5)
                $headers = ['NO', 'NO MESIN', 'LINE', 'AWAL', 'AKHIR', 'PEMAKAIAN PER MESIN DALAM 1 MINGGU (JAM)', 'HARI KERJA', 'JAM PER HARI', 'JUMLAH BRIKET (BATANG)', 'KETERANGAN'];

                $sheet->setCellValue('A5', 'NO');
                $sheet->setCellValue('B5', 'NO MESIN');
                $sheet->setCellValue('C5', 'LINE');
                $sheet->mergeCells('D5:E5');
                $sheet->setCellValue('D5', 'H M');
                $sheet->setCellValue('D6', 'AWAL');
                $sheet->setCellValue('E6', 'AKHIR');
                $sheet->setCellValue('F5', 'PEMAKAIAN PER MESIN DALAM 1 MINGGU (JAM)');
                $sheet->setCellValue('G5', 'HARI KERJA');
                $sheet->setCellValue('H5', 'JAM PER HARI');
                $sheet->setCellValue('I5', 'JUMLAH BRIKET (BATANG)');
                $sheet->setCellValue('J5', 'KETERANGAN');

                // Merge header cells vertically for single-row headers
                $sheet->mergeCells('A5:A6');
                $sheet->mergeCells('B5:B6');
                $sheet->mergeCells('C5:C6');
                $sheet->mergeCells('F5:F6');
                $sheet->mergeCells('G5:G6');
                $sheet->mergeCells('H5:H6');
                $sheet->mergeCells('I5:I6');
                $sheet->mergeCells('J5:J6');

                // Header styling
                $sheet->getStyle('A5:J6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // DATA ROWS
                $row = 7;
                $machineCount = $this->machines->count();

                foreach ($this->machines as $index => $machine) {
                    $sheet->setCellValue("A{$row}", $index + 1);
                    $sheet->setCellValue("B{$row}", $machine['name']);
                    $sheet->setCellValue("C{$row}", $this->params['line']);
                    $sheet->setCellValue("D{$row}", $machine['hm_awal']);

                    // For visual consistency: HM Akhir = HM Awal + Pemakaian
                    $calculatedHM = $machine['hm'];
                    $visualHMAkhir = $machine['hm_awal'] + $calculatedHM;
                    $sheet->setCellValue("E{$row}", $visualHMAkhir);

                    // PEMAKAIAN PER MESIN: 1 decimal place
                    $sheet->setCellValue("F{$row}", $calculatedHM);
                    $sheet->setCellValue("G{$row}", $this->params['hari_kerja']);

                    // JAM PER HARI: actual average usage per day, no decimals
                    $jamPerHari = $this->params['hari_kerja'] > 0 ? $calculatedHM / $this->params['hari_kerja'] : 0;
                    $sheet->setCellValue("H{$row}", $jamPerHari);

                    $row++;
                }

                $dataEndRow = $row - 1;

                // Apply borders to data
                $sheet->getStyle("A7:J{$dataEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Center align data
                $sheet->getStyle("A7:H{$dataEndRow}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // MERGED BRIKET CELL (right side of table)
                $briketStartRow = 7;
                $briketEndRow = $dataEndRow;
                $sheet->mergeCells("I{$briketStartRow}:I{$briketEndRow}");
                $sheet->setCellValue("I{$briketStartRow}", $this->params['total_briket']);
                $sheet->getStyle("I{$briketStartRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'font' => ['bold' => true],
                ]);

                // TOTAL ROWS (PART OF THE TABLE)
                $totalRow = $dataEndRow + 1;
                $totalHours = $this->machines->sum('hm');
                $totalSeconds = $totalHours * 3600;

                // TOTAL JAM KERJA KESELURUHAN (Input value directly)
                $totalWorkingHoursSingle = $this->params['jam_kerja'];

                // TOTAL JAM
                $sheet->mergeCells("D{$totalRow}:E{$totalRow}");
                $sheet->setCellValue("D{$totalRow}", 'TOTAL JAM');
                $sheet->setCellValue("F{$totalRow}", $totalHours);

                $totalDailyAvg = $this->machines->sum(function ($m) {
                    return $this->params['hari_kerja'] > 0 ? $m['hm'] / $this->params['hari_kerja'] : 0;
                });
                $sheet->setCellValue("H{$totalRow}", $totalDailyAvg);
                $sheet->setCellValue("I{$totalRow}", $this->params['total_briket']);

                // TOTAL DETIK
                $sheet->mergeCells("D" . ($totalRow + 1) . ":E" . ($totalRow + 1));
                $sheet->setCellValue("D" . ($totalRow + 1), 'TOTAL DETIK');
                $sheet->setCellValue("F" . ($totalRow + 1), $totalSeconds);

                // TOTAL JAM KERJA
                $sheet->mergeCells("D" . ($totalRow + 2) . ":E" . ($totalRow + 2));
                $sheet->setCellValue("D" . ($totalRow + 2), 'TOTAL JAM KERJA');
                $sheet->setCellValue("F" . ($totalRow + 2), $totalWorkingHoursSingle);

                // Style the total rows (Headers & Values)
                $sheet->getStyle("D{$totalRow}:I" . ($totalRow + 2))->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Apply borders ONLY to specific non-empty cells
                // Row TOTAL JAM
                $sheet->getStyle("D{$totalRow}:F{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("H{$totalRow}:I{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Row TOTAL DETIK
                $sheet->getStyle("D" . ($totalRow + 1) . ":F" . ($totalRow + 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Row TOTAL JAM KERJA
                $sheet->getStyle("D" . ($totalRow + 2) . ":F" . ($totalRow + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Cleanup: remove border from unused cells in total rows if needed, 
                // but usually better to just border the specific ones we used.

                // SUMMARY AT BOTTOM (as small table)
                $avgMachines = $totalWorkingHoursSingle > 0 ? $totalHours / $totalWorkingHoursSingle : 0;

                $summaryRow2 = $totalRow + 4;
                $secondsPerBriket = $this->params['total_briket'] > 0 ? $totalSeconds / $this->params['total_briket'] : 0;

                $sheet->mergeCells("B{$summaryRow2}:D{$summaryRow2}");
                $sheet->setCellValue("B{$summaryRow2}", "TOTAL PEMAKAIAN\nMESIN PRESS\n(DETIK)");

                $sheet->mergeCells("E{$summaryRow2}:F{$summaryRow2}");
                $sheet->setCellValue("E{$summaryRow2}", "JUMLAH\nBRIKET\n(BATANG)");

                $sheet->mergeCells("G{$summaryRow2}:H{$summaryRow2}");
                $sheet->setCellValue("G{$summaryRow2}", "1 BATANG\n(DETIK)");

                // Values for Table 2
                $sheet->mergeCells("B" . ($summaryRow2 + 1) . ":D" . ($summaryRow2 + 1));
                $sheet->setCellValue("B" . ($summaryRow2 + 1), $totalSeconds);

                $sheet->mergeCells("E" . ($summaryRow2 + 1) . ":F" . ($summaryRow2 + 1));
                $sheet->setCellValue("E" . ($summaryRow2 + 1), $this->params['total_briket']);

                $sheet->mergeCells("G" . ($summaryRow2 + 1) . ":H" . ($summaryRow2 + 1));
                $sheet->setCellValue("G" . ($summaryRow2 + 1), $secondsPerBriket);

                $sheet->getStyle("B{$summaryRow2}:H" . ($summaryRow2 + 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                ]);

                // Table 3: TOTAL PEMAKAIAN (JAM), TOTAL JAM KERJA, RATA-RATA
                $summaryRow3 = $summaryRow2 + 3;

                $sheet->mergeCells("B{$summaryRow3}:D{$summaryRow3}");
                $sheet->setCellValue("B{$summaryRow3}", "TOTAL PEMAKAIAN\nMESIN PRESS\n(JAM)");

                $sheet->mergeCells("E{$summaryRow3}:F{$summaryRow3}");
                $sheet->setCellValue("E{$summaryRow3}", "TOTAL\nJAM KERJA");

                $sheet->mergeCells("G{$summaryRow3}:H{$summaryRow3}");
                $sheet->setCellValue("G{$summaryRow3}", "RATA-RATA\nMESIN YANG JALAN");

                // Values for Table 3
                $sheet->mergeCells("B" . ($summaryRow3 + 1) . ":D" . ($summaryRow3 + 1));
                $sheet->setCellValue("B" . ($summaryRow3 + 1), $totalHours);

                $sheet->mergeCells("E" . ($summaryRow3 + 1) . ":F" . ($summaryRow3 + 1));
                $sheet->setCellValue("E" . ($summaryRow3 + 1), $totalWorkingHoursSingle);

                $sheet->mergeCells("G" . ($summaryRow3 + 1) . ":H" . ($summaryRow3 + 1));
                $sheet->setCellValue("G" . ($summaryRow3 + 1), $avgMachines);

                $sheet->getStyle("B{$summaryRow3}:H" . ($summaryRow3 + 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                ]);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(6);
                $sheet->getColumnDimension('D')->setWidth(10);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(25);
                $sheet->getColumnDimension('G')->setWidth(10);
                $sheet->getColumnDimension('H')->setWidth(10);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);

                // Row heights for header
                $sheet->getRowDimension(5)->setRowHeight(30);
                $sheet->getRowDimension(6)->setRowHeight(20);

                // Row heights for summary headers
                $sheet->getRowDimension($summaryRow2)->setRowHeight(40);
                $sheet->getRowDimension($summaryRow3)->setRowHeight(40);

                // ==========================================
                // NUMBER FORMATTING (INDONESIAN STYLE)
                // ==========================================

                // Data Rows
                $sheet->getStyle("D7:F{$dataEndRow}")->getNumberFormat()->setFormatCode('#,##0.0'); // HM Awal, Akhir, Pemakaian
                $sheet->getStyle("G7:H{$dataEndRow}")->getNumberFormat()->setFormatCode('#,##0');   // Hari Kerja, Jam/Hari
                $sheet->getStyle("I{$briketStartRow}:I{$briketEndRow}")->getNumberFormat()->setFormatCode('#,##0'); // Jml Briket

                // Total Rows
                $sheet->getStyle("F{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.0'); // Total Jam
                $sheet->getStyle("H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');   // Daily Avg
                $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');   // Total Briket
                $sheet->getStyle("F" . ($totalRow + 1))->getNumberFormat()->setFormatCode('#,##0'); // Total Detik
                $sheet->getStyle("F" . ($totalRow + 2))->getNumberFormat()->setFormatCode('#,##0'); // Total Jam Kerja

                // Summary Tables
                $sheet->getStyle("B" . ($summaryRow2 + 1))->getNumberFormat()->setFormatCode('#,##0');   // Total Detik
                $sheet->getStyle("E" . ($summaryRow2 + 1))->getNumberFormat()->setFormatCode('#,##0');   // Total Briket
                $sheet->getStyle("G" . ($summaryRow2 + 1))->getNumberFormat()->setFormatCode('#,##0.0'); // 1 Batang ( Detik )

                $sheet->getStyle("B" . ($summaryRow3 + 1))->getNumberFormat()->setFormatCode('#,##0.0'); // Total Jam
                $sheet->getStyle("E" . ($summaryRow3 + 1))->getNumberFormat()->setFormatCode('#,##0');   // Total Jam Kerja
                $sheet->getStyle("G" . ($summaryRow3 + 1))->getNumberFormat()->setFormatCode('#,##0');   // Avg Machine
            },
        ];
    }
}
