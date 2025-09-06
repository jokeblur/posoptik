<?php

namespace App\Imports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class LensaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
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
            Log::info('Processing lensa row:', $row);
            
            // Normalisasi header untuk membaca berbagai format
            $normalizedRow = $this->normalizeHeaders($row);
            
            // Generate kode_lensa otomatis jika tidak ada
            $kodeLensa = $normalizedRow['kode_lensa'] ?? null;
            if (empty($kodeLensa)) {
                $lastLensa = Lensa::latest()->first();
                $idBaru = $lastLensa ? (int)$lastLensa->id + 1 : 1;
                $kodeLensa = 'L' . tambah_nol_didepan($idBaru, 5);
            }

            // Cari branch_id dari nama cabang jika ada
            $branchId = null;
            if (!empty($normalizedRow['cabang'])) {
                $branch = \App\Models\Branch::where('name', $normalizedRow['cabang'])->first();
                if ($branch) {
                    $branchId = $branch->id;
                }
            }
            
            // Jika branch_id masih null, gunakan branch user yang login (untuk kasir)
            if ($branchId === null) {
                $user = auth()->user();
                if ($user && $user->branch_id) {
                    $branchId = $user->branch_id;
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

            // CATATAN: Sistem mengizinkan duplikasi merk_lensa dan kode_lensa
            // Tidak ada pengecekan unique constraint untuk data duplikat
            $data = [
                'kode_lensa' => $kodeLensa,
                'merk_lensa' => $normalizedRow['merk_lensa'] ?? '',
                'type' => $normalizedRow['type'] ?? '',
                'index' => $this->parseIndex($normalizedRow['index']),
                'coating' => $normalizedRow['coating'] ?? '',
                'harga_beli_lensa' => $this->parseNumeric($normalizedRow['harga_beli_lensa']),
                'harga_jual_lensa' => $this->parseNumeric($normalizedRow['harga_jual_lensa']),
                'stok' => $this->parseNumeric($normalizedRow['stok']),
                'is_custom_order' => $this->parseBoolean($normalizedRow['is_custom_order'] ?? ''),
                'add' => $normalizedRow['add'] ?? null,
                'cly' => $normalizedRow['cly'] ?? null,
                'branch_id' => $branchId,
                'sales_id' => $salesId,
            ];

            // Fallback: If stock is not found in normalized row, try to find it in original row
            if (!isset($normalizedRow['stok']) || empty($normalizedRow['stok'])) {
                foreach ($row as $key => $value) {
                    if (stripos($key, 'stok') !== false || stripos($key, 'stock') !== false) {
                        $data['stok'] = $this->parseNumeric($value);
                        Log::debug("Stock value found via fallback: '$value' from header '$key' -> " . $data['stok']);
                        break;
                    }
                }
            }

            // Special fix for user's Excel format - check for exact "Stok" header
            if ($data['stok'] == 0) {
                foreach ($row as $key => $value) {
                    if ($key === 'Stok' || $key === 'stok' || $key === 'STOK') {
                        $data['stok'] = $this->parseNumeric($value);
                        Log::debug("Stock value found via exact match: '$value' from header '$key' -> " . $data['stok']);
                        break;
                    }
                }
            }

            // Debug stock value specifically
            Log::debug('Stock value processing:', [
                'original_stok' => $normalizedRow['stok'] ?? 'NOT_FOUND',
                'parsed_stok' => $data['stok'],
                'normalized_row' => $normalizedRow
            ]);

            // Additional check for stock value
            if (isset($normalizedRow['stok'])) {
                $stockValue = $normalizedRow['stok'];
                Log::debug('Stock value found in normalized row:', [
                    'stock_value' => $stockValue,
                    'stock_type' => gettype($stockValue),
                    'is_numeric' => is_numeric($stockValue),
                    'is_string' => is_string($stockValue),
                    'is_integer' => is_integer($stockValue),
                    'is_float' => is_float($stockValue)
                ]);
                
                // If stock value is a string but contains a number, convert it
                if (is_string($stockValue) && is_numeric($stockValue)) {
                    $data['stok'] = (int)(float)$stockValue;
                    Log::debug('Stock value converted from string to integer: ' . $data['stok']);
                }
            } else {
                Log::warning('Stock value NOT found in normalized row!');
                Log::debug('Available keys in normalized row:', array_keys($normalizedRow));
            }

            Log::info('Creating lensa with data (duplikasi diizinkan):', $data);

            // Validasi data wajib
            if (empty($data['merk_lensa'])) {
                Log::warning('Skipping row: merk_lensa is empty', $row);
                return null;
            }

            // Create dan save ke database
            $lensa = Lensa::create($data);
            Log::info('Lensa successfully created with ID: ' . $lensa->id);
            
            return $lensa;
        } catch (\Exception $e) {
            Log::error('Error processing lensa row: ' . $e->getMessage(), [
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            return null; // Skip this row
        }
    }

    /**
     * Parse numeric value safely
     */
    private function parseNumeric($value)
    {
        // Log the original value for debugging
        Log::debug('parseNumeric: original value: "' . $value . '" (type: ' . gettype($value) . ')');
        
        if (empty($value) && $value !== '0' && $value !== 0) {
            Log::debug('parseNumeric: empty value, returning 0');
            return 0;
        }
        
        // Convert to string and trim
        $value = trim((string)$value);
        Log::debug('parseNumeric: trimmed value: "' . $value . '"');
        
        // Handle special cases for stock
        if (strtolower($value) === 'stok' || strtolower($value) === 'stock') {
            Log::debug('parseNumeric: header detected, returning 0');
            return 0;
        }
        
        // If it's already numeric, return as is
        if (is_numeric($value)) {
            $result = (int)(float)$value; // Convert to integer for stock
            Log::debug('parseNumeric: numeric value, returning: ' . $result);
            return $result;
        }
        
        // Remove any non-numeric characters except decimal point and minus sign
        $cleanValue = preg_replace('/[^0-9.-]/', '', $value);
        Log::debug('parseNumeric: cleaned value: "' . $cleanValue . '"');
        
        // If result is numeric, return it
        if (is_numeric($cleanValue) && $cleanValue !== '') {
            $result = (int)(float)$cleanValue; // Convert to integer for stock
            Log::debug('parseNumeric: cleaned is numeric, returning: ' . $result);
            return $result;
        }
        
        // If still not numeric, return 0
        Log::debug('parseNumeric: not numeric, returning 0');
        return 0;
    }

    /**
     * Parse boolean value safely
     */
    private function parseBoolean($value)
    {
        if (empty($value)) return false;
        
        $cleanValue = strtolower(trim($value));
        
        Log::debug("parseBoolean: processing value '$value' -> '$cleanValue'");
        
        // Check for Ready Stock first (should return false)
        if (strpos($cleanValue, 'ready') !== false || strpos($cleanValue, 'stock') !== false) {
            Log::debug("parseBoolean: detected Ready Stock, returning false");
            return false;
        }
        
        // Check for Custom Order (should return true)
        if (strpos($cleanValue, 'custom') !== false || strpos($cleanValue, 'order') !== false) {
            Log::debug("parseBoolean: detected Custom Order, returning true");
            return true;
        }
        
        // Handle other boolean representations
        $customOrderValues = ['1', 'true', 'yes', 'ya'];
        if (in_array($cleanValue, $customOrderValues)) {
            Log::debug("parseBoolean: detected custom order value, returning true");
            return true;
        }
        
        Log::debug("parseBoolean: default case, returning false");
        return false;
    }

    /**
     * Parse index value (can be string or numeric)
     */
    private function parseIndex($value)
    {
        if (empty($value)) return null;

        // If it's numeric, return as is
        if (is_numeric($value)) {
            return (string)$value;
        }

        // If it's string, return as is
        return $value;
    }

    /**
     * Normalisasi header untuk membaca berbagai format
     */
    private function normalizeHeaders($row)
    {
        $normalized = [];
        
        Log::debug('Original row headers:', array_keys($row));
        Log::debug('Original row values:', $row);
        
        // Log header order for debugging
        $headerOrder = [];
        foreach ($row as $key => $value) {
            $headerOrder[] = $key;
        }
        Log::debug('Header processing order:', $headerOrder);
        
        foreach ($row as $key => $value) {
            $cleanKey = strtolower(trim(str_replace(' ', '_', $key)));
            $originalCleanKey = strtolower(trim($key));
            
            Log::debug("Processing header: '$key' -> '$cleanKey' with value: '$value'");
            
            // Mapping berbagai kemungkinan header dengan exact match dulu
            if ($cleanKey === 'kode_lensa' || $originalCleanKey === 'kode lensa' || strpos($originalCleanKey, 'kode') !== false) {
                $normalized['kode_lensa'] = $value;
            }
            elseif ($cleanKey === 'merk_lensa' || $originalCleanKey === 'merk lensa' || strpos($originalCleanKey, 'merk') !== false || strpos($originalCleanKey, 'brand') !== false) {
                $normalized['merk_lensa'] = $value;
            }
            elseif ($cleanKey === 'type' || $originalCleanKey === 'type') {
                $normalized['type'] = $value;
            }
            elseif ($cleanKey === 'index' || strpos($originalCleanKey, 'index') !== false) {
                $normalized['index'] = $value;
            }
            elseif ($cleanKey === 'coating' || strpos($originalCleanKey, 'coating') !== false) {
                $normalized['coating'] = $value;
            }
            elseif ($cleanKey === 'harga_beli' || $originalCleanKey === 'harga beli' || strpos($originalCleanKey, 'harga beli') !== false || strpos($originalCleanKey, 'harga_beli') !== false) {
                $normalized['harga_beli_lensa'] = $value;
            }
            elseif ($cleanKey === 'harga_jual' || $originalCleanKey === 'harga jual' || strpos($originalCleanKey, 'harga jual') !== false || strpos($originalCleanKey, 'harga_jual') !== false) {
                $normalized['harga_jual_lensa'] = $value;
            }
            elseif ($cleanKey === 'tipe_stok' || $originalCleanKey === 'tipe stok' || strpos($originalCleanKey, 'tipe stok') !== false || $key === 'Tipe Stok') {
                // Convert the value to boolean based on content
                $isCustomOrder = $this->parseBoolean($value);
                $normalized['is_custom_order'] = $isCustomOrder;
                Log::debug("Stock type mapped: '$value' from header '$key' -> is_custom_order: " . ($isCustomOrder ? 'true' : 'false'));
            }
            elseif ($cleanKey === 'stok' || $originalCleanKey === 'stok' || $key === 'Stok' || strpos($originalCleanKey, 'stok') !== false || strpos($originalCleanKey, 'stock') !== false) {
                // Skip if this is actually "Tipe Stok" (Stock Type)
                if (strpos($key, 'Tipe') !== false || strpos($key, 'tipe') !== false || $key === 'Tipe Stok') {
                    Log::debug("Skipping 'Tipe Stok' header: '$key' with value: '$value'");
                } else {
                    $normalized['stok'] = $value;
                    Log::debug("Stock value mapped: '$value' from header '$key'");
                    Log::debug("Header mapping details:", [
                        'original_key' => $key,
                        'clean_key' => $cleanKey,
                        'original_clean_key' => $originalCleanKey,
                        'value' => $value,
                        'key_matches_stok' => $key === 'Stok',
                        'clean_key_matches_stok' => $cleanKey === 'stok',
                        'original_clean_key_matches_stok' => $originalCleanKey === 'stok'
                    ]);
                }
            }
            elseif ($cleanKey === 'cabang' || strpos($originalCleanKey, 'cabang') !== false || strpos($originalCleanKey, 'branch') !== false) {
                $normalized['cabang'] = $value;
            }
            elseif ($cleanKey === 'sales' || strpos($originalCleanKey, 'sales') !== false) {
                $normalized['sales'] = $value;
            }
            elseif ($cleanKey === 'catatan' || strpos($originalCleanKey, 'catatan') !== false || strpos($originalCleanKey, 'add') !== false || strpos($originalCleanKey, 'tambahan') !== false || strpos($originalCleanKey, 'keterangan') !== false) {
                $normalized['add'] = $value;
            }
            elseif ($cleanKey === 'cly' || strpos($originalCleanKey, 'cly') !== false) {
                $normalized['cly'] = $value;
            }
            else {
                // Jika tidak cocok dengan mapping, gunakan key asli
                $normalized[$cleanKey] = $value;
            }
        }
        
        Log::info('Normalized lensa row:', $normalized);
        return $normalized;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'merk_lensa' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'index' => 'nullable', // Allow both string and numeric
            'coating' => 'nullable|string|max:255',
            'harga_beli_lensa' => 'nullable|numeric|min:0',
            'harga_jual_lensa' => 'nullable|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
            'cabang' => 'nullable|string|max:255',
            'sales' => 'nullable|string|max:255',
            'is_custom_order' => 'nullable',
            'add' => 'nullable|string',
            'cly' => 'nullable|string',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'merk_lensa.string' => 'Merk lensa harus berupa teks',
            'merk_lensa.max' => 'Merk lensa maksimal 255 karakter',
            'type.string' => 'Type harus berupa teks',
            'type.max' => 'Type maksimal 255 karakter',
            'coating.string' => 'Coating harus berupa teks',
            'coating.max' => 'Coating maksimal 255 karakter',
            'harga_beli_lensa.numeric' => 'Harga beli lensa harus berupa angka',
            'harga_beli_lensa.min' => 'Harga beli lensa minimal 0',
            'harga_jual_lensa.numeric' => 'Harga jual lensa harus berupa angka',
            'harga_jual_lensa.min' => 'Harga jual lensa minimal 0',
            'stok.integer' => 'Stok harus berupa angka bulat',
            'stok.min' => 'Stok minimal 0',
            'cabang.string' => 'Cabang harus berupa teks',
            'cabang.max' => 'Cabang maksimal 255 karakter',
            'sales.string' => 'Sales harus berupa teks',
            'sales.max' => 'Sales maksimal 255 karakter',
            'add.string' => 'Field add harus berupa teks',
            'cly.string' => 'Field cly harus berupa teks',
        ];
    }
} 