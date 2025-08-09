<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimpleLensaExport implements FromArray, WithHeadings
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
                '2024-01-15 10:00:00',
                '2024-01-15 10:00:00'
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
                '2024-01-15 10:00:00',
                '2024-01-15 10:00:00'
            ],
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
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ];
    }
} 