<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            [
                'John Doe',
                'Jl. Contoh No. 123, Jakarta',
                '081234567890',
                'Sales Senior'
            ],
            [
                'Jane Smith',
                'Jl. Sample No. 456, Bandung',
                '081234567891',
                'Sales Junior'
            ],
            [
                'Bob Johnson',
                'Jl. Template No. 789, Surabaya',
                '081234567892',
                'Sales Manager'
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'nama_sales',
            'alamat',
            'nohp',
            'keterangan'
        ];
    }
} 