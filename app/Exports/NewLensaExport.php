<?php

namespace App\Exports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class NewLensaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        try {
            Log::info('NewLensaExport: Starting export for user ' . $this->user->id . ' (role: ' . $this->user->role . ')');
            
            // Get lensas based on user permissions
            if ($this->user->isSuperAdmin() || $this->user->isAdmin()) {
                $lensas = Lensa::with(['branch', 'sales'])->orderBy('id', 'desc')->get();
                Log::info('NewLensaExport: Getting all lensas (admin/superadmin)');
            } else {
                $lensas = Lensa::with(['branch', 'sales'])
                    ->where('branch_id', $this->user->branch_id ?? 0)
                    ->orderBy('id', 'desc')
                    ->get();
                Log::info('NewLensaExport: Getting lensas for branch ' . ($this->user->branch_id ?? 0));
            }
            
            Log::info('NewLensaExport: Found ' . $lensas->count() . ' lensas to export');
            return $lensas;
        } catch (\Exception $e) {
            Log::error('NewLensaExport error: ' . $e->getMessage());
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
            'Catatan (ADD)',
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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Kode Lensa
            'B' => 25, // Merk Lensa
            'C' => 15, // Type
            'D' => 10, // Index
            'E' => 15, // Coating
            'F' => 15, // Harga Beli
            'G' => 15, // Harga Jual
            'H' => 10, // Stok
            'I' => 20, // Cabang
            'J' => 15, // Sales
            'K' => 15, // Tipe Stok
            'L' => 25, // Catatan (ADD)
            'M' => 10, // Cly
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Set header style
                $event->sheet->getStyle('A1:M1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E86AB'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Set data style
                $event->sheet->getStyle('A2:M' . ($event->sheet->getHighestRow()))
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                // Freeze first row
                $event->sheet->freezePane('A2');
            },
        ];
    }
}
