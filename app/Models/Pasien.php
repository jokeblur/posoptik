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
    protected $fillable = ['nama_pasien', 'alamat', 'nohp', 'service_type', 'no_bpjs'];

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'id_pasien')->orderBy('tanggal', 'asc');
    }

    /**
     * Boot method untuk menambahkan event listener
     */
    protected static function boot()
    {
        parent::boot();

        // Ketika pasien dihapus, hapus juga semua prescriptions yang terkait
        static::deleting(function ($pasien) {
            $pasien->prescriptions()->delete();
        });
    }
}
