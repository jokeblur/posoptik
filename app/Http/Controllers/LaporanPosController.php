<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Branch;
use App\Models\Frame;
use App\Models\Lensa;
use App\Services\BpjsPricingService;
use App\Exports\LaporanPosMonthlyFormatExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPosController extends Controller
{
    private function getBpjsDefaultValue($trx, array $bpjsTypes): float
    {
        $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);

        if (!in_array($serviceType, $bpjsTypes, true)) {
            return 0;
        }

        $defaultPrice = (float) ($trx->bpjs_default_price ?? 0);
        if ($defaultPrice > 0) {
            return $defaultPrice;
        }

        switch ($serviceType) {
            case 'BPJS I':
                return (float) BpjsPricingService::BPJS_I_PRICE;
            case 'BPJS II':
                return (float) BpjsPricingService::BPJS_II_PRICE;
            case 'BPJS III':
                return (float) BpjsPricingService::BPJS_III_PRICE;
            default:
                return 0;
        }
    }

    private function getBpjsAdditionalCostValue($trx): float
    {
        return max(0, (float) ($trx->total_additional_cost ?? 0));
    }

    private function getBpjsOmsetValue($trx, array $bpjsTypes): float
    {
        return $this->getBpjsDefaultValue($trx, $bpjsTypes) + $this->getBpjsAdditionalCostValue($trx);
    }

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
            return $this->getBpjsDefaultValue($trx, $bpjsTypes);
        });

        $totalTambahanBpjsHarian = $transaksiHarian->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            if (!in_array($serviceType, $bpjsTypes, true)) {
                return 0;
            }

            return $this->getBpjsAdditionalCostValue($trx);
        });

        $omsetHarianUmum = $transaksiHarian->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $omsetHarian = $omsetHarianBpjs + $omsetHarianUmum + $totalTambahanBpjsHarian;

        // Omset Bulanan (dipisah BPJS vs Umum, mengikuti filter bulan/tahun)
        $bulan = $request->input('bulan', $today->format('m'));
        $tahun = $request->input('tahun', $today->format('Y'));
        $transaksiBulanan = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->with('pasien:id_pasien,service_type')
            ->get();

        $omsetBulananBpjs = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            return $this->getBpjsDefaultValue($trx, $bpjsTypes);
        });

        $totalTambahanBpjsBulanan = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            if (!in_array($serviceType, $bpjsTypes, true)) {
                return 0;
            }

            return $this->getBpjsAdditionalCostValue($trx);
        });

        $omsetBulananUmum = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $omsetBulanan = $omsetBulananBpjs + $omsetBulananUmum + $totalTambahanBpjsBulanan;

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
                // Untuk layanan BPJS, gunakan default BPJS + total tambahan biaya BPJS
                $omsetLayanan[$layanan] = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereHas('pasien', function($q) use ($layanan) {
                        $q->where('service_type', $layanan);
                    })
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->selectRaw('COALESCE(SUM(COALESCE(bpjs_default_price, 0) + COALESCE(total_additional_cost, 0)), 0) as total')
                    ->value('total');
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
            ->with(['pasien', 'branch', 'details'])
            ->get();

        $detailHarian->each(function ($trx) {
            $trx->total_item = (int) $trx->details->sum('quantity');
            $trx->item_aksesoris = (int) $trx->details
                ->where('itemable_type', 'App\\Models\\Aksesoris')
                ->sum('quantity');
            $trx->nilai_aksesoris = (float) $trx->details
                ->where('itemable_type', 'App\\Models\\Aksesoris')
                ->sum('subtotal');
        });
            
        // Detail transaksi bulanan
        $detailBulanan = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->with(['pasien', 'branch', 'details'])
            ->get();

        $detailBulanan->each(function ($trx) {
            $trx->total_item = (int) $trx->details->sum('quantity');
            $trx->item_aksesoris = (int) $trx->details
                ->where('itemable_type', 'App\\Models\\Aksesoris')
                ->sum('quantity');
            $trx->nilai_aksesoris = (float) $trx->details
                ->where('itemable_type', 'App\\Models\\Aksesoris')
                ->sum('subtotal');
        });

        // Ringkasan aksesoris: transaksi, jumlah item, omset, modal
        $aksesorisHarian = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->leftJoin('aksesoris as a', 'a.id', '=', 'pd.itemable_id')
            ->when($branchId, fn($q) => $q->where('p.branch_id', $branchId))
            ->whereDate('p.created_at', $today)
            ->where('pd.itemable_type', 'App\\Models\\Aksesoris')
            ->selectRaw('COUNT(DISTINCT p.id) as total_transaksi, COALESCE(SUM(pd.quantity),0) as total_item, COALESCE(SUM(pd.subtotal),0) as total_omset, COALESCE(SUM(pd.quantity * COALESCE(a.harga_beli,0)),0) as total_modal')
            ->first();

        $aksesorisBulanan = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->leftJoin('aksesoris as a', 'a.id', '=', 'pd.itemable_id')
            ->when($branchId, fn($q) => $q->where('p.branch_id', $branchId))
            ->whereMonth('p.created_at', $bulan)
            ->whereYear('p.created_at', $tahun)
            ->where('pd.itemable_type', 'App\\Models\\Aksesoris')
            ->selectRaw('COUNT(DISTINCT p.id) as total_transaksi, COALESCE(SUM(pd.quantity),0) as total_item, COALESCE(SUM(pd.subtotal),0) as total_omset, COALESCE(SUM(pd.quantity * COALESCE(a.harga_beli,0)),0) as total_modal')
            ->first();

        $labaKotorAksesorisHarian = (float) (($aksesorisHarian->total_omset ?? 0) - ($aksesorisHarian->total_modal ?? 0));
        $labaKotorAksesorisBulanan = (float) (($aksesorisBulanan->total_omset ?? 0) - ($aksesorisBulanan->total_modal ?? 0));

        $detailAksesorisHarian = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->leftJoin('aksesoris as a', 'a.id', '=', 'pd.itemable_id')
            ->when($branchId, fn($q) => $q->where('p.branch_id', $branchId))
            ->whereDate('p.created_at', $today)
            ->where('pd.itemable_type', 'App\\Models\\Aksesoris')
            ->selectRaw('COALESCE(a.nama_produk, "-") as nama_produk, COUNT(DISTINCT p.id) as total_transaksi, COALESCE(SUM(pd.quantity),0) as total_qty, COALESCE(SUM(pd.subtotal),0) as total_omset, COALESCE(SUM(pd.quantity * COALESCE(a.harga_beli,0)),0) as total_modal, COALESCE(SUM(pd.subtotal - (pd.quantity * COALESCE(a.harga_beli,0))),0) as laba_kotor')
            ->groupBy('a.id', 'a.nama_produk')
            ->orderByDesc('total_qty')
            ->get();

        $detailAksesorisBulanan = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->leftJoin('aksesoris as a', 'a.id', '=', 'pd.itemable_id')
            ->when($branchId, fn($q) => $q->where('p.branch_id', $branchId))
            ->whereMonth('p.created_at', $bulan)
            ->whereYear('p.created_at', $tahun)
            ->where('pd.itemable_type', 'App\\Models\\Aksesoris')
            ->selectRaw('COALESCE(a.nama_produk, "-") as nama_produk, COUNT(DISTINCT p.id) as total_transaksi, COALESCE(SUM(pd.quantity),0) as total_qty, COALESCE(SUM(pd.subtotal),0) as total_omset, COALESCE(SUM(pd.quantity * COALESCE(a.harga_beli,0)),0) as total_modal, COALESCE(SUM(pd.subtotal - (pd.quantity * COALESCE(a.harga_beli,0))),0) as laba_kotor')
            ->groupBy('a.id', 'a.nama_produk')
            ->orderByDesc('total_qty')
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
            'omsetBulananBpjs', 'omsetBulananUmum',
            'totalTambahanBpjsHarian', 'totalTambahanBpjsBulanan',
            'aksesorisHarian', 'aksesorisBulanan',
            'labaKotorAksesorisHarian', 'labaKotorAksesorisBulanan',
            'detailAksesorisHarian', 'detailAksesorisBulanan'
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
            return $this->getBpjsDefaultValue($trx, $bpjsTypes);
        });

        $totalTambahanBpjsHarian = $transaksiHarian->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            if (!in_array($serviceType, $bpjsTypes, true)) {
                return 0;
            }

            return $this->getBpjsAdditionalCostValue($trx);
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
            return $this->getBpjsDefaultValue($trx, $bpjsTypes);
        });

        $totalTambahanBpjsBulanan = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            if (!in_array($serviceType, $bpjsTypes, true)) {
                return 0;
            }

            return $this->getBpjsAdditionalCostValue($trx);
        });

        $omsetBulananUmum = $transaksiBulanan->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $data = [
            'omset_harian' => $omsetHarianBpjs + $omsetHarianUmum + $totalTambahanBpjsHarian,
            'omset_harian_bpjs' => $omsetHarianBpjs,
            'total_tambahan_bpjs_harian' => $totalTambahanBpjsHarian,
            'omset_harian_umum' => $omsetHarianUmum,
            'omset_bulanan' => $omsetBulananBpjs + $omsetBulananUmum + $totalTambahanBpjsBulanan,
            'omset_bulanan_bpjs' => $omsetBulananBpjs,
            'total_tambahan_bpjs_bulanan' => $totalTambahanBpjsBulanan,
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
            'aksesoris_harian' => DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                ->leftJoin('aksesoris as a', 'a.id', '=', 'pd.itemable_id')
                ->where('p.branch_id', $branchId)
                ->whereDate('p.created_at', $today)
                ->where('pd.itemable_type', 'App\\Models\\Aksesoris')
                ->selectRaw('COUNT(DISTINCT p.id) as total_transaksi, COALESCE(SUM(pd.quantity),0) as total_item, COALESCE(SUM(pd.subtotal),0) as total_omset, COALESCE(SUM(pd.quantity * COALESCE(a.harga_beli,0)),0) as total_modal')
                ->first(),
            'aksesoris_bulanan' => DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                ->leftJoin('aksesoris as a', 'a.id', '=', 'pd.itemable_id')
                ->where('p.branch_id', $branchId)
                ->whereMonth('p.created_at', $bulan)
                ->whereYear('p.created_at', $tahun)
                ->where('pd.itemable_type', 'App\\Models\\Aksesoris')
                ->selectRaw('COUNT(DISTINCT p.id) as total_transaksi, COALESCE(SUM(pd.quantity),0) as total_item, COALESCE(SUM(pd.subtotal),0) as total_omset, COALESCE(SUM(pd.quantity * COALESCE(a.harga_beli,0)),0) as total_modal')
                ->first(),
        ];

        return response()->json($data);
    }

    /**
     * Export laporan POS bulanan dengan format rekap manual (sesuai template operasional).
     */
    public function exportMonthlyFormat(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super admin';

        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        if ($bulan < 1 || $bulan > 12) {
            return back()->with('error', 'Bulan tidak valid.');
        }

        $branchId = $request->input('branch_id');
        if (!$isSuperAdmin) {
            $branchId = $user->branch_id;
        } elseif (!empty($branchId) && !Branch::where('id', $branchId)->exists()) {
            return back()->with('error', 'Cabang tidak ditemukan.');
        }

        $query = Penjualan::query()
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->with([
                'branch',
                'dokter:id_dokter,nama_dokter',
                'pasien:id_pasien,nama_pasien,alamat,nohp,service_type',
                'pasien.prescriptions',
                'pasien.prescriptions.dokter:id_dokter,nama_dokter',
                'details.itemable',
            ])
            ->orderBy('created_at', 'asc');

        $transactions = $query->get();

        $bulanLabel = strtoupper(Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y'));
        $branchLabel = 'SEMUA CABANG';

        if ($branchId) {
            $branchLabel = strtoupper((string) optional(Branch::find($branchId))->name ?: 'CABANG TIDAK DIKETAHUI');
        } elseif (!$isSuperAdmin) {
            $branchLabel = strtoupper((string) optional($user->branch)->name ?: 'CABANG');
        }

        $filename = 'laporan_pos_format_' . sprintf('%02d', $bulan) . '_' . $tahun . '.xlsx';

        return Excel::download(
            new LaporanPosMonthlyFormatExport($transactions, $bulanLabel, $branchLabel),
            $filename
        );
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
            ->get(['id', 'pasien_id', 'total', 'bpjs_default_price', 'bpjs_manual_additional_cost', 'pasien_service_type']);

        $jumlahTransaksi = (int) $pendapatanTransactions->count();

        $pendapatanBpjs = (float) $pendapatanTransactions->sum(function ($trx) use ($bpjsTypes) {
            return $this->getBpjsDefaultValue($trx, $bpjsTypes);
        });

        $pendapatanTambahanBpjs = (float) $pendapatanTransactions->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            if (!in_array($serviceType, $bpjsTypes, true)) {
                return 0;
            }

            return $this->getBpjsAdditionalCostValue($trx);
        });

        $pendapatanUmum = (float) $pendapatanTransactions->sum(function ($trx) use ($bpjsTypes) {
            $serviceType = $trx->pasien_service_type ?? ($trx->pasien->service_type ?? null);
            return in_array($serviceType, $bpjsTypes) ? 0 : (float) $trx->total;
        });

        $pendapatan = $pendapatanUmum + $pendapatanBpjs + $pendapatanTambahanBpjs;

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
            'pendapatan', 'pendapatanUmum', 'pendapatanBpjs', 'pendapatanTambahanBpjs', 'jumlahTransaksi',
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