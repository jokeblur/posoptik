<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lensa extends Model
{
    use HasFactory;
    
    protected $table = 'lensa';
    protected $guarded = [] ;

    protected $casts = [
        'is_custom_order' => 'boolean',
        'stok' => 'integer',
    ];

    /**
     * Get the branch that owns the lensa
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /**
     * Get the sales that handles this lensa order
     */
    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'id_sales');
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

    /**
     * Scope to filter by ready stock
     */
    public function scopeReadyStock($query)
    {
        return $query->where('is_custom_order', false);
    }

    /**
     * Scope to filter by custom order
     */
    public function scopeCustomOrder($query)
    {
        return $query->where('is_custom_order', true);
    }

    /**
     * Get stock status label
     */
    public function getStockStatusAttribute()
    {
        return $this->is_custom_order ? 'Custom Order' : 'Ready Stock';
    }

    public function penjualanDetail()
    {
        return $this->morphMany(PenjualanDetail::class, 'itemable');
    }
}
