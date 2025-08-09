<?php

namespace App\Imports;

use App\Models\Sales;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class SalesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Sales([
            'nama_sales' => $row['nama_sales'],
            'alamat' => $row['alamat'] ?? null,
            'nohp' => $row['nohp'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama_sales' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'nohp' => 'nullable|integer',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nama_sales.required' => 'Nama sales wajib diisi',
            'nama_sales.string' => 'Nama sales harus berupa teks',
            'nama_sales.max' => 'Nama sales maksimal 255 karakter',
            'alamat.max' => 'Alamat maksimal 500 karakter',
            'nohp.integer' => 'Nomor HP harus berupa angka',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ];
    }
} 