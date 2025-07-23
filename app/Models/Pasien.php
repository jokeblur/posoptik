<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';
    protected $primaryKey = 'id_pasien';
    protected $guarded = [];

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'id_pasien');
    }
}
