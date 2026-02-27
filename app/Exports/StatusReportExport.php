<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/* 2023 SilverDust) S. Maceren */

class StatusReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    protected string $reportType;
    protected array $data;
    protected array $headers;
    protected string $branchName;

    public function __construct(string $reportType, array $data, array $headers, string $branchName)
    {
        $this->reportType = $reportType;
        $this->data = $data;
        $this->headers = $headers;
        $this->branchName = $branchName;
    }

    public function title(): string
    {
        return substr($this->reportType . ' Report', 0, 31);
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $i => $row) {
            $rows[] = array_merge([$i + 1], array_values($row));
        }
        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headers));

                // ── 1. Insert title row at the very top ──────────────────────────────
                // Before insertion: row 1 = headings, row 2+ = data
                $sheet->insertNewRowBefore(1, 1);
                // After insertion:  row 1 = title, row 2 = headings, row 3+ = data
    
                $sheet->setCellValue('A1', $this->reportType . ' Report – Branch: ' . $this->branchName);
                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '166534']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // ── 2. Style header row (now row 2 after insertion) ─────────────────
                $headerRange = 'A2:' . $lastCol . '2';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '166534']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '14532D']]],
                ]);

                // ── 3. Alternating shading + borders on data rows (row 3 onwards) ───
                $totalRows = count($this->data) + 2; // title + header + data
                for ($row = 3; $row <= $totalRows; $row++) {
                    // Alternating fill: even-numbered rows get a light green tint
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('f0fdf4');
                    }
                    // Light border on every data row
                    $sheet->getStyle('A' . $row . ':' . $lastCol . $row)
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('d1fae5');
                }

                // ── 4. Freeze pane below header so it stays visible when scrolling ──
                $sheet->freezePane('A3');
            },
        ];
    }
}
