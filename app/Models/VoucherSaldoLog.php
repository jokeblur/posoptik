<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherSaldoLog extends Model
{
    public const JENIS_LABEL = [
        'tambah' => 'Tambah Saldo',
        'pakai' => 'Dipakai',
        'kembali' => 'Dikembalikan',
        'kurang' => 'Saldo Dikurangi',
    ];

    protected $fillable = [
        'voucher_id',
        'penjualan_id',
        'user_id',
        'jenis',
        'jumlah',
        'saldo_sesudah',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'float',
        'saldo_sesudah' => 'float',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
