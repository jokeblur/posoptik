<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lensa extends Model
{
    use HasFactory;
    
    protected $table = 'lensa';
    protected $fillable = [
        'kode_lensa',
        'sales_id',
        'merk_lensa',
        'type',
        'index',
        'coating',
        'harga_beli_lensa',
        'harga_jual_lensa',
        'stok',
        'is_custom_order',
        'add',
        'cly',
        'branch_id',
    ];

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
     * Scope to filter BPJS lens category.
     *
     * BPJS lens identifiers:
     * - KRYP CR MC HIJAU
     * - CR MC HIJAU
     */
    public function scopeBpjsCategory($query)
    {
        $normalizedType = "REPLACE(REPLACE(UPPER(TRIM(COALESCE(type, ''))), ' ', ''), '-', '')";
        $normalizedCoating = "REPLACE(REPLACE(UPPER(TRIM(COALESCE(coating, ''))), ' ', ''), '-', '')";
        $normalizedMerk = "REPLACE(REPLACE(UPPER(TRIM(COALESCE(merk_lensa, ''))), ' ', ''), '-', '')";
        $bpjsTokens = "('KRYPCRMCHIJAU', 'KRYPCRMCHIJAU', 'KCRMCHIJAU', 'CRMCHIJAU')";

        return $query->where(function ($q) use ($normalizedType, $normalizedCoating, $normalizedMerk, $bpjsTokens) {
            $q->whereRaw("{$normalizedType} IN {$bpjsTokens}")
                ->orWhereRaw("{$normalizedCoating} IN {$bpjsTokens}")
                ->orWhereRaw("{$normalizedMerk} IN {$bpjsTokens}")
                ->orWhere(function ($q2) use ($normalizedMerk) {
                    $q2->whereRaw("{$normalizedMerk} LIKE '%CRMCHIJAU%'")
                        ->where(function ($q3) use ($normalizedMerk) {
                            $q3->whereRaw("{$normalizedMerk} LIKE '%KRYP%'")
                                ->orWhereRaw("{$normalizedMerk} LIKE '%KRYP%'");
                        });
                });
        });
    }

    /**
     * Scope to exclude BPJS lens category.
     */
    public function scopeNonBpjsCategory($query)
    {
        $normalizedType = "REPLACE(REPLACE(UPPER(TRIM(COALESCE(type, ''))), ' ', ''), '-', '')";
        $normalizedCoating = "REPLACE(REPLACE(UPPER(TRIM(COALESCE(coating, ''))), ' ', ''), '-', '')";
        $normalizedMerk = "REPLACE(REPLACE(UPPER(TRIM(COALESCE(merk_lensa, ''))), ' ', ''), '-', '')";
        $bpjsTokens = "('KRYPCRMCHIJAU', 'KRYPCRMCHIJAU', 'KCRMCHIJAU', 'CRMCHIJAU')";

        return $query->where(function ($q) use ($normalizedType, $normalizedCoating, $normalizedMerk, $bpjsTokens) {
            $bpjsCondition = "(
                {$normalizedType} IN {$bpjsTokens}
                OR {$normalizedCoating} IN {$bpjsTokens}
                OR {$normalizedMerk} IN {$bpjsTokens}
                OR (
                    {$normalizedMerk} LIKE '%CRMCHIJAU%'
                    AND ({$normalizedMerk} LIKE '%KRYP%' OR {$normalizedMerk} LIKE '%KRYP%')
                )
            )";

            $q->whereRaw("NOT {$bpjsCondition}");
        });
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
