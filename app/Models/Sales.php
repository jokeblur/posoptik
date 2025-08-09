<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_sales';
    protected $guarded = [] ;

    /**
     * Get all lensa orders handled by this sales
     */
    public function lensas()
    {
        return $this->hasMany(Lensa::class, 'sales_id', 'id_sales');
    }

    /**
     * Get all ready stock lensas handled by this sales
     */
    public function readyStockLensas()
    {
        return $this->hasMany(Lensa::class, 'sales_id', 'id_sales')->readyStock();
    }

    /**
     * Get all custom order lensas handled by this sales
     */
    public function customOrderLensas()
    {
        return $this->hasMany(Lensa::class, 'sales_id', 'id_sales')->customOrder();
    }
}
