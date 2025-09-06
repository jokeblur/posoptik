<?php

namespace App\Exports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;

class LensaCsvExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                Log::warning('LensaCsvExport: No authenticated user found');
                return collect([]);
            }
            
            // Get lensas based on user permissions
            if (method_exists($user, 'isSuperAdmin') && method_exists($user, 'isAdmin')) {
                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    $lensas = Lensa::with(['branch', 'sales'])->orderBy('id', 'desc')->get();
                } else {
                    $lensas = Lensa::with(['branch', 'sales'])->where('branch_id', $user->branch_id ?? 0)->orderBy('id', 'desc')->get();
                }
            } else {
                $lensas = Lensa::with(['branch', 'sales'])->orderBy('id', 'desc')->get();
            }
            
            Log::info('LensaCsvExport: Successfully exported ' . $lensas->count() . ' records');
            return $lensas;
        } catch (\Exception $e) {
            Log::error('LensaCsvExport error: ' . $e->getMessage());
            return collect([]);
        }
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
            'Catatan',
            'Cly'
        ];
    }

    public function map($lensa): array
    {
        return [
            $lensa->kode_lensa ?? '',
            $lensa->merk_lensa ?? '',
            $lensa->type ?? '',
            $lensa->index ?? '',
            $lensa->coating ?? '',
            $lensa->harga_beli_lensa ?? 0,
            $lensa->harga_jual_lensa ?? 0,
            $lensa->stok ?? 0,
            $lensa->branch ? $lensa->branch->name : '',
            $lensa->sales ? $lensa->sales->nama_sales : '',
            $lensa->is_custom_order ? 'Custom Order' : 'Ready Stock',
            $lensa->add ?? '',
            $lensa->cly ?? '',
        ];
    }
}
