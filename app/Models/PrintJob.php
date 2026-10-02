<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Job cetak yang dikirim dari tablet dan dicetak oleh halaman Print Agent di PC cabang.
 */
class PrintJob extends Model
{
    public const JENIS = [
        'struk' => 'Struk 80mm',
        'half' => 'Nota Half Page (10x15)',
    ];

    public const STATUS_LABEL = [
        'menunggu' => 'Menunggu PC',
        'proses' => 'Sedang dicetak',
        'selesai' => 'Sudah dicetak',
        'gagal' => 'Gagal',
    ];

    /** Job 'proses' yang tidak dilaporkan selesai dalam waktu ini boleh diambil ulang (mis. PC mati). */
    public const BATAS_PROSES_DETIK = 120;

    protected $fillable = [
        'branch_id',
        'penjualan_id',
        'jenis',
        'judul',
        'status',
        'pesan',
        'dibuat_oleh',
        'dicetak_oleh',
        'diambil_at',
        'selesai_at',
    ];

    protected $casts = [
        'diambil_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Halaman yang dicetak agent (halaman cetak nota yang sudah ada).
     */
    public function urlCetak(): ?string
    {
        if (!$this->penjualan_id) {
            return null;
        }

        return $this->jenis === 'struk'
            ? route('penjualan.cetak', $this->penjualan_id)
            : route('penjualan.cetak-half', $this->penjualan_id);
    }

    public function toAgentArray(): array
    {
        return [
            'id' => $this->id,
            'jenis' => $this->jenis,
            'jenis_label' => self::JENIS[$this->jenis] ?? $this->jenis,
            'judul' => $this->judul,
            'status' => $this->status,
            'status_label' => self::STATUS_LABEL[$this->status] ?? $this->status,
            'pesan' => $this->pesan,
            'url' => $this->urlCetak(),
            'dibuat_oleh' => optional($this->pembuat)->name,
            'dibuat_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i:s') : null,
            'selesai_at' => $this->selesai_at ? $this->selesai_at->format('H:i:s') : null,
        ];
    }
}
