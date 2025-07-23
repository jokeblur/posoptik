<?php

namespace App\Exports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LensaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        $user = auth()->user();
        return Lensa::with('branch')
            ->accessibleByUser($user)
            ->select('kode_lensa', 'merk_lensa', 'type', 'index', 'coating', 'harga_beli_lensa', 'harga_jual_lensa', 'stok', 'branch_id')
            ->get();
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
        ];
    }

    public function map($lensa): array
    {
        return [
            $lensa->kode_lensa,
            $lensa->merk_lensa,
            $lensa->type,
            $lensa->index,
            $lensa->coating,
            $lensa->harga_beli_lensa,
            $lensa->harga_jual_lensa,
            $lensa->stok,
            $lensa->branch?->name ?? '-',
        ];
    }
} 