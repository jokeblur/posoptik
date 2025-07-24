<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $table = 'penjualan';
    protected $primaryKey = 'id';
    protected $guarded = [];
    
    // Menambahkan accessor untuk nama pasien (bisa dari relasi atau manual)
    public function getNamaPasienAttribute()
    {
        if ($this->pasien_id && $this->pasien) {
            return $this->pasien->nama_pasien;
        }
        return $this->nama_pasien_manual;
    }

    /**
     * Get the branch that owns the penjualan
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who made the sale
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function passetByUser()
    {
        return $this->belongsTo(User::class, 'passet_by_user_id');
    }

    /**
     * Get the patient associated with the sale
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'id_pasien');
    }

    /**
     * Get the doctor associated with the sale
     */
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id', 'id_dokter');
    }

    /**
     * Scope to filter by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter by user's accessible branches
     */
    public function scopeAccessibleByUser($query, $user)
    {
        if ($user->canAccessAllBranches()) {
            return $query;
        }
        
        return $query->where('branch_id', $user->branch_id);
    }

    public function details()
{
    return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
}
}
