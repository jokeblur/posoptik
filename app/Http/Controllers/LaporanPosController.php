<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Branch;
use App\Models\Frame;
use App\Models\Lensa;
use App\Services\BpjsPricingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanPosController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super admin';
        
        // Get all branches for super admin, only user's branch for others
        $branches = $isSuperAdmin ? Branch::active()->get() : Branch::where('id', $user->branch_id)->get();
        
        // Get selected branch from request or default to active branch for super admin.
        $defaultBranchId = $isSuperAdmin ? session('active_branch_id', $user->branch_id) : $user->branch_id;
        $selectedBranchId = $request->input('branch_id', $defaultBranchId);
        $selectedBranch = $selectedBranchId ? Branch::find($selectedBranchId) : null;

        // Filter data berdasarkan cabang
        $branchId = $selectedBranchId;

        // Omset Harian (dipisah BPJS vs Umum)
        $today = Carbon::today();
        $transaksiHarian = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', $today)
            ->with('pasien:id_pasien,service_type')
            ->get();

        $bpjsTypes = ['BPJS I', 'BPJS II', 'BPJS III'];
        $omsetHarianBpjs = $transaksiHarian->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);

            if (!in_array($serviceType, $bpjsTypes)) {
                return 0;
            }

            if ($trx->bpjs_default_price > 0) {
                return (float) $trx->bpjs_default_price;
            }

            switch ($serviceType) {
                case 'BPJS I':
                    return BpjsPricingService::BPJS_I_PRICE;
                case 'BPJS II':
                    return BpjsPricingService::BPJS_II_PRICE;
                case 'BPJS III':
                    return BpjsPricingService::BPJS_III_PRICE;
                default:
                    return 0;
            }
        });

        $omsetHarianUmum = $transaksiHarian->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $omsetHarian = $omsetHarianBpjs + $omsetHarianUmum;

        // Omset Bulanan (dipisah BPJS vs Umum, mengikuti filter bulan/tahun)
        $bulan = $request->input('bulan', $today->format('m'));
        $tahun = $request->input('tahun', $today->format('Y'));
        $transaksiBulanan = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->with('pasien:id_pasien,service_type')
            ->get();

        $omsetBulananBpjs = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);

            if (!in_array($serviceType, $bpjsTypes)) {
                return 0;
            }

            if ($trx->bpjs_default_price > 0) {
                return (float) $trx->bpjs_default_price;
            }

            switch ($serviceType) {
                case 'BPJS I':
                    return BpjsPricingService::BPJS_I_PRICE;
                case 'BPJS II':
                    return BpjsPricingService::BPJS_II_PRICE;
                case 'BPJS III':
                    return BpjsPricingService::BPJS_III_PRICE;
                default:
                    return 0;
            }
        });

        $omsetBulananUmum = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $omsetBulanan = $omsetBulananBpjs + $omsetBulananUmum;

        // Omset per layanan
        $layananList = ['BPJS I', 'BPJS II', 'BPJS III', 'Umum'];
        $omsetLayanan = [];
        foreach ($layananList as $layanan) {
            if ($layanan === 'Umum') {
                // Untuk layanan umum, gunakan total transaksi
                $omsetLayanan[$layanan] = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereHas('pasien', function($q) use ($layanan) {
                        $q->where('service_type', $layanan);
                    })
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->sum('total');
            } else {
                // Untuk layanan BPJS, gunakan bpjs_default_price
                $omsetLayanan[$layanan] = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereHas('pasien', function($q) use ($layanan) {
                        $q->where('service_type', $layanan);
                    })
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->sum('bpjs_default_price');
            }
        }

        // Rekap DP (Belum Lunas, sudah bayar sebagian)
        $rekapDP = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'Belum Lunas')
            ->where('bayar', '>', 0)
            ->with(['pasien', 'branch'])
            ->get();

        // Rekap Lunas
        $rekapLunas = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'Lunas')
            ->where(function ($q) use ($bpjsTypes) {
                $q->where(function ($sub) use ($bpjsTypes) {
                    $sub->whereNotNull('pasien_service_type')
                        ->whereNotIn('pasien_service_type', $bpjsTypes);
                })->orWhere(function ($sub) use ($bpjsTypes) {
                    $sub->whereNull('pasien_service_type')
                        ->where(function ($sub2) use ($bpjsTypes) {
                            $sub2->whereDoesntHave('pasien')
                                ->orWhereHas('pasien', function ($pasienQuery) use ($bpjsTypes) {
                                    $pasienQuery->whereNotIn('service_type', $bpjsTypes);
                                });
                        });
                });
            })
            ->with(['pasien', 'branch'])
            ->get();



        // Piutang (total dan list transaksi belum lunas)
        $piutangList = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'Belum Lunas')
            ->with(['pasien', 'branch'])
            ->get();
        $totalPiutang = $piutangList->sum('kekurangan');

        // Detail transaksi harian
        $detailHarian = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', $today)
            ->with(['pasien', 'branch'])
            ->get();
            
        // Detail transaksi bulanan
        $detailBulanan = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->with(['pasien', 'branch'])
            ->get();

        // Summary per cabang (hanya untuk super admin)
        $summaryCabang = [];
        if ($isSuperAdmin && !$selectedBranchId) {
            foreach ($branches as $branch) {
                $summaryCabang[$branch->id] = [
                    'name' => $branch->name,
                    'omset_harian' => Penjualan::where('branch_id', $branch->id)
                        ->whereDate('created_at', $today)
                        ->sum('total'),
                    'omset_bulanan' => Penjualan::where('branch_id', $branch->id)
                        ->whereMonth('created_at', $bulan)
                        ->whereYear('created_at', $tahun)
                        ->sum('total'),
                    'piutang' => Penjualan::where('branch_id', $branch->id)
                        ->where('status', 'Belum Lunas')
                        ->sum('kekurangan'),
                    'transaksi_harian' => Penjualan::where('branch_id', $branch->id)
                        ->whereDate('created_at', $today)
                        ->count(),
                    'transaksi_bulanan' => Penjualan::where('branch_id', $branch->id)
                        ->whereMonth('created_at', $bulan)
                        ->whereYear('created_at', $tahun)
                        ->count(),
                ];
            }
        }

        return view('laporan.pos', compact(
            'omsetHarian', 'omsetBulanan', 'rekapDP', 'rekapLunas',
            'bulan', 'tahun', 'piutangList', 'totalPiutang', 'omsetLayanan',
            'detailHarian', 'detailBulanan', 'branches', 'selectedBranchId', 
            'selectedBranch', 'isSuperAdmin', 'summaryCabang',
            'omsetHarianBpjs', 'omsetHarianUmum',
            'omsetBulananBpjs', 'omsetBulananUmum'
        ));
    }

    /**
     * Get laporan data via AJAX for specific branch
     */
    public function getData(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->input('branch_id');
        
        // Validasi akses
        if ($user->role !== 'super admin' && $branchId != $user->branch_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $today = Carbon::today();

        $transaksiHarian = Penjualan::where('branch_id', $branchId)
            ->whereDate('created_at', $today)
            ->with('pasien:id_pasien,service_type')
            ->get();

        $bpjsTypes = ['BPJS I', 'BPJS II', 'BPJS III'];
        $omsetHarianBpjs = $transaksiHarian->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);

            if (!in_array($serviceType, $bpjsTypes)) {
                return 0;
            }

            if ($trx->bpjs_default_price > 0) {
                return (float) $trx->bpjs_default_price;
            }

            switch ($serviceType) {
                case 'BPJS I':
                    return BpjsPricingService::BPJS_I_PRICE;
                case 'BPJS II':
                    return BpjsPricingService::BPJS_II_PRICE;
                case 'BPJS III':
                    return BpjsPricingService::BPJS_III_PRICE;
                default:
                    return 0;
            }
        });

        $omsetHarianUmum = $transaksiHarian->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $transaksiBulanan = Penjualan::where('branch_id', $branchId)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->with('pasien:id_pasien,service_type')
            ->get();

        $omsetBulananBpjs = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);

            if (!in_array($serviceType, $bpjsTypes)) {
                return 0;
            }

            if ($trx->bpjs_default_price > 0) {
                return (float) $trx->bpjs_default_price;
            }

            switch ($serviceType) {
                case 'BPJS I':
                    return BpjsPricingService::BPJS_I_PRICE;
                case 'BPJS II':
                    return BpjsPricingService::BPJS_II_PRICE;
                case 'BPJS III':
                    return BpjsPricingService::BPJS_III_PRICE;
                default:
                    return 0;
            }
        });

        $omsetBulananUmum = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $data = [
            'omset_harian' => $omsetHarianBpjs + $omsetHarianUmum,
            'omset_harian_bpjs' => $omsetHarianBpjs,
            'omset_harian_umum' => $omsetHarianUmum,
            'omset_bulanan' => $omsetBulananBpjs + $omsetBulananUmum,
            'omset_bulanan_bpjs' => $omsetBulananBpjs,
            'omset_bulanan_umum' => $omsetBulananUmum,
            'piutang' => Penjualan::where('branch_id', $branchId)
                ->where('status', 'Belum Lunas')
                ->sum('kekurangan'),
            'transaksi_harian' => Penjualan::where('branch_id', $branchId)
                ->whereDate('created_at', $today)
                ->count(),
            'transaksi_bulanan' => Penjualan::where('branch_id', $branchId)
                ->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->count(),
        ];

        return response()->json($data);
    }

    /**
     * Laporan laba/rugi khusus super admin.
     * Menghitung dari transaksi penjualan nyata + beban operasional.
     */
    public function profitLoss(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya untuk super admin.');
        }

        $branches         = Branch::active()->get();
        $selectedBranchId = $request->input('branch_id');
        $bulan            = (int) $request->input('bulan', date('n'));
        $tahun            = (int) $request->input('tahun', date('Y'));

        // ============================================================
        // 1. PENDAPATAN — umum + BPJS (BPJS wajib pakai harga default layanan)
        // ============================================================
        $bpjsTypes = ['BPJS I', 'BPJS II', 'BPJS III'];

        $pendapatanTransactions = Penjualan::with('pasien:id_pasien,service_type')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->get(['id', 'pasien_id', 'total', 'bpjs_default_price', 'pasien_service_type']);

        $jumlahTransaksi = (int) $pendapatanTransactions->count();

        $pendapatanBpjs = (float) $pendapatanTransactions->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);

            if (!in_array($serviceType, $bpjsTypes)) {
                return 0;
            }

            if ((float) $trx->bpjs_default_price > 0) {
                return (float) $trx->bpjs_default_price;
            }

            switch ($serviceType) {
                case 'BPJS I':
                    return BpjsPricingService::BPJS_I_PRICE;
                case 'BPJS II':
                    return BpjsPricingService::BPJS_II_PRICE;
                case 'BPJS III':
                    return BpjsPricingService::BPJS_III_PRICE;
                default:
                    return 0;
            }
        });

        $pendapatanUmum = (float) $pendapatanTransactions->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $pendapatan = $pendapatanUmum + $pendapatanBpjs;

        // ============================================================
        // 2. HPP — harga beli item yang terjual
        //    penjualan_detail JOIN frames / lensa untuk ambil harga_beli
        // ============================================================
        $baseDetailQuery = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->whereMonth('p.created_at', $bulan)
            ->whereYear('p.created_at', $tahun)
            ->when($selectedBranchId, fn($q) => $q->where('p.branch_id', $selectedBranchId));

        // HPP Frame
        $hppFrame = (float) (clone $baseDetailQuery)
            ->where('pd.itemable_type', 'App\\Models\\Frame')
            ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
            ->selectRaw('COALESCE(SUM(pd.quantity * COALESCE(f.harga_beli_frame, 0)), 0) as hpp')
            ->value('hpp');

        // HPP Lensa
        $hppLensa = (float) (clone $baseDetailQuery)
            ->where('pd.itemable_type', 'App\\Models\\Lensa')
            ->join('lensa as l', 'l.id', '=', 'pd.itemable_id')
            ->selectRaw('COALESCE(SUM(pd.quantity * COALESCE(l.harga_beli_lensa, 0)), 0) as hpp')
            ->value('hpp');

        // HPP Aksesoris
        $hppAksesoris = (float) (clone $baseDetailQuery)
            ->where('pd.itemable_type', 'App\\Models\\Aksesoris')
            ->join('aksesoris as a', 'a.id', '=', 'pd.itemable_id')
            ->selectRaw('COALESCE(SUM(pd.quantity * COALESCE(a.harga_beli, 0)), 0) as hpp')
            ->value('hpp');

        $totalHpp   = $hppFrame + $hppLensa + $hppAksesoris;
        $labaKotor  = $pendapatan - $totalHpp;

        // ============================================================
        // 3. BEBAN OPERASIONAL
        // ============================================================

        // 3a. Beban gaji karyawan
        $bebanGajiQuery = DB::table('gaji_karyawans as gk')
            ->join('karyawans as k', 'k.id', '=', 'gk.karyawan_id')
            ->where('gk.bulan', $bulan)
            ->where('gk.tahun', $tahun);
        if ($selectedBranchId) {
            $bebanGajiQuery->where('k.branch_id', $selectedBranchId);
        }
        $bebanGaji = (float) $bebanGajiQuery->sum('gk.total_gaji');

        // 3b. Pengeluaran dari tabel keuangan
        $bebanKeuanganQuery = DB::table('keuangans')
            ->where('jenis', 'pengeluaran')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId));
        $bebanKeuangan = (float) $bebanKeuanganQuery->sum('jumlah');

        // 3c. Pemasukan non-penjualan (dari tabel keuangan)
        $pemasukanLain = (float) DB::table('keuangans')
            ->where('jenis', 'pemasukan')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->sum('jumlah');

        $totalBeban = $bebanGaji + $bebanKeuangan;
        $labaBersih = $labaKotor + $pemasukanLain - $totalBeban;

        // ============================================================
        // 4. RINCIAN BEBAN GAJI PER KARYAWAN (periode ini)
        // ============================================================
        $detailGaji = DB::table('gaji_karyawans as gk')
            ->join('karyawans as k', 'k.id', '=', 'gk.karyawan_id')
            ->leftJoin('branches as b', 'b.id', '=', 'k.branch_id')
            ->where('gk.bulan', $bulan)
            ->where('gk.tahun', $tahun)
            ->when($selectedBranchId, fn($q) => $q->where('k.branch_id', $selectedBranchId))
            ->select('k.nama', 'k.jabatan', 'b.name as cabang',
                'gk.gaji_pokok', 'gk.bonus', 'gk.tunjangan', 'gk.potongan', 'gk.total_gaji')
            ->get();

        // ============================================================
        // 5. RINCIAN PENGELUARAN PER KATEGORI (periode ini)
        // ============================================================
        $detailPengeluaran = DB::table('keuangans')
            ->where('jenis', 'pengeluaran')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        // ============================================================
        // 6. DATA STOK (tetap ditampilkan sebagai info)
        // ============================================================
        $frameStats = DB::table('frames')
            ->where('stok', '>', 0)
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->selectRaw('COUNT(*) as total_item, COALESCE(SUM(stok),0) as total_qty,
                COALESCE(SUM(stok * COALESCE(harga_jual_frame,0)),0) as total_jual,
                COALESCE(SUM(stok * COALESCE(harga_beli_frame,0)),0) as total_beli')
            ->first();

        $lensaStats = DB::table('lensa')
            ->where('stok', '>', 0)
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->selectRaw('COUNT(*) as total_item, COALESCE(SUM(stok),0) as total_qty,
                COALESCE(SUM(stok * COALESCE(harga_jual_lensa,0)),0) as total_jual,
                COALESCE(SUM(stok * COALESCE(harga_beli_lensa,0)),0) as total_beli')
            ->first();

        return view('laporan.profit-loss', compact(
            'branches', 'selectedBranchId', 'bulan', 'tahun',
            // P&L data
            'pendapatan', 'pendapatanUmum', 'pendapatanBpjs', 'jumlahTransaksi',
            'hppFrame', 'hppLensa', 'hppAksesoris', 'totalHpp',
            'labaKotor',
            'bebanGaji', 'bebanKeuangan', 'pemasukanLain', 'totalBeban',
            'labaBersih',
            'detailGaji', 'detailPengeluaran',
            // Stock info
            'frameStats', 'lensaStats'
        ));
    }
} 