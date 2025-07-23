<?php

namespace App\Imports;

use App\Models\Frame;
use Maatwebsite\Excel\Concerns\ToModel;

class FrameImport implements ToModel
{
    public function model(array $row)
    {
        // Asumsi urutan kolom: kode_frame, nama_frame, jenis_frame, harga_beli_frame, harga_jual_frame, stok, branch_id, id_sales
        return new Frame([
            'kode_frame'        => $row[0],
            'nama_frame'        => $row[1],
            'jenis_frame'       => $row[2],
            'harga_beli_frame'  => $row[3],
            'harga_jual_frame'  => $row[4],
            'stok'              => $row[5],
            'branch_id'         => $row[6],
            'id_sales'          => $row[7],
        ]);
    }
} 