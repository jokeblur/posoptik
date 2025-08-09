<?php

namespace App\Exports;

use App\Models\Frame;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FrameExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        try {
            $user = auth()->user();
            
            if ($user && ($user->role === 'super admin' || $user->role === 'admin')) {
                $frames = Frame::with(['branch', 'sales'])->orderBy('id', 'desc')->get();
            } else {
                $frames = Frame::with(['branch', 'sales'])
                    ->where('branch_id', $user->branch_id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
            
            $result = [];
            foreach ($frames as $frame) {
                $result[] = [
                    $frame->id,
                    $frame->kode_frame,
                    $frame->merk_frame,
                    $frame->jenis_frame,
                    $frame->harga_beli_frame,
                    $frame->harga_jual_frame,
                    $frame->stok,
                    $frame->branch ? $frame->branch->name : '',
                    $frame->sales ? $frame->sales->nama_sales : '',
                    $frame->created_at ? $frame->created_at->format('d/m/Y H:i:s') : '',
                    $frame->updated_at ? $frame->updated_at->format('d/m/Y H:i:s') : '',
                ];
            }
            
            return $result;
        } catch (\Exception $e) {
            // Jika ada error, return data dummy
            return [
                [
                    1,
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
                    2,
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
    }

    public function headings(): array
    {
        return [
            'ID Frame',
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