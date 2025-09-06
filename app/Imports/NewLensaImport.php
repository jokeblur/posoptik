<?php

namespace App\Imports;

use App\Models\Lensa;
use App\Models\Branch;
use App\Models\Sales;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewLensaImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, WithBatchInserts
{
    protected $user;
    protected $importedCount = 0;
    protected $errors = [];

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        try {
            // Generate kode_lensa if empty
            $kodeLensa = $row['kode_lensa'] ?? '';
            if (empty($kodeLensa)) {
                $kodeLensa = 'L' . str_pad(Lensa::max('id') + 1, 5, '0', STR_PAD_LEFT);
            }

            // Find branch by name
            $branchId = null;
            if (!empty($row['cabang'])) {
                $branch = Branch::where('name', 'like', '%' . $row['cabang'] . '%')->first();
                if ($branch) {
                    $branchId = $branch->id;
                } else {
                    // Use user's branch if not found
                    $branchId = $this->user->branch_id;
                }
            } else {
                $branchId = $this->user->branch_id;
            }

            // Find sales by name
            $salesId = null;
            if (!empty($row['sales'])) {
                $sales = Sales::where('nama_sales', 'like', '%' . $row['sales'] . '%')->first();
                if ($sales) {
                    $salesId = $sales->id_sales;
                }
            }

            // Determine stock type
            $isCustomOrder = false;
            if (isset($row['tipe_stok'])) {
                $isCustomOrder = strtolower($row['tipe_stok']) === 'custom order';
            }

            $lensa = new Lensa([
                'kode_lensa' => $kodeLensa,
                'merk_lensa' => $row['merk_lensa'] ?? '',
                'type' => $row['type'] ?? '',
                'index' => $row['index'] ?? '',
                'coating' => $row['coating'] ?? '',
                'harga_beli_lensa' => $row['harga_beli'] ?? 0,
                'harga_jual_lensa' => $row['harga_jual'] ?? 0,
                'stok' => $row['stok'] ?? 0,
                'branch_id' => $branchId,
                'sales_id' => $salesId,
                'is_custom_order' => $isCustomOrder,
                'add' => $row['catatan_add'] ?? '',
                'cly' => $row['cly'] ?? '',
            ]);

            $this->importedCount++;
            
            if ($this->importedCount % 100 === 0) {
                Log::info('NewLensaImport: Imported ' . $this->importedCount . ' records');
            }

            return $lensa;

        } catch (\Exception $e) {
            Log::error('NewLensaImport error on row: ' . json_encode($row) . ' - ' . $e->getMessage());
            $this->errors[] = 'Error on row: ' . json_encode($row) . ' - ' . $e->getMessage();
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'merk_lensa' => 'required|string|max:255',
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'merk_lensa.required' => 'Merk lensa harus diisi',
            'harga_beli.numeric' => 'Harga beli harus berupa angka',
            'harga_jual.numeric' => 'Harga jual harus berupa angka',
            'stok.integer' => 'Stok harus berupa angka bulat',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
