<?php

namespace App\Exports;

use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class LaporanBpjsFormattedExport implements FromArray, WithEvents, WithColumnWidths
{
    private Collection $transactions;
    private string $periodLabel;
    private array $tempImagePaths = [];

    public function __construct(Collection $transactions, string $periodLabel)
    {
        $this->transactions = $transactions->values();
        $this->periodLabel = $periodLabel;
    }

    public function array(): array
    {
        // Sheet content is built in AfterSheet for full layout control.
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 12,
            'C' => 24,
            'D' => 18,
            'E' => 8,
            'F' => 7,
            'G' => 7,
            'H' => 6,
            'I' => 7,
            'J' => 22,
            'K' => 12,
            'L' => 14,
            'M' => 16,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', 'BUKTI PENGAMBILAN KACAMATA BPJS KESEHATAN DI OPTIK MELATI TELUK KUANTAN');
                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', strtoupper($this->periodLabel));

                $sheet->mergeCells('A4:A5');
                $sheet->setCellValue('A4', 'NO');
                $sheet->mergeCells('B4:B5');
                $sheet->setCellValue('B4', 'TANGGAL');
                $sheet->mergeCells('C4:C5');
                $sheet->setCellValue('C4', 'NAMA');
                $sheet->mergeCells('D4:D5');
                $sheet->setCellValue('D4', 'NO KARTU PESERTA');
                $sheet->mergeCells('E4:H4');
                $sheet->setCellValue('E4', 'UKURAN KACAMATA');
                $sheet->setCellValue('E5', 'SPH');
                $sheet->setCellValue('F5', 'CYL');
                $sheet->setCellValue('G5', 'AXIS');
                $sheet->setCellValue('H5', 'Add');
                $sheet->mergeCells('I4:I5');
                $sheet->setCellValue('I4', 'KELAS');
                $sheet->mergeCells('J4:J5');
                $sheet->setCellValue('J4', 'FRAME / BINGKAI');
                $sheet->setCellValue('J5', 'LENSA');
                $sheet->mergeCells('K4:K5');
                $sheet->setCellValue('K4', 'ASAL RESEP');
                $sheet->mergeCells('L4:L5');
                $sheet->setCellValue('L4', 'TANDA TANGAN');
                $sheet->mergeCells('M4:M5');
                $sheet->setCellValue('M4', 'FOTO BUKTI BPJS');

                $currentRow = 6;
                $no = 1;

                foreach ($this->transactions as $transaction) {
                    $rowTop = $currentRow;
                    $rowBottom = $currentRow + 1;

                    $resep = $this->getLatestPrescription($transaction);
                    $kelas = $this->resolveKelas($transaction);
                    [$frameName, $lensaName] = $this->resolveFrameLensa($transaction);
                    $doctorName = $this->resolveDoctorName($transaction, $resep);

                    $sheet->mergeCells("A{$rowTop}:A{$rowBottom}");
                    $sheet->mergeCells("B{$rowTop}:B{$rowBottom}");
                    $sheet->mergeCells("C{$rowTop}:C{$rowBottom}");
                    $sheet->mergeCells("D{$rowTop}:D{$rowBottom}");
                    $sheet->mergeCells("I{$rowTop}:I{$rowBottom}");
                    $sheet->mergeCells("K{$rowTop}:K{$rowBottom}");
                    $sheet->mergeCells("L{$rowTop}:L{$rowBottom}");
                    $sheet->mergeCells("M{$rowTop}:M{$rowBottom}");

                    $sheet->setCellValue("A{$rowTop}", $no++);
                    $sheet->setCellValue("B{$rowTop}", optional($transaction->tanggal)->format('d/m/Y'));
                    $sheet->setCellValue("C{$rowTop}", $transaction->pasien->nama_pasien ?? '-');
                    $sheet->setCellValue("D{$rowTop}", $transaction->pasien->no_bpjs ?? '-');
                    $sheet->setCellValue("I{$rowTop}", $kelas);

                    $sheet->setCellValue("E{$rowTop}", 'R/' . ($resep->od_sph ?? ''));
                    $sheet->setCellValue("F{$rowTop}", $resep->od_cyl ?? '');
                    $sheet->setCellValue("G{$rowTop}", $resep->od_axis ?? '');
                    $sheet->setCellValue("H{$rowTop}", $resep->add ?? '');

                    $sheet->setCellValue("E{$rowBottom}", 'L/' . ($resep->os_sph ?? ''));
                    $sheet->setCellValue("F{$rowBottom}", $resep->os_cyl ?? '');
                    $sheet->setCellValue("G{$rowBottom}", $resep->os_axis ?? '');
                    $sheet->setCellValue("H{$rowBottom}", $resep->add ?? '');

                    $sheet->setCellValue("J{$rowTop}", $frameName);
                    $sheet->setCellValue("J{$rowBottom}", $lensaName);
                    $sheet->setCellValue("K{$rowTop}", $doctorName);

                    // Tanda tangan diambil dari signature_bpjs saat input penjualan.
                    $this->insertSignatureIfAvailable($sheet, $transaction, $rowTop);
                    // Foto bukti diambil dari photo_bpjs saat input penjualan.
                    $this->insertPhotoProofIfAvailable($sheet, $transaction, $rowTop);

                    $sheet->getRowDimension($rowTop)->setRowHeight(24);
                    $sheet->getRowDimension($rowBottom)->setRowHeight(24);

                    $sheet->getStyle("A{$rowTop}:M{$rowBottom}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                    $currentRow += 2;
                }

                if ($this->transactions->isEmpty()) {
                    $sheet->mergeCells('A6:M6');
                    $sheet->setCellValue('A6', 'Tidak ada data BPJS pada periode ini');
                    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A6:M6')->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    $currentRow = 6;
                }

                $sheet->getStyle('A1:M1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A2:M2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A4:M5')->applyFromArray([
                    'font' => ['bold' => true],
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

                if ($currentRow >= 6) {
                    $sheet->getStyle("A6:M{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A6:A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B6:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D6:D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F6:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("L6:L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("M6:M{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }

    private function getLatestPrescription(Penjualan $transaction)
    {
        if (!$transaction->pasien || !$transaction->pasien->relationLoaded('prescriptions')) {
            return null;
        }

        return $transaction->pasien->prescriptions
            ->sortByDesc('tanggal')
            ->first();
    }

    private function resolveKelas(Penjualan $transaction): string
    {
        $serviceType = $transaction->pasien_service_type ?? ($transaction->pasien->service_type ?? '');

        switch ($serviceType) {
            case 'BPJS I':
                return '1';
            case 'BPJS II':
                return '2';
            case 'BPJS III':
                return '3';
            default:
                return '-';
        }
    }

    private function resolveFrameLensa(Penjualan $transaction): array
    {
        $frameName = '-';
        $lensaName = '-';

        if (!$transaction->relationLoaded('details')) {
            return [$frameName, $lensaName];
        }

        foreach ($transaction->details as $detail) {
            if ($detail->itemable_type === 'App\\Models\\Frame' && $detail->itemable) {
                $frameName = $detail->itemable->merk_frame ?? 'Frame';
            }

            if ($detail->itemable_type === 'App\\Models\\Lensa' && $detail->itemable) {
                $lensaName = $detail->itemable->merk_lensa ?? 'Lensa';
            }
        }

        return [$frameName, $lensaName];
    }

    private function resolveDoctorName(Penjualan $transaction, $resep): string
    {
        if (!empty($transaction->dokter->nama_dokter)) {
            return $transaction->dokter->nama_dokter;
        }

        if (!empty($transaction->dokter_manual)) {
            return $transaction->dokter_manual;
        }

        if ($resep && !empty($resep->dokter->nama_dokter)) {
            return $resep->dokter->nama_dokter;
        }

        if ($resep && !empty($resep->dokter_manual)) {
            return $resep->dokter_manual;
        }

        return '-';
    }

    private function insertSignatureIfAvailable($sheet, Penjualan $transaction, int $targetRow): void
    {
        if (empty($transaction->signature_bpjs) || !is_string($transaction->signature_bpjs)) {
            $sheet->setCellValue("L{$targetRow}", '-');
            return;
        }

        if (strpos($transaction->signature_bpjs, 'data:image') !== 0) {
            $sheet->setCellValue("L{$targetRow}", '-');
            return;
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $transaction->signature_bpjs, $matches)) {
            $sheet->setCellValue("L{$targetRow}", '-');
            return;
        }

        $extension = strtolower($matches[1]);
        $base64Data = substr($transaction->signature_bpjs, strpos($transaction->signature_bpjs, ',') + 1);
        $binaryData = base64_decode($base64Data);

        if ($binaryData === false) {
            $sheet->setCellValue("L{$targetRow}", '-');
            return;
        }

        $tmpPath = storage_path('app/temp_signature_' . $transaction->id . '_' . uniqid() . '.' . $extension);
        @file_put_contents($tmpPath, $binaryData);

        if (!file_exists($tmpPath)) {
            $sheet->setCellValue("L{$targetRow}", '-');
            return;
        }

        $this->tempImagePaths[] = $tmpPath;

        $drawing = new Drawing();
        $drawing->setName('Tanda Tangan BPJS');
        $drawing->setDescription('Tanda Tangan BPJS');
        $drawing->setPath($tmpPath);
        $drawing->setCoordinates("L{$targetRow}");
        $drawing->setHeight(42);
        $drawing->setWorksheet($sheet);
    }

    private function insertPhotoProofIfAvailable($sheet, Penjualan $transaction, int $targetRow): void
    {
        if (empty($transaction->photo_bpjs) || !is_string($transaction->photo_bpjs)) {
            $sheet->setCellValue("M{$targetRow}", '-');
            return;
        }

        $relativePath = ltrim($transaction->photo_bpjs, '/');
        if (strpos($relativePath, 'storage/') === 0) {
            $relativePath = substr($relativePath, strlen('storage/'));
        }

        $fullPath = storage_path('app/public/' . $relativePath);
        if (!file_exists($fullPath)) {
            $sheet->setCellValue("M{$targetRow}", '-');
            return;
        }

        $drawing = new Drawing();
        $drawing->setName('Foto Bukti BPJS');
        $drawing->setDescription('Foto Bukti BPJS');
        $drawing->setPath($fullPath);
        $drawing->setCoordinates("M{$targetRow}");
        $drawing->setHeight(42);
        $drawing->setWorksheet($sheet);
    }

    public function __destruct()
    {
        foreach ($this->tempImagePaths as $tempImagePath) {
            if (is_string($tempImagePath) && file_exists($tempImagePath)) {
                @unlink($tempImagePath);
            }
        }
    }
}
