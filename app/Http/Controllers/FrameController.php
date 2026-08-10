<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Frame;
use App\Models\Sales;
use App\Imports\FrameImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FrameExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FrameController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isKasir())) {
                return $next($request);
            }
            // Kasir boleh menambah dan restok data frame.
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        })->only(['store', 'restock', 'update']);

        $this->middleware(function ($request, $next) {
            if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())) {
                return $next($request);
            }

            abort(403, 'Hanya admin dan super admin yang dapat menghapus data frame.');
        })->only(['destroy', 'bulkDelete']);

        $this->middleware(function ($request, $next) {
            if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())) {
                return $next($request);
            }

            abort(403, 'Hanya admin dan super admin yang dapat mengakses analisa frame.');
        })->only(['analysis']);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $sales = Sales::all()->pluck('nama_sales', 'id_sales');
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        $batasStok = 2;
        $lowStockFrame = Frame::accessibleByUser($user)
            ->where('stok', '<=', $batasStok)
            ->with('branch')
            ->orderBy('stok', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $frameAnalysisStart = now()->subDays(30)->startOfDay();
        $frameAnalysisEnd = now()->endOfDay();
        $frameAnalysisPeriodLabel = '30 Hari Terakhir';

        $frameAnalysisBaseQuery = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
            ->leftJoin('sales as s', 's.id_sales', '=', 'f.id_sales')
            ->where('pd.itemable_type', Frame::class)
            ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd]);

        $patientServiceTypeSql = "(SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1)";
        $bpjsServiceTypeSql = "LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF($patientServiceTypeSql, ''), '')))";
        $jenisFrameNormalizedSql = "CASE
            WHEN LOWER(TRIM(COALESCE(NULLIF(f.jenis_frame, ''), ''))) = 'umum' THEN 'Umum'
            WHEN LOWER(TRIM(COALESCE(NULLIF(f.jenis_frame, ''), ''))) LIKE 'bpjs%' THEN UPPER(TRIM(COALESCE(NULLIF(f.jenis_frame, ''), '-')))
            ELSE COALESCE(NULLIF(TRIM(f.jenis_frame), ''), '-')
        END";

        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            $frameAnalysisBaseQuery->where('p.branch_id', $user->branch_id);
        }

        $frameAnalysisBpjsSummary = (clone $frameAnalysisBaseQuery)
            ->whereRaw($bpjsServiceTypeSql . ' LIKE ?', ['%bpjs%'])
            ->selectRaw('COALESCE(SUM(pd.quantity), 0) as total_qty')
            ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
            ->first();

        $frameAnalysisUmumSummary = (clone $frameAnalysisBaseQuery)
            ->where(function ($query) {
                $query->whereRaw("LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF((SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1), ''), ''))) NOT LIKE ?", ['%bpjs%']);
            })
            ->selectRaw('COALESCE(SUM(pd.quantity), 0) as total_qty')
            ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
            ->first();

        $frameAnalysisUmumByBranch = collect();
        $branchNames = [
            'Optik Melati 1' => ['optik melati cabang 1', 'optik melati 1'],
            'Optik Melati 2' => ['optik melati cabang 2', 'optik melati 2'],
        ];

        foreach ($branchNames as $branchLabel => $branchKeywords) {
            $branchQuery = DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
                ->join('branches as b', 'b.id', '=', 'p.branch_id')
                ->where('pd.itemable_type', Frame::class)
                ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd])
                ->where(function ($query) use ($branchKeywords) {
                    foreach ($branchKeywords as $keyword) {
                        $query->orWhereRaw('LOWER(TRIM(COALESCE(b.name, ""))) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    }
                })
                ->where(function ($query) {
                    $query->whereRaw("LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF((SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1), ''), ''))) NOT LIKE ?", ['%bpjs%']);
                })
                ->whereRaw("LOWER(TRIM(COALESCE(NULLIF(f.jenis_frame, ''), '-'))) = ?", ['umum'])
                ->selectRaw('COALESCE(SUM(pd.quantity), 0) as total_qty')
                ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
                ->first();

            $branchSummary = $branchQuery ? (object) array_merge((array) $branchQuery, ['branch_name' => $branchLabel]) : (object) [
                'branch_name' => $branchLabel,
                'total_qty' => 0,
                'total_transaksi' => 0,
            ];

            $frameAnalysisUmumByBranch->push($branchSummary);
        }

        $frameAnalysisBpjs = (clone $frameAnalysisBaseQuery)
            ->whereRaw($bpjsServiceTypeSql . ' LIKE ?', ['%bpjs%'])
            ->selectRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') as merk_frame")
            ->selectRaw($jenisFrameNormalizedSql . ' as jenis_frame')
            ->selectRaw("COALESCE(NULLIF(TRIM(s.nama_sales), ''), '-') as sales_name")
            ->selectRaw('SUM(COALESCE(pd.quantity, 0)) as total_qty')
            ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
            ->groupBy('merk_frame', 'jenis_frame', 'sales_name')
            ->havingRaw('SUM(COALESCE(pd.quantity, 0)) > 2')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $frameAnalysisBpjs = $frameAnalysisBpjs->map(function ($item) use ($frameAnalysisStart, $frameAnalysisEnd, $selectedSalesId, $bpjsServiceTypeSql) {
            $item->kode_frame_details = DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
                ->where('pd.itemable_type', Frame::class)
                ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd])
                ->whereNotNull('p.pasien_service_type')
                ->whereRaw('LOWER(TRIM(COALESCE(p.pasien_service_type, ""))) LIKE ?', ['%bpjs%'])
                ->whereRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') = ?", [$item->merk_frame])
                ->whereRaw("COALESCE(NULLIF(TRIM(f.jenis_frame), ''), '-') = ?", [$item->jenis_frame])
                ->whereNotNull('f.kode_frame')
                ->selectRaw('f.kode_frame as kode_frame')
                ->selectRaw('SUM(COALESCE(pd.quantity, 0)) as total_qty')
                ->groupBy('f.kode_frame')
                ->orderByDesc('total_qty')
                ->orderBy('f.kode_frame')
                ->get()
                ->map(function ($detail) {
                    return [
                        'kode_frame' => $detail->kode_frame,
                        'total_qty' => (int) $detail->total_qty,
                    ];
                })
                ->values();

            return $item;
        });

        $frameAnalysisBpjs = $frameAnalysisBpjs->map(function ($item) use ($frameAnalysisStart, $frameAnalysisEnd) {
            $item->kode_frames = DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
                ->where('pd.itemable_type', Frame::class)
                ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd])
                ->whereNotNull('p.pasien_service_type')
                ->whereRaw('LOWER(TRIM(COALESCE(p.pasien_service_type, ""))) LIKE ?', ['%bpjs%'])
                ->whereRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') = ?", [$item->merk_frame])
                ->whereRaw("COALESCE(NULLIF(TRIM(f.jenis_frame), ''), '-') = ?", [$item->jenis_frame])
                ->whereNotNull('f.kode_frame')
                ->select('f.kode_frame')
                ->distinct()
                ->orderBy('f.kode_frame')
                ->pluck('f.kode_frame')
                ->values();

            return $item;
        });

        $frameAnalysisUmum = collect();
        foreach ($branchNames as $branchLabel => $branchKeywords) {
            $branchItems = (clone $frameAnalysisBaseQuery)
                ->join('branches as b', 'b.id', '=', 'p.branch_id')
                ->where(function ($query) use ($branchKeywords) {
                    foreach ($branchKeywords as $keyword) {
                        $query->orWhereRaw('LOWER(TRIM(COALESCE(b.name, ""))) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    }
                })
                ->where(function ($query) {
                    $query->whereNull('p.pasien_service_type')
                        ->orWhereRaw('LOWER(TRIM(COALESCE(p.pasien_service_type, ""))) NOT LIKE ?', ['%bpjs%']);
                })
                ->selectRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') as merk_frame")
                ->selectRaw("COALESCE(NULLIF(TRIM(f.jenis_frame), ''), '-') as jenis_frame")
                ->selectRaw("COALESCE(NULLIF(TRIM(s.nama_sales), ''), '-') as sales_name")
                ->selectRaw('SUM(COALESCE(pd.quantity, 0)) as total_qty')
                ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
                ->groupBy('merk_frame', 'jenis_frame', 'sales_name')
                ->orderByDesc('total_qty')
                ->limit(10)
                ->get()
                ->map(function ($item) use ($branchLabel, $branchKeywords, $frameAnalysisStart, $frameAnalysisEnd) {
                    $item->cabang = $branchLabel;

                    $item->kode_frames = DB::table('penjualan_detail as pd')
                        ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                        ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
                        ->join('branches as b', 'b.id', '=', 'p.branch_id')
                        ->where('pd.itemable_type', Frame::class)
                        ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd])
                        ->where(function ($query) use ($branchKeywords) {
                            foreach ($branchKeywords as $keyword) {
                                $query->orWhereRaw('LOWER(TRIM(COALESCE(b.name, ""))) LIKE ?', ['%' . strtolower($keyword) . '%']);
                            }
                        })
                        ->where(function ($query) {
                            $query->whereNull('p.pasien_service_type')
                                ->orWhereRaw('LOWER(TRIM(COALESCE(p.pasien_service_type, ""))) NOT LIKE ?', ['%bpjs%']);
                        })
                        ->whereRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') = ?", [$item->merk_frame])
                        ->whereRaw("COALESCE(NULLIF(TRIM(f.jenis_frame), ''), '-') = ?", [$item->jenis_frame])
                        ->whereNotNull('f.kode_frame')
                        ->select('f.kode_frame')
                        ->distinct()
                        ->orderBy('f.kode_frame')
                        ->pluck('f.kode_frame')
                        ->values();

                    return $item;
                });

            $frameAnalysisUmum = $frameAnalysisUmum->merge($branchItems);
        }

        return view('frame.index', compact(
            'sales',
            'branches',
            'lowStockFrame',
            'batasStok',
            'frameAnalysisBpjsSummary',
            'frameAnalysisUmumSummary',
            'frameAnalysisBpjs',
            'frameAnalysisUmum',
            'frameAnalysisUmumByBranch',
            'frameAnalysisPeriodLabel'
        ));
    }

    public function analysis(Request $request)
    {
        $user = auth()->user();
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        $selectedSalesId = $request->get('sales_id');
        $selectedYear = (int) substr($selectedMonth, 0, 4);
        $selectedMonthNumber = (int) substr($selectedMonth, 5, 2);

        $frameAnalysisStart = Carbon::create($selectedYear, $selectedMonthNumber, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $frameAnalysisEnd = Carbon::create($selectedYear, $selectedMonthNumber, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth();
        $frameAnalysisPeriodLabel = $frameAnalysisStart->translatedFormat('F Y');
        $sales = Sales::all()->pluck('nama_sales', 'id_sales');
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        $batasStok = 2;
        $lowStockFrame = Frame::accessibleByUser($user)
            ->where('stok', '<=', $batasStok)
            ->with('branch')
            ->orderBy('stok', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $frameAnalysisBaseQuery = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
            ->leftJoin('sales as s', 'f.id_sales', '=', 's.id_sales')
            ->where('pd.itemable_type', Frame::class)
            ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd]);

        $patientServiceTypeSql = "(SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1)";
        $bpjsServiceTypeSql = "LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF($patientServiceTypeSql, ''), '')))";

        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            $frameAnalysisBaseQuery->where('p.branch_id', $user->branch_id);
        }

        if (!empty($selectedSalesId)) {
            $frameAnalysisBaseQuery->where('f.id_sales', $selectedSalesId);
        }

        $frameAnalysisBpjsSummary = (clone $frameAnalysisBaseQuery)
            ->whereRaw($bpjsServiceTypeSql . ' LIKE ?', ['%bpjs%'])
            ->selectRaw('COALESCE(SUM(pd.quantity), 0) as total_qty')
            ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
            ->first();

        $frameAnalysisUmumSummary = (clone $frameAnalysisBaseQuery)
            ->where(function ($query) {
                $query->whereRaw("LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF((SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1), ''), ''))) NOT LIKE ?", ['%bpjs%']);
            })
            ->selectRaw('COALESCE(SUM(pd.quantity), 0) as total_qty')
            ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
            ->first();

        $branchNames = [
            'Optik Melati 1' => ['optik melati cabang 1', 'optik melati 1'],
            'Optik Melati 2' => ['optik melati cabang 2', 'optik melati 2'],
        ];

        $frameAnalysisUmumByBranch = collect();
        foreach ($branchNames as $branchLabel => $branchKeywords) {
            $branchQuery = DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
                ->join('branches as b', 'b.id', '=', 'p.branch_id')
                ->where('pd.itemable_type', Frame::class)
                ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd])
                ->where(function ($query) use ($branchKeywords) {
                    foreach ($branchKeywords as $keyword) {
                        $query->orWhereRaw('LOWER(TRIM(COALESCE(b.name, ""))) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    }
                })
                ->where(function ($query) {
                    $query->whereRaw("LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF((SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1), ''), ''))) NOT LIKE ?", ['%bpjs%']);
                })
                ->selectRaw('COALESCE(SUM(pd.quantity), 0) as total_qty')
                ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
                ->first();

            $frameAnalysisUmumByBranch->push((object) [
                'branch_name' => $branchLabel,
                'total_qty' => (int) ($branchQuery->total_qty ?? 0),
                'total_transaksi' => (int) ($branchQuery->total_transaksi ?? 0),
            ]);
        }

        $frameAnalysisBpjs = (clone $frameAnalysisBaseQuery)
            ->whereRaw($bpjsServiceTypeSql . ' LIKE ?', ['%bpjs%'])
            ->selectRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') as merk_frame")
            ->selectRaw("COALESCE(NULLIF(TRIM(f.jenis_frame), ''), '-') as jenis_frame")
            ->selectRaw("COALESCE(NULLIF(TRIM(s.nama_sales), ''), '-') as sales_name")
            ->selectRaw('SUM(COALESCE(pd.quantity, 0)) as total_qty')
            ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
            ->groupBy('merk_frame', 'jenis_frame', 'sales_name')
            ->havingRaw('SUM(COALESCE(pd.quantity, 0)) > 2')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $frameAnalysisBpjs = $frameAnalysisBpjs->map(function ($item) use ($frameAnalysisStart, $frameAnalysisEnd, $selectedSalesId, $bpjsServiceTypeSql) {
            $item->kode_frame_details = DB::table('penjualan_detail as pd')
                ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
                ->leftJoin('sales as s', 'f.id_sales', '=', 's.id_sales')
                ->where('pd.itemable_type', Frame::class)
                ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd])
                ->whereRaw($bpjsServiceTypeSql . ' LIKE ?', ['%bpjs%'])
                ->when(!empty($selectedSalesId), function ($query) use ($selectedSalesId) {
                    $query->where('f.id_sales', $selectedSalesId);
                })
                ->whereRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') = ?", [$item->merk_frame])
                ->whereRaw($jenisFrameNormalizedSql . ' = ?', [$item->jenis_frame])
                ->whereNotNull('f.kode_frame')
                ->selectRaw('f.kode_frame as kode_frame')
                ->selectRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') as merk_frame")
                ->selectRaw("COALESCE(NULLIF(TRIM(s.nama_sales), ''), '-') as sales_name")
                ->selectRaw('SUM(COALESCE(pd.quantity, 0)) as total_qty')
                ->groupBy('f.kode_frame', 'merk_frame', 'sales_name')
                ->orderByDesc('total_qty')
                ->orderBy('f.kode_frame')
                ->get()
                ->map(function ($detail) {
                    return [
                        'kode_frame' => $detail->kode_frame,
                        'merk_frame' => $detail->merk_frame,
                        'sales_name' => $detail->sales_name,
                        'total_qty' => (int) $detail->total_qty,
                    ];
                })
                ->values();

            return $item;
        });

        $frameAnalysisUmum = collect();
        foreach ($branchNames as $branchLabel => $branchKeywords) {
            $branchItems = (clone $frameAnalysisBaseQuery)
                ->join('branches as b', 'b.id', '=', 'p.branch_id')
                ->where(function ($query) use ($branchKeywords) {
                    foreach ($branchKeywords as $keyword) {
                        $query->orWhereRaw('LOWER(TRIM(COALESCE(b.name, ""))) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    }
                })
                ->where(function ($query) {
                    $query->whereRaw("LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF((SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1), ''), ''))) NOT LIKE ?", ['%bpjs%']);
                })
                ->whereRaw("LOWER(TRIM(COALESCE(NULLIF(f.jenis_frame, ''), '-'))) = ?", ['umum'])
                ->selectRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') as merk_frame")
                ->selectRaw($jenisFrameNormalizedSql . ' as jenis_frame')
                ->selectRaw("COALESCE(NULLIF(TRIM(s.nama_sales), ''), '-') as sales_name")
                ->selectRaw('SUM(COALESCE(pd.quantity, 0)) as total_qty')
                ->selectRaw('COUNT(DISTINCT pd.penjualan_id) as total_transaksi')
                ->groupBy('merk_frame', 'jenis_frame', 'sales_name')
                ->orderByDesc('total_qty')
                ->limit(10)
                ->get()
                ->map(function ($item) use ($branchLabel, $branchKeywords, $frameAnalysisStart, $frameAnalysisEnd, $selectedSalesId) {
                    $item->cabang = $branchLabel;

                    $item->kode_frame_details = DB::table('penjualan_detail as pd')
                        ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
                        ->join('frames as f', 'f.id', '=', 'pd.itemable_id')
                        ->leftJoin('sales as s', 'f.id_sales', '=', 's.id_sales')
                        ->join('branches as b', 'b.id', '=', 'p.branch_id')
                        ->where('pd.itemable_type', Frame::class)
                        ->whereBetween('p.created_at', [$frameAnalysisStart, $frameAnalysisEnd])
                        ->where(function ($query) use ($branchKeywords) {
                            foreach ($branchKeywords as $keyword) {
                                $query->orWhereRaw('LOWER(TRIM(COALESCE(b.name, ""))) LIKE ?', ['%' . strtolower($keyword) . '%']);
                            }
                        })
                        ->where(function ($query) {
                            $query->whereRaw("LOWER(TRIM(COALESCE(NULLIF(p.pasien_service_type, ''), NULLIF((SELECT service_type FROM pasien WHERE pasien.id_pasien = p.pasien_id LIMIT 1), ''), ''))) NOT LIKE ?", ['%bpjs%']);
                        })
                        ->whereRaw("LOWER(TRIM(COALESCE(NULLIF(f.jenis_frame, ''), '-'))) = ?", ['umum'])
                        ->when(!empty($selectedSalesId), function ($query) use ($selectedSalesId) {
                            $query->where('f.id_sales', $selectedSalesId);
                        })
                        ->whereRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') = ?", [$item->merk_frame])
                        ->whereRaw($jenisFrameNormalizedSql . ' = ?', [$item->jenis_frame])
                        ->whereNotNull('f.kode_frame')
                        ->selectRaw('f.kode_frame as kode_frame')
                        ->selectRaw("COALESCE(NULLIF(TRIM(f.merk_frame), ''), 'Tanpa Merk') as merk_frame")
                        ->selectRaw("COALESCE(NULLIF(TRIM(s.nama_sales), ''), '-') as sales_name")
                        ->selectRaw('SUM(COALESCE(pd.quantity, 0)) as total_qty')
                        ->groupBy('f.kode_frame', 'merk_frame', 'sales_name')
                        ->orderByDesc('total_qty')
                        ->orderBy('f.kode_frame')
                        ->get()
                        ->map(function ($detail) {
                            return [
                                'kode_frame' => $detail->kode_frame,
                                'merk_frame' => $detail->merk_frame,
                                'sales_name' => $detail->sales_name,
                                'total_qty' => (int) $detail->total_qty,
                            ];
                        })
                        ->values();

                    return $item;
                });

            $frameAnalysisUmum = $frameAnalysisUmum->merge($branchItems);
        }

        return view('frame.analysis', compact(
            'sales',
            'branches',
            'lowStockFrame',
            'batasStok',
            'frameAnalysisBpjsSummary',
            'frameAnalysisUmumSummary',
            'frameAnalysisBpjs',
            'frameAnalysisUmum',
            'frameAnalysisUmumByBranch',
            'frameAnalysisPeriodLabel',
            'selectedMonth',
            'selectedSalesId'
        ));
    }

    public function data(Request $request)
    {
        $user = auth()->user();

        $query = Frame::query()
            ->leftJoin('branches', 'frames.branch_id', '=', 'branches.id')
            ->leftJoin('sales', 'frames.id_sales', '=', 'sales.id_sales')
            ->select('frames.*', 'branches.name as branch_name', 'sales.nama_sales as sales_name');

        if (!($user->isSuperAdmin() || $user->isAdmin())) {
            $query->where('frames.branch_id', $user->branch_id);
        }

        if ($request->filled('jenis_frame')) {
            $query->where('frames.jenis_frame', $request->jenis_frame);
        }

        return datatables()
            ->of($query)
            ->addColumn('checkbox', function ($frame) use ($user) {
                if (!($user->isSuperAdmin() || $user->isAdmin())) {
                    return '';
                }

                return '<input type="checkbox" name="selected_frame[]" value="' . $frame->id . '">';
            })
            ->editColumn('branch_name', function ($frame) {
                return $frame->branch_name ?? '-';
            })
            ->editColumn('sales_name', function ($frame) {
                return $frame->sales_name ?? '-';
            })
            ->addColumn('harga_beli_frame', function ($frame) {
                return format_uang($frame->harga_beli_frame);
            })
            ->addColumn('harga_jual_frame', function ($frame) {
                return format_uang($frame->harga_jual_frame);
            })
            ->addColumn('stok', function ($frame) {
                return format_uang($frame->stok);
            })
            ->addColumn('jenis_frame', function($frame) {
                return $frame->jenis_frame;
            })
            ->addIndexColumn()
            ->addColumn('aksi', function ($frame) use ($user) {
                if ($user->isKasir()) {
                    return '<div class="btn-group">
                        <button onclick="editform(`' . route('frame.update', $frame->id) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                        <button onclick="restockFrame(`' . route('frame.restock', $frame->id) . '`, `' . e($frame->merk_frame) . '`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-plus"></i></button>
                    </div>';
                }

                return '<div class="btn-group">
                    <button onclick="editform(`' . route('frame.update', $frame->id) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button onclick="restockFrame(`' . route('frame.restock', $frame->id) . '`, `' . e($frame->merk_frame) . '`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-plus"></i></button>
                    <button onclick="deleteData(`' . route('frame.destroy', $frame->id) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['aksi', 'checkbox'])
            ->filterColumn('branch_name', function ($query, $keyword) {
                $query->where('branches.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('branch_name', 'branches.name $1')
            ->make(true);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'kode_frame' => 'required|string|max:255|unique:frames,kode_frame',
            'merk_frame' => 'required|string|max:255',
            'jenis_frame' => 'nullable|string|max:255',
            'harga_beli_frame' => 'nullable|numeric|min:0',
            'harga_jual_frame' => 'nullable|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
            'id_sales' => 'nullable|exists:sales,id_sales',
        ]);

        $data = $request->all();

        // Logika baru yang membedakan peran Admin dan Kasir
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Untuk Admin, branch_id diambil dari form input
            // Validasi untuk memastikan cabang dipilih
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
            $data['branch_id'] = $request->branch_id;
        } else {
            // Untuk Kasir, branch_id dipaksa dari profil user
            $data['branch_id'] = $user->branch_id;
        }

        Frame::create($data);
        return response()->json(['success' => true, 'message' => 'Frame berhasil ditambahkan']);
    }

    public function show($id)
    {
        $frame = Frame::find($id);
        return response()->json($frame);
    }

    public function edit($id)
    {
        $frame = Frame::find($id);
        return response()->json($frame);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->isKasir()) {
            $frame = Frame::query()
                ->accessibleByUser($user)
                ->findOrFail($id);

            $data = $request->validate([
                'jenis_frame' => 'nullable|string|max:255',
                'harga_jual_frame' => 'nullable|numeric|min:0',
            ]);

            $frame->update($data);

            return response()->json('Data berhasil disimpan', 200);
        }

        $frame = Frame::findOrFail($id);

        $request->validate([
            'kode_frame' => 'required|string|max:255|unique:frames,kode_frame,' . $id,
            'merk_frame' => 'required|string|max:255',
            'jenis_frame' => 'nullable|string|max:255',
            'harga_beli_frame' => 'nullable|numeric|min:0',
            'harga_jual_frame' => 'nullable|numeric|min:0',
            'stok' => 'nullable|integer|min:0',
            'id_sales' => 'nullable|exists:sales,id_sales',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $data = $request->all();
        $data['branch_id'] = $request->branch_id;

        $frame->update($data);

        return response()->json('Data berhasil disimpan', 200);
    }

    public function restock(Request $request, $id)
    {
        $user = auth()->user();

        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $frame = Frame::query()
            ->accessibleByUser($user)
            ->findOrFail($id);

        $frame->increment('stok', (int) $request->qty);
        $frame->refresh();

        return response()->json([
            'message' => 'Restok berhasil. Stok saat ini: ' . $frame->stok,
            'stok' => (int) $frame->stok,
        ]);
    }

    public function destroy($id)
    {
        $frame = Frame::find($id);
        $frame->delete();
        return response(null, 204);
    }

    public function getData()
    {
        $frame = Frame::all();
        return response()->json($frame);
    }

    public function import(Request $request)
    {
        try {
        $request->validate([
                'file' => 'required|mimes:xlsx,xls'
        ]);

            // Debug: Log file info
            \Log::info('Import file info:', [
                'filename' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
                'mime' => $request->file('file')->getMimeType()
            ]);

        Excel::import(new FrameImport, $request->file('file'));
            
            return response()->json([
                'success' => true,
                'message' => 'Data frame berhasil diimport!'
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            \Log::error('Frame import validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error validasi: ' . $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Frame import error: ' . $e->getMessage());
            \Log::error('Frame import error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal import data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            return Excel::download(new FrameExport, 'frame_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('Frame export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportFull()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            return Excel::download(new FrameExport, 'frame_lengkap_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('Frame export full error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template Excel for frame import
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new \App\Exports\FrameTemplateExport, 
                'template_frame.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal download template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete multiple frames
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:frames,id'
        ]);

        try {
            $ids = $request->input('ids');
            $deletedCount = 0;

            foreach ($ids as $id) {
                $frame = Frame::find($id);
                if ($frame) {
                    $frame->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'message' => "Berhasil menghapus {$deletedCount} data frame."
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
