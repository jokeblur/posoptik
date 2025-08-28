<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $guarded = [];

    protected $fillable = [
        'kode_penjualan', 'tanggal', 'user_id', 'branch_id', 'pasien_id', 
        'dokter_id', 'dokter_manual', 'tanggal_siap', 'total', 'diskon', 'bayar', 
        'kekurangan', 'status', 'status_pengerjaan', 'photo_bpjs', 'signature_bpjs', 'signature_date'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_siap' => 'date',
        'signature_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'id_pasien');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id', 'id_dokter');
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class);
    }

    public function passetByUser()
    {
        return $this->belongsTo(User::class, 'passet_by_user_id');
    }

    /**
     * Check if this transaction is for BPJS patient
     */
    public function isBPJS()
    {
        return $this->pasien && str_contains(strtolower($this->pasien->service_type), 'bpjs');
    }

    /**
     * Check if signature is required and completed
     */
    public function isSignatureRequired()
    {
        return $this->isBPJS() && !empty($this->signature_bpjs);
    }

    /**
     * Get signature status
     */
    public function getSignatureStatusAttribute()
    {
        if (!$this->isBPJS()) {
            return 'Tidak Berlaku';
        }
        
        return !empty($this->signature_bpjs) ? 'Sudah Ditandatangani' : 'Belum Ditandatangani';
    }
}
