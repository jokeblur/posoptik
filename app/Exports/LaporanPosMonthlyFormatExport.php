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

class LaporanPosMonthlyFormatExport implements FromArray, WithEvents, WithColumnWidths
{
    private Collection $transactions;
    private string $periodLabel;
    private string $branchLabel;

    public function __construct(Collection $transactions, string $periodLabel, string $branchLabel)
    {
        $this->transactions = $transactions->values();
        $this->periodLabel = $periodLabel;
        $this->branchLabel = $branchLabel;
    }

    public function array(): array
    {
        // Layout penuh dibangun di event AfterSheet.
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 26,
            'D' => 24,
            'E' => 16,
            'F' => 20,
            'G' => 20,
            'H' => 4,
            'I' => 9,
            'J' => 8,
            'K' => 7,
            'L' => 8,
            'M' => 8,
            'N' => 20,
            'O' => 14,
            'P' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:P1');
                $sheet->setCellValue('A1', 'DATA PASIEN ' . $this->branchLabel . ' ' . $this->periodLabel);

                $sheet->mergeCells('A3:A4');
                $sheet->setCellValue('A3', 'NO');
                $sheet->mergeCells('B3:B4');
                $sheet->setCellValue('B3', 'TANGGAL');
                $sheet->mergeCells('C3:C4');
                $sheet->setCellValue('C3', 'NAMA');
                $sheet->mergeCells('D3:D4');
                $sheet->setCellValue('D3', 'ALAMAT');
                $sheet->mergeCells('E3:E4');
                $sheet->setCellValue('E3', 'NO HP');
                $sheet->mergeCells('F3:F4');
                $sheet->setCellValue('F3', 'FRAME');
                $sheet->mergeCells('G3:G4');
                $sheet->setCellValue('G3', 'LENSA');
                $sheet->mergeCells('H3:M3');
                $sheet->setCellValue('H3', 'UKURAN / RESEP');
                $sheet->setCellValue('H4', '');
                $sheet->setCellValue('I4', 'SPH');
                $sheet->setCellValue('J4', 'CYL');
                $sheet->setCellValue('K4', 'AXIS');
                $sheet->setCellValue('L4', 'ADD');
                $sheet->setCellValue('M4', 'PD');
                $sheet->mergeCells('N3:N4');
                $sheet->setCellValue('N3', 'ASAL RESEP');
                $sheet->mergeCells('O3:O4');
                $sheet->setCellValue('O3', 'BPJS');
                $sheet->mergeCells('P3:P4');
                $sheet->setCellValue('P3', 'UMUM');

                $currentRow = 5;
                $number = 1;

                foreach ($this->transactions as $transaction) {
                    $rowTop = $currentRow;
                    $rowBottom = $currentRow + 1;

                    [$frameName, $lensaName] = $this->resolveFrameLensa($transaction);
                    $resep = $this->getLatestPrescription($transaction);
                    [$amountBpjs, $amountUmum] = $this->resolveAmounts($transaction);
                    $doctorName = $this->resolveDoctorName($transaction, $resep);

                    $sheet->mergeCells("A{$rowTop}:A{$rowBottom}");
                    $sheet->mergeCells("B{$rowTop}:B{$rowBottom}");
                    $sheet->mergeCells("C{$rowTop}:C{$rowBottom}");
                    $sheet->mergeCells("D{$rowTop}:D{$rowBottom}");
                    $sheet->mergeCells("E{$rowTop}:E{$rowBottom}");
                    $sheet->mergeCells("F{$rowTop}:F{$rowBottom}");
                    $sheet->mergeCells("G{$rowTop}:G{$rowBottom}");
                    $sheet->mergeCells("N{$rowTop}:N{$rowBottom}");
                    $sheet->mergeCells("O{$rowTop}:O{$rowBottom}");
                    $sheet->mergeCells("P{$rowTop}:P{$rowBottom}");

                    $sheet->setCellValue("A{$rowTop}", $number++);
                    $sheet->setCellValue("B{$rowTop}", $this->formatTanggal($transaction));
                    $sheet->setCellValue("C{$rowTop}", $transaction->pasien->nama_pasien ?? $transaction->nama_pasien_manual ?? '-');
                    $sheet->setCellValue("D{$rowTop}", $transaction->pasien->alamat ?? '-');
                    $sheet->setCellValue("E{$rowTop}", $transaction->pasien->nohp ?? '-');
                    $sheet->setCellValue("F{$rowTop}", $frameName);
                    $sheet->setCellValue("G{$rowTop}", $lensaName);

                    $sheet->setCellValue("H{$rowTop}", 'R');
                    $sheet->setCellValue("I{$rowTop}", $resep->od_sph ?? '-');
                    $sheet->setCellValue("J{$rowTop}", $resep->od_cyl ?? '-');
                    $sheet->setCellValue("K{$rowTop}", $resep->od_axis ?? '-');
                    $sheet->setCellValue("L{$rowTop}", $resep->add_kanan ?? $resep->add ?? '-');
                    $sheet->setCellValue("M{$rowTop}", $resep->pd_kanan ?? $resep->pd ?? '-');

                    $sheet->setCellValue("H{$rowBottom}", 'L');
                    $sheet->setCellValue("I{$rowBottom}", $resep->os_sph ?? '-');
                    $sheet->setCellValue("J{$rowBottom}", $resep->os_cyl ?? '-');
                    $sheet->setCellValue("K{$rowBottom}", $resep->os_axis ?? '-');
                    $sheet->setCellValue("L{$rowBottom}", $resep->add_kiri ?? $resep->add ?? '-');
                    $sheet->setCellValue("M{$rowBottom}", $resep->pd_kiri ?? $resep->pd ?? '-');

                    $sheet->setCellValue("N{$rowTop}", $doctorName);
                    $sheet->setCellValue("O{$rowTop}", $amountBpjs > 0 ? $amountBpjs : '-');
                    $sheet->setCellValue("P{$rowTop}", $amountUmum > 0 ? $amountUmum : '-');

                    $sheet->getStyle("A{$rowTop}:P{$rowBottom}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                    $sheet->getRowDimension($rowTop)->setRowHeight(21);
                    $sheet->getRowDimension($rowBottom)->setRowHeight(21);

                    $currentRow += 2;
                }

                if ($this->transactions->isEmpty()) {
                    $sheet->mergeCells('A5:P5');
                    $sheet->setCellValue('A5', 'Tidak ada data transaksi pada periode ini');
                    $sheet->getStyle('A5:P5')->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $currentRow = 5;
                }

                $sheet->getStyle('A1:P1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A3:P4')->applyFromArray([
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

                if ($currentRow >= 5) {
                    $sheet->getStyle("A5:P{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A5:B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H5:M{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("O5:P{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
                }
            },
        ];
    }

    private function formatTanggal(Penjualan $transaction): string
    {
        $date = $transaction->tanggal ?: $transaction->created_at;
        if (!$date) {
            return '-';
        }

        $dateCarbon = Carbon::parse($date)->locale('id');
        return $dateCarbon->format('j') . '. ' . $dateCarbon->translatedFormat('F');
    }

    private function getLatestPrescription(Penjualan $transaction)
    {
        if (!$transaction->pasien || !$transaction->pasien->relationLoaded('prescriptions')) {
            return null;
        }

        return $transaction->pasien->prescriptions
            ->sortByDesc(function ($item) {
                return $item->tanggal ?: $item->created_at;
            })
            ->first();
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
                $frameName = $detail->itemable->merk_frame ?? $frameName;
            }

            if ($detail->itemable_type === 'App\\Models\\Lensa' && $detail->itemable) {
                $lensaName = $detail->itemable->merk_lensa ?? $lensaName;
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

    private function resolveAmounts(Penjualan $transaction): array
    {
        $serviceType = strtoupper((string) ($transaction->pasien_service_type ?? ($transaction->pasien->service_type ?? '')));
        $isBpjs = in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III'], true);

        if ($isBpjs) {
            $bpjsDefault = (float) ($transaction->bpjs_default_price ?? 0);
            $bpjsAdditional = max(0, (float) ($transaction->total_additional_cost ?? 0));
            return [$bpjsDefault + $bpjsAdditional, 0.0];
        }

        return [0.0, (float) ($transaction->total ?? 0)];
    }
}
