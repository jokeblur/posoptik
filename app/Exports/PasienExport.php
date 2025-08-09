<?php

namespace App\Exports;

use App\Models\Pasien;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PasienExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $data = Pasien::with(['prescriptions' => function($q) {
            $q->orderByDesc('tanggal');
        }])->get();
        return $data->map(function($pasien) {
            $rx = $pasien->prescriptions->first();
            
            // Get doctor name - check if it's manual entry or from dokter table
            $dokterName = '';
            if ($rx) {
                if (!empty($rx->dokter_manual)) {
                    $dokterName = $rx->dokter_manual;
                } elseif ($rx->dokter_id) {
                    // Query dokter directly since relationship might not work
                    $dokter = \App\Models\Dokter::find($rx->dokter_id);
                    $dokterName = $dokter ? $dokter->nama_dokter : '';
                }
            }
            
            return [
                'id_pasien' => $pasien->id_pasien,
                'nama_pasien' => $pasien->nama_pasien,
                'alamat' => $pasien->alamat,
                'nohp' => $pasien->nohp,
                'service_type' => $pasien->service_type,
                'no_bpjs' => $pasien->no_bpjs,
                'created_at' => $pasien->created_at,
                'updated_at' => $pasien->updated_at,
                // Prescription
                'od_sph' => $rx->od_sph ?? '',
                'od_cyl' => $rx->od_cyl ?? '',
                'od_axis' => $rx->od_axis ?? '',
                'os_sph' => $rx->os_sph ?? '',
                'os_cyl' => $rx->os_cyl ?? '',
                'os_axis' => $rx->os_axis ?? '',
                'add' => $rx->add ?? '',
                'pd' => $rx->pd ?? '',
                'dokter' => $dokterName,
                'catatan' => $rx->catatan ?? '',
                'tanggal_resep' => $rx->tanggal ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Pasien',
            'Alamat',
            'No HP',
            'Jenis Layanan',
            'No BPJS',
            'Tanggal Daftar',
            'Tanggal Update',
            // Prescription
            'OD SPH',
            'OD CYL',
            'OD AXIS',
            'OS SPH',
            'OS CYL',
            'OS AXIS',
            'ADD',
            'PD',
            'Dokter',
            'Catatan',
            'Tanggal Resep',
        ];
    }
} 