<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Dompet/anggaran voucher (satu baris). Saldo voucher uang diambil dari sini.
 */
class VoucherDana extends Model
{
    protected $table = 'voucher_dana';

    protected $fillable = ['saldo'];

    protected $casts = [
        'saldo' => 'float',
    ];

    /**
     * Ambil baris dana (dibuat bila belum ada). Pakai $lock = true di dalam transaksi DB
     * sebelum mengubah saldo agar tidak bentrok.
     */
    public static function utama(bool $lock = false): self
    {
        $query = static::query()->orderBy('id');
        $dana = $lock ? $query->lockForUpdate()->first() : $query->first();

        return $dana ?: static::create(['saldo' => 0]);
    }

    public function saldoLabel(): string
    {
        return 'Rp ' . number_format((float) $this->saldo, 0, ',', '.');
    }

    /**
     * Ubah saldo dana dan catat riwayatnya. 'isi' & 'kembali' menambah, 'tarik' & 'ambil' mengurangi.
     *
     * @throws RuntimeException bila saldo dana tidak cukup.
     */
    public function catat(string $jenis, float $jumlah, ?int $userId = null, ?Voucher $voucher = null, ?string $keterangan = null): VoucherDanaLog
    {
        $jumlah = round(max(0, $jumlah), 2);
        $keluar = in_array($jenis, ['tarik', 'ambil'], true);

        if ($keluar && $jumlah > round((float) $this->saldo, 2)) {
            throw new RuntimeException(
                'Dana voucher tidak cukup. Sisa dana ' . $this->saldoLabel()
                . ', dibutuhkan Rp ' . number_format($jumlah, 0, ',', '.') . '.'
            );
        }

        $this->saldo = $keluar ? (float) $this->saldo - $jumlah : (float) $this->saldo + $jumlah;
        $this->save();

        return VoucherDanaLog::create([
            'voucher_id' => $voucher ? $voucher->id : null,
            'voucher_kode' => $voucher ? $voucher->kode : null,
            'user_id' => $userId,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'saldo_sesudah' => $this->saldo,
            'keterangan' => $keterangan,
        ]);
    }
}
