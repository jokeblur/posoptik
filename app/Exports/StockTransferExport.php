<?php

namespace App\Exports;

use App\Models\StockTransfer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class StockTransferExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $transfers;

    public function __construct($transfers)
    {
        $this->transfers = $transfers;
    }

    public function collection()
    {
        return $this->transfers;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Transfer',
            'Dari Cabang',
            'Ke Cabang',
            'Diminta Oleh',
            'Disetujui Oleh',
            'Status',
            'Catatan',
            'Alasan Penolakan',
            'Tanggal Dibuat',
            'Tanggal Disetujui',
            'Tanggal Selesai',
            'Total Produk',
            'Total Nilai'
        ];
    }

    public function map($transfer): array
    {
        return [
            $transfer->id,
            $transfer->kode_transfer,
            $transfer->fromBranch->name,
            $transfer->toBranch->name,
            $transfer->requestedBy->name,
            $transfer->approvedBy ? $transfer->approvedBy->name : '-',
            $this->getStatusText($transfer->status),
            $transfer->notes ?: '-',
            $transfer->rejection_reason ?: '-',
            $transfer->created_at->format('d/m/Y H:i'),
            $transfer->approved_at ? $transfer->approved_at->format('d/m/Y H:i') : '-',
            $transfer->completed_at ? $transfer->completed_at->format('d/m/Y H:i') : '-',
            $transfer->getTotalQuantity(),
            'Rp ' . number_format($transfer->getTotalValue(), 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter('A1:N1');

        // Column alignment
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M:N')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Wrap text for long content
        $sheet->getStyle('H:I')->getAlignment()->setWrapText(true);

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 20,  // Kode Transfer
            'C' => 20,  // Dari Cabang
            'D' => 20,  // Ke Cabang
            'E' => 20,  // Diminta Oleh
            'F' => 20,  // Disetujui Oleh
            'G' => 15,  // Status
            'H' => 30,  // Catatan
            'I' => 30,  // Alasan Penolakan
            'J' => 20,  // Tanggal Dibuat
            'K' => 20,  // Tanggal Disetujui
            'L' => 20,  // Tanggal Selesai
            'M' => 15,  // Total Produk
            'N' => 20,  // Total Nilai
        ];
    }

    private function getStatusText($status)
    {
        $statusMap = [
            'Pending' => 'Menunggu Persetujuan',
            'Approved' => 'Disetujui',
            'Rejected' => 'Ditolak',
            'Completed' => 'Selesai',
            'Cancelled' => 'Dibatalkan'
        ];

        return $statusMap[$status] ?? $status;
    }
}
