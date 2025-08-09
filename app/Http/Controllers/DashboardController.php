<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpenDay;
use App\Models\Branch;
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
            $rekapOmset = \App\Models\Transaksi::where('branch_id', $selectedBranchId)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->selectRaw('user_id, SUM(total) as total_omset, COUNT(*) as jumlah_transaksi')
                ->groupBy('user_id')
                ->with('user')
                ->get();
        } else {
            $omsetKasir = \App\Models\Transaksi::where('branch_id', $selectedBranchId)
                ->where('user_id', $user->id)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->sum('total');
            $omsetBpjs = \App\Models\Transaksi::where('branch_id', $selectedBranchId)
                ->where('user_id', $user->id)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->whereHas('pasien', function($q) {
                    $q->where('service_type', 'like', 'BPJS%');
                })->sum('total');
            $omsetUmum = \App\Models\Transaksi::where('branch_id', $selectedBranchId)
                ->where('user_id', $user->id)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->whereHas('pasien', function($q) {
                    $q->where('service_type', 'UMUM');
                })->sum('total');
            $transaksiKasir = \App\Models\Transaksi::where('branch_id', $selectedBranchId)
                ->where('user_id', $user->id)
                ->when($omsetStart, fn($q) => $q->where('created_at', '>=', $omsetStart))
                ->when($omsetEnd, fn($q) => $q->where('created_at', '<=', $omsetEnd))
                ->orderBy('created_at', 'desc')
                ->get();
            $jumlahPasien = \App\Models\Pasien::count();
            $jumlahLensa = \App\Models\Lensa::where('branch_id', $selectedBranchId)->count();
            $jumlahFrame = \App\Models\Frame::where('branch_id', $selectedBranchId)->count();
            $jumlahAksesoris = \App\Models\Aksesoris::where('branch_id', $selectedBranchId)->count();
            $jumlahTransaksiAktif = \App\Models\Transaksi::where('branch_id', $selectedBranchId)
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
        $jumlahTransaksiAktif = \App\Models\Transaksi::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)
            ->whereDate('created_at', now())
            ->count();

        // Data detail untuk modal
        $detailFrame = \App\Models\Frame::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->limit(100)->get();
        $detailLensa = \App\Models\Lensa::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->limit(100)->get();
        $detailAksesoris = \App\Models\Aksesoris::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)->limit(100)->get();
        $detailPasien = \App\Models\Pasien::limit(100)->get();
        $detailTransaksiAktif = \App\Models\Transaksi::when($user->isSuperAdmin() ? null : $selectedBranchId, fn($q, $branchId) => $branchId ? $q->where('branch_id', $branchId) : $q)
            ->whereDate('created_at', now())
            ->limit(100)->get();

        // Data untuk grafik penjualan (hanya untuk super admin)
        $chartData = null;
        if ($user->isSuperAdmin()) {
            $chartData = $this->getChartDataPrivate($selectedBranchId);
        }

        return view('home', compact(
            'branches', 'selectedBranchId', 'openDay',
            'jumlahFrame', 'jumlahLensa', 'jumlahAksesoris', 'jumlahPasien', 'jumlahTransaksiAktif',
            'detailFrame', 'detailLensa', 'detailAksesoris', 'detailPasien', 'detailTransaksiAktif',
            'rekapOmset', 'omsetKasir', 'omsetBpjs', 'omsetUmum', 'transaksiKasir', 'chartData'
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
        $dailySales = \App\Models\Transaksi::when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total_sales, COUNT(*) as total_transactions')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Data penjualan BPJS vs Umum
        $bpjsVsUmum = \App\Models\Transaksi::when($branchId, function($query) use ($branchId) {
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
            $branchSales = \App\Models\Transaksi::whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                ->join('branches', 'penjualan.branch_id', '=', 'branches.id')
                ->selectRaw('branches.name as branch_name, SUM(penjualan.total) as total_sales, COUNT(*) as total_transactions')
                ->groupBy('branches.id', 'branches.name')
                ->orderBy('total_sales', 'desc')
                ->limit(10)
                ->get();
        }

        // Data status transaksi BPJS
        $bpjsStatus = \App\Models\Transaksi::when($branchId, function($query) use ($branchId) {
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

        return [
            'daily_sales' => $dailySales,
            'last_7_days' => $last7Days,
            'bpjs_vs_umum' => $bpjsVsUmum,
            'branch_sales' => $branchSales,
            'bpjs_status' => $bpjsStatus
        ];
    }
} 