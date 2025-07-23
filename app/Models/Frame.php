<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    use HasFactory;
    
    protected $guarded = [] ;

    /**
     * Get the branch that owns the frame
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /**
     * Get the sales that associated with the frame
     */
    public function sales()
    {
        return $this->belongsTo(Sales::class, 'id_sales');
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
