<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Branch;
use Carbon\Carbon;

class LaporanPosController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super admin';
        
        // Get all branches for super admin, only user's branch for others
        $branches = $isSuperAdmin ? Branch::active()->get() : Branch::where('id', $user->branch_id)->get();
        
        // Get selected branch from request or default to user's branch
        $selectedBranchId = $request->input('branch_id', $isSuperAdmin ? null : $user->branch_id);
        $selectedBranch = $selectedBranchId ? Branch::find($selectedBranchId) : null;

        // Filter data berdasarkan cabang
        $branchId = $selectedBranchId;

        // Omset Harian
        $today = Carbon::today();
        $omsetHarian = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', $today)
            ->sum('total');

        // Omset Bulanan
        $bulan = $request->input('bulan', $today->format('m'));
        $tahun = $request->input('tahun', $today->format('Y'));
        $omsetBulanan = Penjualan::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->sum('total');

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
            'selectedBranch', 'isSuperAdmin', 'summaryCabang'
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

        $data = [
            'omset_harian' => Penjualan::where('branch_id', $branchId)
                ->whereDate('created_at', $today)
                ->sum('total'),
            'omset_bulanan' => Penjualan::where('branch_id', $branchId)
                ->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)
                ->sum('total'),
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
} 