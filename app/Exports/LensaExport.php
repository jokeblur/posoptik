<?php

namespace App\Exports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LensaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Lensa::select('kode_lensa', 'merk_lensa', 'type', 'index', 'coating', 'harga_beli_lensa', 'harga_jual_lensa', 'stok', 'branch_id')->get();
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
            'Branch ID',
        ];
    }
} 