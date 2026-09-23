<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pasien;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirMobileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Layout data yang dipakai bersama oleh semua halaman kasir mobile.
     */
    private function layoutData(string $title, string $activeTab): array
    {
        $user = auth()->user();
        $branch = $user->branch_id ? Branch::find($user->branch_id) : Branch::first();

        return [
            'mobileTitle' => $title,
            'activeTab' => $activeTab,
            'branchName' => $branch->name ?? 'Cabang',
        ];
    }

    /**
     * Halaman POS / transaksi penjualan (tab utama).
     */
    public function index()
    {
        $data = $this->layoutData('Kasir', 'pos');
        $data['pasienList'] = Pasien::orderBy('nama_pasien')->limit(300)->get(['id_pasien', 'nama_pasien', 'nohp', 'service_type']);

        return view('kasir-mobile.index', $data);
    }

    /**
     * Riwayat transaksi kasir hari ini.
     */
    public function riwayat()
    {
        $data = $this->layoutData('Riwayat', 'riwayat');

        $transaksis = Penjualan::with('pasien:id_pasien,nama_pasien')
            ->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        $data['transaksis'] = $transaksis;
        $data['totalHariIni'] = $transaksis->sum('total');
        $data['jumlahTransaksi'] = $transaksis->count();

        return view('kasir-mobile.riwayat', $data);
    }

    /**
     * Cek stok barang (frame, lensa, aksesoris).
     */
    public function stok()
    {
        return view('kasir-mobile.stok', $this->layoutData('Cek Stok', 'stok'));
    }

    /**
     * Endpoint pencarian stok untuk halaman mobile (JSON).
     */
    public function stokData(Request $request)
    {
        $user = auth()->user();
        $q = trim((string) $request->get('q', ''));

        $result = collect();

        $frames = \App\Models\Frame::with('branch')
            ->when(!($user->isAdmin() || $user->isSuperAdmin()), fn ($query) => $query->where('branch_id', $user->branch_id))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('merk_frame', 'LIKE', "%{$q}%")
                      ->orWhere('kode_frame', 'LIKE', "%{$q}%")
                      ->orWhere('jenis_frame', 'LIKE', "%{$q}%");
                });
            })
            ->limit($q === '' ? 20 : 50)
            ->get()
            ->map(fn ($f) => [
                'kode' => $f->kode_frame,
                'nama' => $f->merk_frame,
                'info' => $f->jenis_frame ?: 'Umum',
                'stok' => (int) $f->stok,
                'harga' => 'Rp ' . number_format((float) $f->harga_jual_frame, 0, ',', '.'),
                'cabang' => $f->branch->name ?? '-',
                'tipe' => 'Frame',
            ]);

        $lensas = \App\Models\Lensa::with('branch')
            ->where('is_custom_order', false)
            ->when(!($user->isAdmin() || $user->isSuperAdmin()), fn ($query) => $query->where('branch_id', $user->branch_id))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('merk_lensa', 'LIKE', "%{$q}%")
                      ->orWhere('kode_lensa', 'LIKE', "%{$q}%")
                      ->orWhere('type', 'LIKE', "%{$q}%")
                      ->orWhere('coating', 'LIKE', "%{$q}%");
                });
            })
            ->limit($q === '' ? 20 : 50)
            ->get()
            ->map(fn ($l) => [
                'kode' => $l->kode_lensa,
                'nama' => $l->merk_lensa,
                'info' => trim(($l->type ?? '-') . ' · ' . ($l->index ?? '-')),
                'stok' => (int) $l->stok,
                'harga' => 'Rp ' . number_format((float) $l->harga_jual_lensa, 0, ',', '.'),
                'cabang' => $l->branch->name ?? '-',
                'tipe' => 'Lensa',
            ]);

        $aksesoris = \App\Models\Aksesoris::with('branch')
            ->when(!($user->isAdmin() || $user->isSuperAdmin()), fn ($query) => $query->where('branch_id', $user->branch_id))
            ->when($q !== '', fn ($query) => $query->where('nama_produk', 'LIKE', "%{$q}%"))
            ->limit($q === '' ? 20 : 50)
            ->get()
            ->map(fn ($a) => [
                'kode' => 'AKS-' . $a->id,
                'nama' => $a->nama_produk,
                'info' => 'Aksesoris',
                'stok' => (int) $a->stok,
                'harga' => 'Rp ' . number_format((float) $a->harga_jual, 0, ',', '.'),
                'cabang' => $a->branch->name ?? '-',
                'tipe' => 'Aksesoris',
            ]);

        return response()->json($result->concat($frames)->concat($lensas)->concat($aksesoris)->values());
    }

    /**
     * Daftar permintaan transfer stok cabang sendiri (ringkas, mobile).
     */
    public function transfer()
    {
        $data = $this->layoutData('Transfer Stok', 'transfer');
        $user = auth()->user();

        $data['transfers'] = \App\Models\StockTransfer::with(['fromBranch', 'toBranch', 'details'])
            ->accessibleByUser($user)
            ->latest()
            ->limit(30)
            ->get();

        return view('kasir-mobile.transfer', $data);
    }
}
