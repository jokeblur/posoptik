<?php

namespace App\Imports;

use App\Models\Pasien;
use App\Models\Prescription;
use App\Models\Dokter;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PasienImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return DB::transaction(function() use ($row) {
            $pasien = Pasien::updateOrCreate(
                ['id_pasien' => $row['id'] ?? null],
                [
                    'nama_pasien' => $row['nama_pasien'] ?? '',
                    'alamat' => $row['alamat'] ?? '',
                    'nohp' => (string)($row['no_hp'] ?? ''), // Convert to string
                    'service_type' => $row['jenis_layanan'] ?? '',
                    'no_bpjs' => $row['no_bpjs'] ?? '',
                ]
            );
            
            if (
                $row['od_sph'] ?? null || $row['od_cyl'] ?? null || $row['od_axis'] ?? null ||
                $row['os_sph'] ?? null || $row['os_cyl'] ?? null || $row['os_axis'] ?? null ||
                $row['add'] ?? null || $row['pd'] ?? null || $row['catatan'] ?? null ||
                $row['dokter'] ?? null
            ) {
                // Handle dokter data
                $dokterId = null;
                $dokterManual = null;
                
                if (!empty($row['dokter'])) {
                    // Coba cari dokter berdasarkan nama
                    $dokter = Dokter::where('nama_dokter', 'LIKE', '%' . trim($row['dokter']) . '%')->first();
                    
                    if ($dokter) {
                        $dokterId = $dokter->id_dokter;
                    } else {
                        // Jika tidak ditemukan, simpan sebagai dokter manual
                        $dokterManual = trim($row['dokter']);
                    }
                }
                
                Prescription::create([
                    'id_pasien' => $pasien->id_pasien,
                    'od_sph' => $row['od_sph'] ?? '',
                    'od_cyl' => $row['od_cyl'] ?? '',
                    'od_axis' => $row['od_axis'] ?? '',
                    'os_sph' => $row['os_sph'] ?? '',
                    'os_cyl' => $row['os_cyl'] ?? '',
                    'os_axis' => $row['os_axis'] ?? '',
                    'add' => $row['add'] ?? '',
                    'pd' => $row['pd'] ?? '',
                    'dokter_id' => $dokterId,
                    'dokter_manual' => $dokterManual,
                    'catatan' => $row['catatan'] ?? '',
                    'tanggal' => $row['tanggal_resep'] ?? now(),
                ]);
            }
            return $pasien;
        });
    }
} 