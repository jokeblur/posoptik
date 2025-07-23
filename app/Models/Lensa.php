<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lensa extends Model
{
    use HasFactory;
    
    protected $table = 'lensa';
    protected $guarded = [] ;

    /**
     * Get the branch that owns the lensa
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
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
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }
        
        return $query->where('branch_id', $user->branch_id);
    }

    public function penjualanDetail()
    {
        return $this->morphMany(PenjualanDetail::class, 'itemable');
    }
}
