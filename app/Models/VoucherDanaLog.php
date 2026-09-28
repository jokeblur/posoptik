<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherDanaLog extends Model
{
    public const JENIS_LABEL = [
        'isi' => 'Isi Dana',
        'tarik' => 'Tarik Dana',
        'ambil' => 'Dipakai Voucher',
        'kembali' => 'Kembali dari Voucher',
    ];

    protected $fillable = [
        'voucher_id',
        'voucher_kode',
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

    public function isMasuk(): bool
    {
        return in_array($this->jenis, ['isi', 'kembali'], true);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
