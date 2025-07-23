<?php

namespace App\Exports;

use App\Models\Frame;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FrameExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Frame::select('kode_frame', 'nama_frame', 'jenis_frame', 'harga_beli_frame', 'harga_jual_frame', 'stok', 'branch_id', 'id_sales')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Frame',
            'Nama Frame',
            'Jenis Frame',
            'Harga Beli',
            'Harga Jual',
            'Stok',
            'Branch ID',
            'ID Sales',
        ];
    }
} 