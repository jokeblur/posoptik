<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FrameSoldAnalysisExport implements WithMultipleSheets
{
    protected string $periodLabel;
    protected array $bpjsRows;
    protected array $umumMelati1Rows;
    protected array $umumMelati2Rows;

    public function __construct(string $periodLabel, array $bpjsRows, array $umumMelati1Rows, array $umumMelati2Rows)
    {
        $this->periodLabel = $periodLabel;
        $this->bpjsRows = $bpjsRows;
        $this->umumMelati1Rows = $umumMelati1Rows;
        $this->umumMelati2Rows = $umumMelati2Rows;
    }

    public function sheets(): array
    {
        return [
            new FrameSoldAnalysisSheet('Frame BPJS', $this->periodLabel, $this->bpjsRows),
            new FrameSoldAnalysisSheet('Umum Optik Melati 1', $this->periodLabel, $this->umumMelati1Rows),
            new FrameSoldAnalysisSheet('Umum Optik Melati 2', $this->periodLabel, $this->umumMelati2Rows),
        ];
    }
}
