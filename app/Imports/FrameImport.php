<?php

namespace App\Imports;

use App\Models\Frame;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class FrameImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
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
            // Log row data untuk debugging
            Log::info('Processing row:', $row);
            
            // Normalisasi header untuk membaca berbagai format
            $normalizedRow = $this->normalizeHeaders($row);
            
            // Generate kode_frame otomatis jika tidak ada
            $kodeFrame = $normalizedRow['kode_frame'] ?? null;
            if (empty($kodeFrame)) {
                $lastFrame = Frame::latest()->first();
                $idBaru = $lastFrame ? (int)$lastFrame->id + 1 : 1;
                $kodeFrame = 'FR' . tambah_nol_didepan($idBaru, 6);
            }

            // Cari branch_id dari nama cabang jika ada
            $branchId = null;
            if (!empty($normalizedRow['cabang'])) {
                $branch = \App\Models\Branch::where('name', $normalizedRow['cabang'])->first();
                if ($branch) {
                    $branchId = $branch->id;
                }
            }

            // Cari sales_id dari nama sales jika ada
            $salesId = null;
            if (!empty($normalizedRow['sales'])) {
                $sales = \App\Models\Sales::where('nama_sales', $normalizedRow['sales'])->first();
                if ($sales) {
                    $salesId = $sales->id_sales;
                }
            }

            // Validasi data sebelum create
            $data = [
                'kode_frame' => $kodeFrame,
                'merk_frame' => $normalizedRow['merk_frame'] ?? '',
                'jenis_frame' => $normalizedRow['jenis_frame'] ?? '',
                    'id_sales' => $salesId,
                'harga_beli_frame' => $this->parseNumeric($normalizedRow['harga_beli_frame']),
                'harga_jual_frame' => $this->parseNumeric($normalizedRow['harga_jual_frame']),
                'stok' => $this->parseNumeric($normalizedRow['stok']),
                    'branch_id' => $branchId,
            ];

            Log::info('Creating frame with data:', $data);

            return new Frame($data);
        } catch (\Exception $e) {
            Log::error('Error processing row: ' . $e->getMessage(), $row);
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

    /**
     * Normalisasi header untuk membaca berbagai format
     */
    private function normalizeHeaders($row)
    {
        $normalized = [];
        
        foreach ($row as $key => $value) {
            $cleanKey = strtolower(trim($key));
            
            // Mapping berbagai kemungkinan header
            if (strpos($cleanKey, 'kode') !== false || strpos($cleanKey, 'code') !== false) {
                $normalized['kode_frame'] = $value;
            }
            elseif (strpos($cleanKey, 'merk') !== false || strpos($cleanKey, 'brand') !== false) {
                $normalized['merk_frame'] = $value;
            }
            elseif (strpos($cleanKey, 'jenis') !== false || strpos($cleanKey, 'type') !== false) {
                $normalized['jenis_frame'] = $value;
            }
            elseif (strpos($cleanKey, 'harga beli') !== false || strpos($cleanKey, 'harga_beli') !== false || strpos($cleanKey, 'buy') !== false) {
                $normalized['harga_beli_frame'] = $value;
            }
            elseif (strpos($cleanKey, 'harga jual') !== false || strpos($cleanKey, 'harga_jual') !== false || strpos($cleanKey, 'sell') !== false) {
                $normalized['harga_jual_frame'] = $value;
            }
            elseif (strpos($cleanKey, 'stok') !== false || strpos($cleanKey, 'stock') !== false || strpos($cleanKey, 'qty') !== false) {
                $normalized['stok'] = $value;
            }
            elseif (strpos($cleanKey, 'cabang') !== false || strpos($cleanKey, 'branch') !== false) {
                $normalized['cabang'] = $value;
            }
            elseif (strpos($cleanKey, 'sales') !== false) {
                $normalized['sales'] = $value;
            }
            else {
                // Jika tidak cocok dengan mapping, gunakan key asli
                $normalized[$cleanKey] = $value;
            }
        }
        
        Log::info('Normalized row:', $normalized);
        return $normalized;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'merk_frame' => 'nullable|string|max:255',
            'jenis_frame' => 'nullable|string|max:255',
            'harga_beli_frame' => 'nullable|numeric|min:0',
            'harga_jual_frame' => 'nullable|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
            'cabang' => 'nullable|string|max:255',
            'sales' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'merk_frame.string' => 'Merk frame harus berupa teks',
            'merk_frame.max' => 'Merk frame maksimal 255 karakter',
            'jenis_frame.string' => 'Jenis frame harus berupa teks',
            'jenis_frame.max' => 'Jenis frame maksimal 255 karakter',
            'harga_beli_frame.numeric' => 'Harga beli frame harus berupa angka',
            'harga_beli_frame.min' => 'Harga beli frame minimal 0',
            'harga_jual_frame.numeric' => 'Harga jual frame harus berupa angka',
            'harga_jual_frame.min' => 'Harga jual frame minimal 0',
            'stok.integer' => 'Stok harus berupa angka bulat',
            'stok.min' => 'Stok minimal 0',
            'cabang.string' => 'Cabang harus berupa teks',
            'cabang.max' => 'Cabang maksimal 255 karakter',
            'sales.string' => 'Sales harus berupa teks',
            'sales.max' => 'Sales maksimal 255 karakter',
        ];
    }
} 