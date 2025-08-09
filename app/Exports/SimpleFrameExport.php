<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimpleFrameExport implements FromArray, WithHeadings
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
                'John Sales',
                '2024-01-15 10:00:00',
                '2024-01-15 10:00:00'
            ],
            [
                'FR000002',
                'Oakley',
                'Sport',
                300000,
                450000,
                15,
                'Cabang Utama',
                'Jane Sales',
                '2024-01-15 10:00:00',
                '2024-01-15 10:00:00'
            ],
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
            'Sales',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ];
    }
} 