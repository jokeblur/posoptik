<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the manager of the branch
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all users assigned to this branch
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all frames in this branch
     */
    public function frames()
    {
        return $this->hasMany(Frames::class);
    }

    /**
     * Get all lensas in this branch
     */
    public function lensas()
    {
        return $this->hasMany(Lensa::class);
    }

    /**
     * Get all sales in this branch
     */
    public function penjualan()
    {
        return $this->hasMany(Penjualan::class);
    }

    /**
     * Scope to get only active branches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
