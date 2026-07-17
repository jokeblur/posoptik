<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'jabatan', 'branch_id', 'no_hp', 'email',
        'tanggal_masuk', 'status', 'gaji_pokok', 'catatan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'gaji_pokok'    => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function gajiKaryawan()
    {
        return $this->hasMany(GajiKaryawan::class);
    }
}
