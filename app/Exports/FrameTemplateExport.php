<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FrameTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'FR000001',
                'Ray-Ban',
                'Sunglasses',
                500000,
                750000,
                10,
                'Cabang Utama',
                'John Sales'
            ],
            [
                'FR000002',
                'Oakley',
                'Sport',
                300000,
                450000,
                15,
                'Cabang Utama',
                'Jane Sales'
            ],
            [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Frame',
            'Merk Frame',
            'Jenis Frame',
            'Harga Beli',
            'Harga Jual',
            'Stok',
            'Cabang',
            'Sales'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
} 