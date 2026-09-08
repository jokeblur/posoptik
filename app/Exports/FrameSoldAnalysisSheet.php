<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FrameSoldAnalysisSheet implements FromArray, WithTitle, WithColumnWidths, WithStyles
{
    protected string $sheetTitle;
    protected string $periodLabel;
    protected array $rows;

    public function __construct(string $sheetTitle, string $periodLabel, array $rows)
    {
        $this->sheetTitle = $sheetTitle;
        $this->periodLabel = $periodLabel;
        $this->rows = $rows;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function array(): array
    {
        $data = [];

        $data[] = ['ANALISA FRAME TERJUAL - ' . strtoupper($this->sheetTitle)];
        $data[] = ['Periode: ' . $this->periodLabel];
        $data[] = [];
        $data[] = [
            'No',
            'Kode Frame',
            'Merk Frame',
            'Jenis Frame',
            'Sales',
            'Cabang',
            'Qty Terjual',
            'Jumlah Transaksi',
        ];

        $no = 1;
        $totalQty = 0;
        $totalTransaksi = 0;

        foreach ($this->rows as $row) {
            $data[] = [
                $no++,
                $row['kode_frame'],
                $row['merk_frame'],
                $row['jenis_frame'],
                $row['sales_name'],
                $row['cabang'],
                (int) $row['total_qty'],
                (int) $row['total_transaksi'],
            ];

            $totalQty += (int) $row['total_qty'];
            $totalTransaksi += (int) $row['total_transaksi'];
        }

        if (empty($this->rows)) {
            $data[] = ['', 'Tidak ada frame terjual pada periode ini', '', '', '', '', '', ''];
        }

        $data[] = [];
        $data[] = ['', '', '', '', '', 'TOTAL', $totalQty, $totalTransaksi];

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 28,
            'D' => 14,
            'E' => 20,
            'F' => 24,
            'G' => 12,
            'H' => 16,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:H1');

        $lastRow = $sheet->getHighestRow();

        $styles = [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
            4 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DDEBF7'],
                ],
            ],
            $lastRow => ['font' => ['bold' => true]],
        ];

        return $styles;
    }
}
