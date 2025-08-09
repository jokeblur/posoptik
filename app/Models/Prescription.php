<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id', 'id_dokter');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'id_pasien', 'id_pasien');
    }
}
