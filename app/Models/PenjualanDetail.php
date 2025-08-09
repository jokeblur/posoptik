<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_detail';
    protected $guarded = [];
    
    protected $fillable = [
        'penjualan_id', 'itemable_id', 'itemable_type', 'quantity', 'price', 'subtotal', 'additional_cost'
    ];

    public function itemable()
    {
        return $this->morphTo();
    }

    public function penjualan()
    {
        return $this->belongsTo(Transaksi::class, 'penjualan_id');
    }
}
