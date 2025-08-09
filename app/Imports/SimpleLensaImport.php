<?php

namespace App\Imports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class SimpleLensaImport implements ToModel, WithHeadingRow, SkipsOnError
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
            Log::info('Processing simple lensa import row:', $row);
            
            // Generate kode_lensa otomatis
            $lastLensa = Lensa::latest()->first();
            $idBaru = $lastLensa ? (int)$lastLensa->id + 1 : 1;
            $kodeLensa = 'L' . tambah_nol_didepan($idBaru, 5);

            // Ambil data dari kolom yang ada (fleksibel)
            $data = [
                'kode_lensa' => $kodeLensa,
                'merk_lensa' => $row['merk_lensa'] ?? $row['merk'] ?? $row['brand'] ?? '',
                'type' => $row['type'] ?? $row['tipe'] ?? '',
                'index' => $row['index'] ?? '',
                'coating' => $row['coating'] ?? '',
                'harga_beli_lensa' => $this->parseNumeric($row['harga_beli_lensa'] ?? $row['harga_beli'] ?? $row['buy'] ?? 0),
                'harga_jual_lensa' => $this->parseNumeric($row['harga_jual_lensa'] ?? $row['harga_jual'] ?? $row['sell'] ?? 0),
                'stok' => $this->parseNumeric($row['stok'] ?? $row['stock'] ?? $row['qty'] ?? 0),
                'branch_id' => null, // Default null
            ];

            // Fallback: If stock is not found, try to find it in any column with stock-related names
            if ($data['stok'] == 0) {
                foreach ($row as $key => $value) {
                    if (stripos($key, 'stok') !== false || stripos($key, 'stock') !== false || stripos($key, 'qty') !== false) {
                        $parsedStock = $this->parseNumeric($value);
                        if ($parsedStock > 0) {
                            $data['stok'] = $parsedStock;
                            Log::debug("Stock value found via fallback: '$value' from header '$key' -> " . $data['stok']);
                            break;
                        }
                    }
                }
            }

            // Debug stock value specifically
            Log::debug('SimpleLensaImport stock value processing:', [
                'original_stok' => $row['stok'] ?? $row['stock'] ?? $row['qty'] ?? 'NOT_FOUND',
                'parsed_stok' => $data['stok'],
                'row_data' => $row
            ]);

            // Additional check for stock value conversion
            $originalStock = $row['stok'] ?? $row['stock'] ?? $row['qty'] ?? null;
            if ($originalStock !== null && is_string($originalStock) && is_numeric($originalStock)) {
                $data['stok'] = (int)(float)$originalStock;
                Log::debug('SimpleLensaImport stock value converted from string to integer: ' . $data['stok']);
            }

            Log::info('Creating lensa with data:', $data);

            return new Lensa($data);
        } catch (\Exception $e) {
            Log::error('Error processing simple lensa import row: ' . $e->getMessage(), $row);
            return null; // Skip this row
        }
    }

    /**
     * Parse numeric value safely
     */
    private function parseNumeric($value)
    {
        // Log the original value for debugging
        Log::debug('SimpleLensaImport parseNumeric: original value: "' . $value . '" (type: ' . gettype($value) . ')');
        
        if (empty($value) && $value !== '0' && $value !== 0) {
            Log::debug('SimpleLensaImport parseNumeric: empty value, returning 0');
            return 0;
        }
        
        // Convert to string and trim
        $value = trim((string)$value);
        Log::debug('SimpleLensaImport parseNumeric: trimmed value: "' . $value . '"');
        
        // Handle special cases for stock
        if (strtolower($value) === 'stok' || strtolower($value) === 'stock') {
            Log::debug('SimpleLensaImport parseNumeric: header detected, returning 0');
            return 0;
        }
        
        // If it's already numeric, return as is
        if (is_numeric($value)) {
            $result = (int)(float)$value; // Convert to integer for stock
            Log::debug('SimpleLensaImport parseNumeric: numeric value, returning: ' . $result);
            return $result;
        }
        
        // Remove any non-numeric characters except decimal point and minus sign
        $cleanValue = preg_replace('/[^0-9.-]/', '', $value);
        Log::debug('SimpleLensaImport parseNumeric: cleaned value: "' . $cleanValue . '"');
        
        // If result is numeric, return it
        if (is_numeric($cleanValue) && $cleanValue !== '') {
            $result = (int)(float)$cleanValue; // Convert to integer for stock
            Log::debug('SimpleLensaImport parseNumeric: cleaned is numeric, returning: ' . $result);
            return $result;
        }
        
        // If still not numeric, return 0
        Log::debug('SimpleLensaImport parseNumeric: not numeric, returning 0');
        return 0;
    }
} 