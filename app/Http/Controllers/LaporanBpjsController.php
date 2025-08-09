<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanBpjsController extends Controller
{
    public function index()
    {
        return view('laporan.bpjs.index');
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = Transaksi::with('user', 'branch', 'pasien')->latest();

        // Filter berdasarkan cabang jika bukan super admin
        if ($user->role !== 'super admin') {
            $query->where('branch_id', $user->branch_id);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter berdasarkan jenis transaksi
        if ($request->filled('transaction_type')) {
            switch ($request->transaction_type) {
                case 'bpjs_normal':
                    $query->whereNotNull('pasien_service_type')
                          ->where('transaction_status', 'Normal');
                    break;
                case 'bpjs_naik_kelas':
                    $query->whereNotNull('pasien_service_type')
                          ->where('transaction_status', 'Naik Kelas');
                    break;
                case 'umum':
                    $query->whereNull('pasien_service_type');
                    break;
                case 'all_bpjs':
                    $query->whereNotNull('pasien_service_type');
                    break;
            }
        }

        $transactions = $query->get();

        return datatables()
            ->of($transactions)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($transaction) {
                return tanggal_indonesia($transaction->created_at, false);
            })
            ->editColumn('kode_penjualan', function ($transaction) {
                return '<span class="label label-success">'. $transaction->kode_penjualan .'</span>';
            })
            ->addColumn('nama_pasien', function ($transaction) {
                return $transaction->nama_pasien ?? 'N/A';
            })
            ->addColumn('jenis_layanan', function ($transaction) {
                if ($transaction->pasien_service_type) {
                    return '<span class="label label-info">'. $transaction->pasien_service_type .'</span>';
                }
                return '<span class="label label-default">Umum</span>';
            })
            ->addColumn('status_transaksi', function ($transaction) {
                if ($transaction->transaction_status == 'Naik Kelas') {
                    return '<span class="label label-warning">Naik Kelas</span>';
                } elseif ($transaction->transaction_status == 'Normal') {
                    return '<span class="label label-success">Normal</span>';
                } else {
                    return '<span class="label label-default">'. ($transaction->transaction_status ?? 'Normal') .'</span>';
                }
            })
            ->addColumn('total_harga', function ($transaction) {
                return 'Rp. '. format_uang($transaction->total);
            })
            ->addColumn('harga_default_bpjs', function ($transaction) {
                if ($transaction->bpjs_default_price > 0) {
                    return 'Rp. '. format_uang($transaction->bpjs_default_price);
                }
                return '-';
            })
            ->addColumn('biaya_tambahan', function ($transaction) {
                if ($transaction->total_additional_cost > 0) {
                    return '<span class="label label-warning">Rp. '. format_uang($transaction->total_additional_cost) .'</span>';
                }
                return '-';
            })
            ->addColumn('kasir', function ($transaction) {
                return $transaction->user->name ?? 'N/A';
            })
            ->addColumn('cabang', function ($transaction) {
                return $transaction->branch->name ?? 'N/A';
            })
            ->addColumn('aksi', function ($transaction) {
                return '<a href="'. route('penjualan.show', $transaction->id) .'" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i> Detail</a>';
            })
            ->rawColumns(['kode_penjualan', 'jenis_layanan', 'status_transaksi', 'biaya_tambahan', 'aksi'])
            ->make(true);
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
        $query = Transaksi::query();

        // Filter berdasarkan cabang jika bukan super admin
        if ($user->role !== 'super admin') {
            $query->where('branch_id', $user->branch_id);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Summary berdasarkan jenis transaksi
        $summary = [
            'total_transaksi' => $query->count(),
            'total_pendapatan' => $query->sum('total'),
            
            // BPJS Normal
            'bpjs_normal_count' => $query->clone()->whereNotNull('pasien_service_type')->where('transaction_status', 'Normal')->count(),
            'bpjs_normal_total' => $query->clone()->whereNotNull('pasien_service_type')->where('transaction_status', 'Normal')->sum('total'),
            'bpjs_normal_default_total' => $query->clone()->whereNotNull('pasien_service_type')->where('transaction_status', 'Normal')->sum('bpjs_default_price'),
            
            // BPJS Naik Kelas
            'bpjs_naik_kelas_count' => $query->clone()->whereNotNull('pasien_service_type')->where('transaction_status', 'Naik Kelas')->count(),
            'bpjs_naik_kelas_total' => $query->clone()->whereNotNull('pasien_service_type')->where('transaction_status', 'Naik Kelas')->sum('total'),
            'bpjs_naik_kelas_default_total' => $query->clone()->whereNotNull('pasien_service_type')->where('transaction_status', 'Naik Kelas')->sum('bpjs_default_price'),
            'bpjs_naik_kelas_additional_total' => $query->clone()->whereNotNull('pasien_service_type')->where('transaction_status', 'Naik Kelas')->sum('total_additional_cost'),
            
            // Transaksi Umum
            'umum_count' => $query->clone()->whereNull('pasien_service_type')->count(),
            'umum_total' => $query->clone()->whereNull('pasien_service_type')->sum('total'),
        ];

        return response()->json($summary);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $query = Transaksi::with('user', 'branch', 'pasien')->latest();

        // Filter berdasarkan cabang jika bukan super admin
        if ($user->role !== 'super admin') {
            $query->where('branch_id', $user->branch_id);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter berdasarkan jenis transaksi
        if ($request->filled('transaction_type')) {
            switch ($request->transaction_type) {
                case 'bpjs_normal':
                    $query->whereNotNull('pasien_service_type')
                          ->where('transaction_status', 'Normal');
                    break;
                case 'bpjs_naik_kelas':
                    $query->whereNotNull('pasien_service_type')
                          ->where('transaction_status', 'Naik Kelas');
                    break;
                case 'umum':
                    $query->whereNull('pasien_service_type');
                    break;
                case 'all_bpjs':
                    $query->whereNotNull('pasien_service_type');
                    break;
            }
        }

        $transactions = $query->get();

        // Export ke Excel atau CSV
        $filename = 'laporan_bpjs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'Tanggal',
                'Kode Penjualan',
                'Nama Pasien',
                'Jenis Layanan',
                'Status Transaksi',
                'Total Harga',
                'Harga Default BPJS',
                'Biaya Tambahan',
                'Kasir',
                'Cabang'
            ]);

            // Data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    tanggal_indonesia($transaction->created_at, false),
                    $transaction->kode_penjualan,
                    $transaction->nama_pasien ?? 'N/A',
                    $transaction->pasien_service_type ?? 'Umum',
                    $transaction->transaction_status ?? 'Normal',
                    $transaction->total,
                    $transaction->bpjs_default_price,
                    $transaction->total_additional_cost,
                    $transaction->user->name ?? 'N/A',
                    $transaction->branch->name ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
