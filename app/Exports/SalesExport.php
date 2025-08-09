<?php

namespace App\Exports;

use App\Models\Sales;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Sales::orderBy('id_sales', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Sales',
            'Nama Sales',
            'Alamat',
            'No HP',
            'Keterangan',
            'Tanggal Dibuat',
            'Tanggal Diupdate'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id_sales,
            $row->nama_sales,
            $row->alamat ?? '-',
            $row->nohp ?? '-',
            $row->keterangan ?? '-',
            $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '-',
            $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '-',
        ];
    }
} 