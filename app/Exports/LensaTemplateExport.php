<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LensaTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'L00001',
                'Essilor',
                'Single Vision',
                '1.56',
                'Anti-Reflective',
                200000,
                300000,
                20,
                'Cabang Utama',
                'John Sales',
                'Ready Stock',
                'Lensa premium kualitas tinggi'
            ],
            [
                'L00002',
                'Hoya',
                'Progressive',
                '1.67',
                'Blue Cut',
                400000,
                600000,
                15,
                'Cabang Utama',
                'Jane Sales',
                'Custom Order',
                'Lensa progresif untuk presbyopia'
            ],
            array_fill(0, 12, '') // Empty row for more data
        ];
    }

    public function headings(): array
    {
        return [
            'Kode Lensa',
            'Merk Lensa',
            'Type',
            'Index', 
            'Coating',
            'Harga Beli',
            'Harga Jual',
            'Stok',
            'Cabang',
            'Sales',
            'Tipe Stok',
            'Catatan'
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