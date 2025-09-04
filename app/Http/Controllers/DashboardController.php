<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpenDay;
use App\Models\Branch;
use App\Services\BpjsPricingService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $today = now()->toDateString();

        if ($user->isSuperAdmin()) {
            $branches = Branch::all();
            $selectedBranchId = $request->get('branch_id', $branches->first()->id ?? null);
        } else {
            $branches = null;
            $selectedBranchId = $user->branch_id;
        }

        $openDay = OpenDay::where('branch_id', $selectedBranchId)
            ->where('tanggal', $today)
            ->first();

        $omsetStart = null;
        $omsetEnd = null;
        if ($openDay) {
            $omsetStart = $openDay->created_at;
            // Cari waktu close (updated_at saat is_open=false)
            if (!$openDay->is_open) {
                $omsetEnd = $openDay->updated_at;
            } else {
                $omsetEnd = now();
            }
        }

        // Rekap omset harian seluruh kasir di cabang (untuk admin/superadmin)
        $rekapOmset = null;
        $omsetKasir = null;
        $omsetBpjs = null;
        $omsetUmum = null;
        $transaksiKasir = null;
        $jumlahPasien = null;
        $jumlahLensa = null;
        $jumlahFrame = null;
        $jumlahAksesoris = null;
        $jumlahTransaksiAktif = null;
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Ambil semua transaksi dengan relasi pasien untuk admin
            $adminTransactions = \App\Models\Penjualan::where('branch_id', $selectedBranchId)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->with(['user', 'pasien'])
                ->get();
                
            // Hitung rekap omset per kasir dengan harga default BPJS
            $rekapOmset = $adminTransactions->groupBy('user_id')->map(function($transactions) {
                $firstTransaction = $transactions->first();
                $totalOmset = $transactions->sum(function($transaksi) {
                    $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
                    
                    // Untuk transaksi BPJS, gunakan harga default
                    if (in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                        switch ($serviceType) {
                            case 'BPJS I':
                                return BpjsPricingService::BPJS_I_PRICE;
                            case 'BPJS II':
                                return BpjsPricingService::BPJS_II_PRICE;
                            case 'BPJS III':
                                return BpjsPricingService::BPJS_III_PRICE;
                        }
                    }
                    
                    // Untuk transaksi non-BPJS, gunakan total asli
                    return $transaksi->total;
                });
                
                return (object) [
                    'user_id' => $firstTransaction->user_id,
                    'user' => $firstTransaction->user,
                    'total_omset' => $totalOmset,
                    'jumlah_transaksi' => $transactions->count()
                ];
            })->values();
        } else {
            // Ambil semua transaksi kasir dengan relasi pasien
            $kasirTransactions = \App\Models\Penjualan::where('branch_id', $selectedBranchId)
                ->where('user_id', $user->id)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->with('pasien')
                ->get();
                
            // Hitung omset kasir dengan harga default BPJS
            $omsetKasir = $kasirTransactions->sum(function($transaksi) {
                $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
                
                // Untuk transaksi BPJS, gunakan harga default
                if (in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III'])) {
                    switch ($serviceType) {
                        case 'BPJS I':
                            return BpjsPricingService::BPJS_I_PRICE;
                        case 'BPJS II':
                            return BpjsPricingService::BPJS_II_PRICE;
                        case 'BPJS III':
                            return BpjsPricingService::BPJS_III_PRICE;
                    }
                }
                
                // Untuk transaksi non-BPJS, gunakan total asli
                return $transaksi->total;
            });
            
            // Hitung omset BPJS dengan harga default
            $bpjsTransactions = $kasirTransactions->filter(function($transaksi) {
                $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
                return in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III']);
            });
            
            $omsetBpjs = $bpjsTransactions->sum(function($transaksi) {
                $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
                
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
            
            // Hitung omset umum (non-BPJS) dengan harga asli
            $umumTransactions = $kasirTransactions->filter(function($transaksi) {
                $serviceType = $transaksi->pasien->service_type ?? 'UMUM';
                return !in_array($serviceType, ['BPJS I', 'BPJS II', 'BPJS III']);
            });
            
            $omsetUmum = $umumTransactions->sum('total');
            
            // Debug logging untuk membantu troubleshooting
            \Log::info('DashboardController - Omset Kasir Data:', [
                'user_id' => $user->id,
                'branch_id' => $selectedBranchId,
                'omset_kasir' => $omsetKasir,
                'omset_bpjs' => $omsetBpjs,
                'omset_umum' => $omsetUmum,
                'total_kasir_transactions' => $kasirTransactions->count(),
                'bpjs_transactions_count' => $bpjsTransactions->count(),
                'umum_transactions_count' => $umumTransactions->count(),
            ]);
            
            $transaksiKasir = \App\Models\Penjualan::where('branch_id', $selectedBranchId)
                ->where('user_id', $user->id)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->with('pasien')
                ->orderBy('created_at', 'desc')
                ->get();
            
                    // Debug: Log data transaksi untuk troubleshooting
        \Log::info('DashboardController - Transaksi Kasir Data:', [
            'user_id' => $user->id,
            'branch_id' => $selectedBranchId,
            'total_transactions' => $transaksiKasir->count(),
            'sample_transaction' => $transaksiKasir->first() ? [
                'id' => $transaksiKasir->first()->id,
                'kode_penjualan' => $transaksiKasir->first()->kode_penjualan,
                'pasien_id' => $transaksiKasir->first()->pasien_id,
                'pasien_data' => $transaksiKasir->first()->pasien ? [
                    'id' => $transaksiKasir->first()->pasien->id_pasien,
                    'nama' => $transaksiKasir->first()->pasien->nama_pasien,
                    'service_type' => $transaksiKasir->first()->pasien->service_type,
                ] : null,
            ] : null,
        ]);
            $jumlahPasien = \App\Models\Pasien::count();
            $jumlahLensa = \App\Models\Lensa::where('branch_id', $selectedBranchId)->count();
            $jumlahFrame = \App\Models\Frame::where('branch_id', $selectedBranchId)->count();
            $jumlahAksesoris = \App\Models\Aksesoris::where('branch_id', $selectedBranchId)->count();
            $jumlahTransaksiAktif = \App\Models\Penjualan::where('branch_id', $selectedBranchId)
                ->where('user_id', $user->id)
                ->where('status_pengerjaan', '!=', 'Sudah Diambil')
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->count();
        }

        // Data summary
        $jumlahFrame = \App\Models\Frame::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->count();
        $jumlahLensa = \App\Models\Lensa::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->count();
        $jumlahAksesoris = \App\Models\Aksesoris::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->count();
        $jumlahPasien = \App\Models\Pasien::count();
        $jumlahTransaksiAktif = \App\Models\Penjualan::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)
            ->whereDate('created_at', now())
            ->count();

        // Low stock data (stok < 5)
        $batasStok = 5;
        $lowStockLensa = \App\Models\Lensa::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)
            ->where('stok', '<', $batasStok)
            ->with('branch')
            ->get();
        $lowStockFrame = \App\Models\Frame::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)
            ->where('stok', '<', $batasStok)
            ->with('branch')
            ->get();
        $lowStockAksesoris = \App\Models\Aksesoris::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)
            ->where('stok', '<', $batasStok)
            ->with('branch')
            ->get();

        // Data untuk passet bantu - transaksi yang menunggu pengerjaan
        $transaksiMenungguPengerjaan = null;
        if ($user->isPassetBantu()) {
            $transaksiMenungguPengerjaan = \App\Models\Penjualan::whereIn('status_pengerjaan', ['Menunggu Pengerjaan', 'Sedang Dikerjakan'])
                ->count();
        }

        // Data untuk passet bantu - transaksi yang sudah dikerjakan user ini di bulan ini
        $transaksiSelesaiBulanIni = null;
        if ($user->isPassetBantu()) {
            $transaksiSelesaiBulanIni = \App\Models\Penjualan::where('passet_by_user_id', $user->id)
                ->where('status_pengerjaan', 'Selesai Dikerjakan')
                ->whereMonth('waktu_selesai_dikerjakan', now()->month)
                ->whereYear('waktu_selesai_dikerjakan', now()->year)
                ->count();
        }

        // Data detail untuk modal
        $detailFrame = \App\Models\Frame::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->limit(100)->get();
        $detailLensa = \App\Models\Lensa::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->limit(100)->get();
        $detailAksesoris = \App\Models\Aksesoris::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->limit(100)->get();
        $detailPasien = \App\Models\Pasien::limit(100)->get();
        $detailTransaksiAktif = \App\Models\Penjualan::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)
            ->whereDate('created_at', now())
            ->with(['pasien', 'dokter'])
            ->limit(100)
            ->get()
            ->map(function($trx) {
                // Ensure all required fields are present
                return (object) [
                    'id' => $trx->id,
                    'kode_penjualan' => $trx->kode_penjualan ?? '-',
                    'total' => $trx->total ?? 0,
                    'status' => $trx->status ?? '-',
                    'status_pengerjaan' => $trx->status_pengerjaan ?? '-',
                    'created_at' => $trx->created_at,
                    'dokter_manual' => $trx->dokter_manual ?? '-',
                    'pasien' => $trx->pasien ? (object) [
                        'nama_pasien' => $trx->pasien->nama_pasien ?? '-',
                        'service_type' => $trx->pasien->service_type ?? '-'
                    ] : (object) ['nama_pasien' => '-', 'service_type' => '-'],
                    'dokter' => $trx->dokter ? (object) [
                        'nama' => $trx->dokter->nama ?? '-'
                    ] : null
                ];
            });
            
        // Debug logging
        \Log::info('Dashboard data for user: ' . $user->id, [
            'frame_count' => $detailFrame->count(),
            'lensa_count' => $detailLensa->count(),
            'pasien_count' => $detailPasien->count(),
            'transaksi_count' => $detailTransaksiAktif->count(),
            'selected_branch_id' => $selectedBranchId
        ]);

        // Data untuk grafik penjualan (hanya untuk super admin)
        $chartData = null;
        if ($user->isSuperAdmin()) {
            $chartData = $this->getChartDataPrivate($selectedBranchId);
        }

        return view('home', compact(
            'branches', 'selectedBranchId', 'openDay',
            'jumlahFrame', 'jumlahLensa', 'jumlahAksesoris', 'jumlahPasien', 'jumlahTransaksiAktif',
            'detailFrame', 'detailLensa', 'detailAksesoris', 'detailPasien', 'detailTransaksiAktif',
            'rekapOmset', 'omsetKasir', 'omsetBpjs', 'omsetUmum', 'transaksiKasir', 'chartData',
            'lowStockLensa', 'lowStockFrame', 'lowStockAksesoris', 'batasStok',
            'transaksiMenungguPengerjaan', 'transaksiSelesaiBulanIni'
        ));
    }

    public function openDay(Request $request)
    {
        $user = auth()->user();
        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Hanya super admin dan admin yang boleh melakukan open day.');
        }
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $branchId = $user->isSuperAdmin()
            ? $request->input('branch_id')
            : $user->branch_id;

        $openDay = OpenDay::where('branch_id', $branchId)->where('tanggal', $today)->first();
        if ($openDay) {
            $openDay->is_open = true;
            $openDay->updated_at = Carbon::now('Asia/Jakarta');
            $openDay->save();
        } else {
            OpenDay::create([
                'branch_id' => $branchId,
                'tanggal' => $today,
                'is_open' => true,
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
        return back()->with('success', 'Kasir dibuka untuk cabang ini!');
    }

    public function closeDay(Request $request)
    {
        $user = auth()->user();
        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Hanya super admin dan admin yang boleh melakukan close day.');
        }
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $branchId = $user->isSuperAdmin()
            ? $request->input('branch_id')
            : $user->branch_id;

        $openDay = OpenDay::where('branch_id', $branchId)->where('tanggal', $today)->first();
        if ($openDay) {
            $openDay->is_open = false;
            $openDay->updated_at = Carbon::now('Asia/Jakarta');
            $openDay->save();
            return back()->with('success', 'Kasir ditutup untuk cabang ini!');
        } else {
            return back()->with('error', 'Gagal close day: Data open day tidak ditemukan untuk cabang ini.');
        }
    }

    public function getChartData(Request $request)
    {
        $user = auth()->user();
        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Unauthorized');
        }

        $branchId = $request->get('branch_id');
        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            $branchId = $user->branch_id;
        }

        $chartData = $this->getChartDataPrivate($branchId);
        return response()->json($chartData);
    }

    private function getChartDataPrivate($branchId = null)
    {
        // Data penjualan 7 hari terakhir
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7Days->push($date->format('Y-m-d'));
        }

        // Data penjualan per hari
        $dailySales = \App\Models\Penjualan::when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total_sales, COUNT(*) as total_transactions')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Data penjualan BPJS vs Umum
        $bpjsVsUmum = \App\Models\Penjualan::when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('
                CASE 
                    WHEN pasien_service_type IS NOT NULL THEN "BPJS"
                    ELSE "Umum"
                END as transaction_type,
                SUM(total) as total_sales,
                COUNT(*) as total_transactions
            ')
            ->groupBy('transaction_type')
            ->get();

        // Data penjualan per cabang (jika tidak ada filter cabang)
        $branchSales = null;
        if (!$branchId) {
            $branchSales = \App\Models\Penjualan::whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                ->join('branches', 'penjualan.branch_id', '=', 'branches.id')
                ->selectRaw('branches.name as branch_name, SUM(penjualan.total) as total_sales, COUNT(*) as total_transactions')
                ->groupBy('branches.id', 'branches.name')
                ->orderBy('total_sales', 'desc')
                ->limit(10)
                ->get();
        }

        // Data status transaksi BPJS
        $bpjsStatus = \App\Models\Penjualan::when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereNotNull('pasien_service_type')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('
                transaction_status,
                COUNT(*) as total_transactions,
                SUM(total) as total_sales
            ')
            ->groupBy('transaction_status')
            ->get();

        // Data penjualan bulanan untuk Cabang 1 dan Cabang 2 (12 bulan terakhir)
        $monthlySalesBranch1 = [];
        $monthlySalesBranch2 = [];
        $months = [];

        // Get branch IDs for "Cabang 1" and "Cabang 2"
        $branch1 = \App\Models\Branch::where('name', 'Cabang 1')->first();
        $branch2 = \App\Models\Branch::where('name', 'Cabang 2')->first();

        if ($branch1 && $branch2) {
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthYear = $date->format('Y-m');
                $months[] = $date->format('M Y'); // e.g., Jan 2023

                $sales1 = \App\Models\Penjualan::where('branch_id', $branch1->id)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total');
                $monthlySalesBranch1[] = $sales1;

                $sales2 = \App\Models\Penjualan::where('branch_id', $branch2->id)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total');
                $monthlySalesBranch2[] = $sales2;
            }
        }

        return [
            'daily_sales' => $dailySales,
            'last_7_days' => $last7Days,
            'bpjs_vs_umum' => $bpjsVsUmum,
            'branch_sales' => $branchSales,
            'bpjs_status' => $bpjsStatus,
            'monthly_sales_branches' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'Cabang 1',
                        'data' => $monthlySalesBranch1,
                        'backgroundColor' => 'rgba(60, 141, 188, 0.7)',
                        'borderColor' => 'rgba(60, 141, 188, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Cabang 2',
                        'data' => $monthlySalesBranch2,
                        'backgroundColor' => 'rgba(0, 166, 90, 0.7)',
                        'borderColor' => 'rgba(0, 166, 90, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ]
        ];
    }
} 