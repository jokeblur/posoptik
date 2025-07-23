<?php

namespace App\Imports;

use App\Models\Lensa;
use Maatwebsite\Excel\Concerns\ToModel;

class LensaImport implements ToModel
{
    public function model(array $row)
    {
        // Asumsi urutan kolom: kode_lensa, merk_lensa, type, index, coating, harga_beli_lensa, harga_jual_lensa, stok, branch_id
        return new Lensa([
            'kode_lensa'        => $row[0],
            'merk_lensa'        => $row[1],
            'type'              => $row[2],
            'index'             => $row[3],
            'coating'           => $row[4],
            'harga_beli_lensa'  => $row[5],
            'harga_jual_lensa'  => $row[6],
            'stok'              => $row[7],
            'branch_id'         => $row[8],
        ]);
    }
} 