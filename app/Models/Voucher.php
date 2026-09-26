<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'jenis_nominal',
        'nominal',
        'saldo',
        'syarat_ketentuan',
        'berlaku_mulai',
        'berlaku_sampai',
        'aktif',
        'created_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'saldo' => 'float',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
        'aktif' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'voucher_id');
    }

    public function saldoLogs()
    {
        return $this->hasMany(VoucherSaldoLog::class)->latest('id');
    }

    public function isDiskon(): bool
    {
        return ($this->jenis_nominal ?? 'uang') === 'diskon';
    }

    public function jumlahPemakaian(): int
    {
        if (array_key_exists('penjualans_count', $this->attributes)) {
            return (int) $this->attributes['penjualans_count'];
        }

        return $this->relationLoaded('penjualans') ? $this->penjualans->count() : $this->penjualans()->count();
    }

    /**
     * Status pemakaian voucher: key, label, dan apakah bisa dipakai di penjualan.
     * Voucher uang bisa dipakai berkali-kali selama saldo masih ada; voucher diskon sekali pakai.
     */
    public function statusInfo(): array
    {
        if ($this->isDiskon() && $this->jumlahPemakaian() > 0) {
            return ['key' => 'terpakai', 'label' => 'Sudah Dipakai', 'valid' => false];
        }
        if (!$this->isDiskon() && (float) $this->saldo <= 0) {
            return ['key' => 'saldo_habis', 'label' => 'Saldo Habis', 'valid' => false];
        }
        if (!$this->aktif) {
            return ['key' => 'nonaktif', 'label' => 'Nonaktif', 'valid' => false];
        }
        if ($this->berlaku_sampai && $this->berlaku_sampai->copy()->endOfDay()->isPast()) {
            return ['key' => 'kadaluarsa', 'label' => 'Kadaluarsa', 'valid' => false];
        }
        if ($this->berlaku_mulai && $this->berlaku_mulai->copy()->startOfDay()->isFuture()) {
            return ['key' => 'belum_berlaku', 'label' => 'Belum Berlaku', 'valid' => false];
        }

        return ['key' => 'aktif', 'label' => 'Aktif - Bisa Digunakan', 'valid' => true];
    }

    public function nominalLabel(): string
    {
        return $this->isDiskon()
            ? number_format((float) $this->nominal, 0, ',', '.') . '%'
            : 'Rp ' . number_format((float) $this->nominal, 0, ',', '.');
    }

    public function saldoLabel(): string
    {
        return $this->isDiskon() ? '-' : 'Rp ' . number_format((float) $this->saldo, 0, ',', '.');
    }

    /**
     * Potongan rupiah untuk total belanja; tidak pernah melebihi saldo voucher
     * maupun sisa yang harus dibayar setelah diskon lain.
     */
    public function hitungPotongan(float $totalBelanja, float $diskonLain = 0): float
    {
        $totalBelanja = max(0, $totalBelanja);
        $potongan = $this->isDiskon()
            ? round($totalBelanja * min(100, max(0, (float) $this->nominal)) / 100)
            : max(0, (float) $this->saldo);

        return min($potongan, max(0, $totalBelanja - max(0, $diskonLain)));
    }

    /**
     * Ubah saldo dan catat riwayatnya. Pemanggil wajib mengunci baris voucher (lockForUpdate)
     * di dalam transaksi DB agar saldo tidak bentrok.
     */
    public function catatSaldo(string $jenis, float $jumlah, ?int $userId = null, ?int $penjualanId = null, ?string $keterangan = null): VoucherSaldoLog
    {
        $jumlah = round(max(0, $jumlah), 2);
        $this->saldo = $jenis === 'pakai'
            ? max(0, (float) $this->saldo - $jumlah)
            : (float) $this->saldo + $jumlah;
        $this->save();

        return $this->saldoLogs()->create([
            'penjualan_id' => $penjualanId,
            'user_id' => $userId,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'saldo_sesudah' => $this->saldo,
            'keterangan' => $keterangan,
        ]);
    }
}
