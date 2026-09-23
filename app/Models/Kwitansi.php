<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
    use HasFactory;

    protected $table = 'kwitansis';

    protected $fillable = [
        'jenis',
        'nomor',
        'tempat_tanggal',
        'penerima_dari',
        'untuk_pembayaran',
        'nama_pembuat',
        'harga_frame',
        'harga_lensa',
        'jumlah',
        'terbilang',
        'created_by',
    ];

    protected $casts = [
        'harga_frame' => 'decimal:2',
        'harga_lensa' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
