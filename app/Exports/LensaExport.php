<?php

namespace App\Exports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class LensaExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        try {
            $user = auth()->user();
            
            // Simple auth check
            if (!$user) {
                Log::warning('LensaExport: No authenticated user found');
                return collect([]);
            }
            
            // Get lensas based on user permissions - simplified
            if (method_exists($user, 'isSuperAdmin') && method_exists($user, 'isAdmin')) {
                if (($user->isSuperAdmin ?? false) || ($user->isAdmin ?? false)) {
                    $lensas = Lensa::orderBy('id', 'desc')->get();
                } else {
                    $lensas = Lensa::where('branch_id', $user->branch_id ?? 0)->orderBy('id', 'desc')->get();
                }
            } else {
                // Fallback: get all lensas
                $lensas = Lensa::orderBy('id', 'desc')->get();
            }
            
            // Transform data to simple array format - format import
            $result = $lensas->map(function($lensa) {
                return [
                    (string) ($lensa->kode_lensa ?? ''),
                    (string) ($lensa->merk_lensa ?? ''),
                    (string) ($lensa->type ?? ''),
                    (string) ($lensa->index ?? ''),
                    (string) ($lensa->coating ?? ''),
                    (string) ($lensa->harga_beli_lensa ?? '0'),
                    (string) ($lensa->harga_jual_lensa ?? '0'),
                    (string) ($lensa->stok ?? '0'),
                    (string) ($lensa->branch ? $lensa->branch->name : ''),
                    (string) ($lensa->sales ? $lensa->sales->nama_sales : ''),
                    (string) ($lensa->is_custom_order ? 'Custom Order' : 'Ready Stock'),
                    (string) ($lensa->add ?? ''),
                    (string) ($lensa->cly ?? ''),
                ];
            });
            
            Log::info('LensaExport: Successfully exported ' . $result->count() . ' records');
            return $result;
        } catch (\Exception $e) {
            Log::error('LensaExport error: ' . $e->getMessage());
            Log::error('LensaExport trace: ' . $e->getTraceAsString());
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

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

} 