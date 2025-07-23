<?php

namespace App\Exports;

use App\Models\Frame;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FrameExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        $user = auth()->user();
        return Frame::with(['branch', 'sales'])
            ->accessibleByUser($user)
            ->select('kode_frame', 'merk_frame', 'jenis_frame', 'harga_beli_frame', 'harga_jual_frame', 'stok', 'branch_id', 'id_sales')
            ->get();
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
            'Cabang',
            'Sales',
        ];
    }

    public function map($frame): array
    {
        return [
            $frame->kode_frame,
            $frame->merk_frame,
            $frame->jenis_frame,
            $frame->harga_beli_frame,
            $frame->harga_jual_frame,
            $frame->stok,
            $frame->branch?->name ?? '-',
            $frame->sales?->nama_sales ?? '-',
        ];
    }
} 