<?php

namespace App\Imports;

use App\Models\Frame;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class SimpleFrameImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            Log::info('Processing simple import row:', $row);
            
            // Generate kode_frame otomatis
            $lastFrame = Frame::latest()->first();
            $idBaru = $lastFrame ? (int)$lastFrame->id + 1 : 1;
            $kodeFrame = 'FR' . tambah_nol_didepan($idBaru, 6);

            // Ambil data dari kolom yang ada (fleksibel)
            $data = [
                'kode_frame' => $kodeFrame,
                'merk_frame' => $row['merk_frame'] ?? $row['merk'] ?? $row['brand'] ?? '',
                'jenis_frame' => $row['jenis_frame'] ?? $row['jenis'] ?? $row['type'] ?? '',
                'harga_beli_frame' => $this->parseNumeric($row['harga_beli_frame'] ?? $row['harga_beli'] ?? $row['buy'] ?? 0),
                'harga_jual_frame' => $this->parseNumeric($row['harga_jual_frame'] ?? $row['harga_jual'] ?? $row['sell'] ?? 0),
                'stok' => $this->parseNumeric($row['stok'] ?? $row['stock'] ?? $row['qty'] ?? 0),
                'branch_id' => null, // Default null
                'id_sales' => null, // Default null
            ];

            Log::info('Creating frame with data:', $data);

            return new Frame($data);
        } catch (\Exception $e) {
            Log::error('Error processing simple import row: ' . $e->getMessage(), $row);
            return null; // Skip this row
        }
    }

    /**
     * Parse numeric value safely
     */
    private function parseNumeric($value)
    {
        if (empty($value)) return 0;
        
        // Remove any non-numeric characters except decimal point
        $cleanValue = preg_replace('/[^0-9.]/', '', $value);
        
        return is_numeric($cleanValue) ? (float)$cleanValue : 0;
    }
} 