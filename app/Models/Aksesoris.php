<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aksesoris extends Model
{
    protected $fillable = ['nama_produk', 'harga_beli', 'harga_jual', 'stok', 'branch_id'];
    public function branch() {
        return $this->belongsTo(\App\Models\Branch::class);
    }
} 