<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajiKaryawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id', 'bulan', 'tahun',
        'gaji_pokok', 'bonus', 'tunjangan', 'potongan', 'total_gaji',
        'keterangan', 'created_by',
    ];

    protected $casts = [
        'gaji_pokok'  => 'decimal:2',
        'bonus'       => 'decimal:2',
        'tunjangan'   => 'decimal:2',
        'potongan'    => 'decimal:2',
        'total_gaji'  => 'decimal:2',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNamaBulanAttribute()
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $bulan[$this->bulan] ?? '-';
    }
}
