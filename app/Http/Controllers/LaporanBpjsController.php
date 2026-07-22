<?php

namespace App\Http\Controllers;

use App\Exports\LaporanBpjsFormattedExport;
use App\Models\Penjualan;
use App\Models\Branch;
use App\Services\BpjsPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanBpjsController extends Controller
{
    private function bpjsServiceTypes(): array
    {
        return ['BPJS I', 'BPJS II', 'BPJS III'];
    }

    private function applyBpjsOnlyFilter($query)
    {
        $bpjsTypes = $this->bpjsServiceTypes();

        return $query->where(function ($q) use ($bpjsTypes) {
            $q->whereIn('pasien_service_type', $bpjsTypes)
              ->orWhereHas('pasien', function ($pasienQuery) use ($bpjsTypes) {
                  $pasienQuery->whereIn('service_type', $bpjsTypes);
              });
        });
    }

    private function resolveBpjsDefaultAmount(Penjualan $transaction): float
    {
        if ((float) $transaction->bpjs_default_price > 0) {
            return (float) $transaction->bpjs_default_price;
        }

        $serviceType = $transaction->pasien_service_type ?? ($transaction->pasien->service_type ?? null);
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
    }

    public function index()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $branches = $isSuperAdmin ? Branch::active()->get() : Branch::where('id', $user->branch_id)->get();
        $selectedBranchId = $isSuperAdmin ? session('active_branch_id', $user->branch_id) : $user->branch_id;

        return view('laporan.bpjs.index', compact('branches', 'selectedBranchId', 'isSuperAdmin'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = Penjualan::with('user', 'branch', 'pasien');

        $branchId = $request->input('branch_id');
        if ($user->isSuperAdmin()) {
            $branchId = $branchId ?: session('active_branch_id', $user->branch_id);
        } else {
            $branchId = $user->branch_id;
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Halaman laporan BPJS hanya menampilkan transaksi layanan BPJS
        $this->applyBpjsOnlyFilter($query);

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter berdasarkan jenis transaksi
        if ($request->filled('transaction_type')) {
            switch ($request->transaction_type) {
                case 'bpjs_normal':
                    $query->where('transaction_status', 'Normal');
                    break;
                case 'bpjs_naik_kelas':
                    $query->where('transaction_status', 'Naik Kelas');
                    break;
                case 'all_bpjs':
                    // no-op, karena query dasar sudah difilter BPJS
                    break;
            }
        }

        return datatables()
            ->of($query)
            ->addIndexColumn()
            ->orderColumn('tanggal', function ($query, $order) {
                return $query->orderBy('tanggal', $order);
            })
            ->addColumn('tanggal', function ($transaction) {
                return tanggal_indonesia($transaction->tanggal, false);
            })
            ->editColumn('kode_penjualan', function ($transaction) {
                return '<span class="label label-success">'. $transaction->kode_penjualan .'</span>';
            })
            ->addColumn('nama_pasien', function ($transaction) {
                return $transaction->pasien->nama_pasien ?? 'N/A';
            })
            ->addColumn('jenis_layanan', function ($transaction) {
                $serviceType = $transaction->pasien_service_type ?? ($transaction->pasien->service_type ?? null);
                if ($serviceType) {
                    return '<span class="label label-info">'. $serviceType .'</span>';
                }
                return '<span class="label label-default">-</span>';
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
                return 'Rp. '. format_uang($this->resolveBpjsDefaultAmount($transaction));
            })
            ->addColumn('harga_default_bpjs', function ($transaction) {
                $defaultAmount = $this->resolveBpjsDefaultAmount($transaction);
                if ($defaultAmount > 0) {
                    return 'Rp. '. format_uang($defaultAmount);
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
                return '<div class="btn-group">'
                    . '<a href="'. route('penjualan.show', $transaction->id) .'" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i> Detail</a>'
                    . '<button type="button" class="btn btn-xs btn-danger btn-flat" onclick="hapusTransaksi(\'' . route('penjualan.destroy', $transaction->id) . '\')"><i class="fa fa-trash"></i> Hapus</button>'
                    . '</div>';
            })
            ->filterColumn('nama_pasien', function($query, $keyword) {
                $query->whereHas('pasien', function($q) use ($keyword) {
                    $q->where('nama_pasien', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['kode_penjualan', 'jenis_layanan', 'status_transaksi', 'biaya_tambahan', 'aksi'])
            ->make(true);
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
        $query = Penjualan::query();

        $branchId = $request->input('branch_id');
        if ($user->isSuperAdmin()) {
            $branchId = $branchId ?: session('active_branch_id', $user->branch_id);
        } else {
            $branchId = $user->branch_id;
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Halaman laporan BPJS hanya menampilkan transaksi layanan BPJS
        $this->applyBpjsOnlyFilter($query);

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter berdasarkan jenis transaksi
        if ($request->filled('transaction_type')) {
            switch ($request->transaction_type) {
                case 'bpjs_normal':
                    $query->where('transaction_status', 'Normal');
                    break;
                case 'bpjs_naik_kelas':
                    $query->where('transaction_status', 'Naik Kelas');
                    break;
                case 'all_bpjs':
                    // no-op, karena query dasar sudah difilter BPJS
                    break;
            }
        }

        $allBpjsTransactions = (clone $query)->with('pasien:id_pasien,service_type')->get();
        $bpjsNormalTransactions = (clone $query)->where('transaction_status', 'Normal')->with('pasien:id_pasien,service_type')->get();
        $bpjsNaikKelasTransactions = (clone $query)->where('transaction_status', 'Naik Kelas')->with('pasien:id_pasien,service_type')->get();

        // Summary berdasarkan jenis transaksi
        $summary = [
            'total_transaksi' => $query->count(),
            'total_pendapatan' => $allBpjsTransactions->sum(function ($trx) {
                return $this->resolveBpjsDefaultAmount($trx);
            }),
            
            // BPJS Normal
            'bpjs_normal_count' => $query->clone()->where('transaction_status', 'Normal')->count(),
            'bpjs_normal_total' => $bpjsNormalTransactions->sum(function ($trx) {
                return $this->resolveBpjsDefaultAmount($trx);
            }),
            'bpjs_normal_default_total' => $bpjsNormalTransactions->sum(function ($trx) {
                return $this->resolveBpjsDefaultAmount($trx);
            }),
            
            // BPJS Naik Kelas
            'bpjs_naik_kelas_count' => $query->clone()->where('transaction_status', 'Naik Kelas')->count(),
            'bpjs_naik_kelas_total' => $bpjsNaikKelasTransactions->sum(function ($trx) {
                return $this->resolveBpjsDefaultAmount($trx);
            }),
            'bpjs_naik_kelas_default_total' => $bpjsNaikKelasTransactions->sum(function ($trx) {
                return $this->resolveBpjsDefaultAmount($trx);
            }),
            'bpjs_naik_kelas_additional_total' => $query->clone()->where('transaction_status', 'Naik Kelas')->sum('total_additional_cost'),
            
            // Transaksi Umum
            'umum_count' => 0,
            'umum_total' => 0,
        ];

        return response()->json($summary);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $query = Penjualan::with(['user', 'branch', 'pasien.prescriptions.dokter', 'dokter', 'details.itemable'])->latest();

        $branchId = $request->input('branch_id');
        if ($user->isSuperAdmin()) {
            $branchId = $branchId ?: session('active_branch_id', $user->branch_id);
        } else {
            $branchId = $user->branch_id;
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Halaman laporan BPJS hanya menampilkan transaksi layanan BPJS
        $this->applyBpjsOnlyFilter($query);

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter berdasarkan jenis transaksi
        if ($request->filled('transaction_type')) {
            switch ($request->transaction_type) {
                case 'bpjs_normal':
                    $query->where('transaction_status', 'Normal');
                    break;
                case 'bpjs_naik_kelas':
                    $query->where('transaction_status', 'Naik Kelas');
                    break;
                case 'all_bpjs':
                    // no-op, karena query dasar sudah difilter BPJS
                    break;
            }
        }

        $transactions = $query->get();

        $periodLabel = 'BULAN PELAYANAN ' . strtoupper(Carbon::now()->translatedFormat('F Y'));
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            if ($startDate->format('mY') === $endDate->format('mY')) {
                $periodLabel = 'BULAN PELAYANAN ' . strtoupper($startDate->translatedFormat('F Y'));
            } else {
                $periodLabel = 'PERIODE ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
            }
        } elseif ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date);
            $periodLabel = 'BULAN PELAYANAN ' . strtoupper($startDate->translatedFormat('F Y'));
        }

        $filename = 'laporan_bpjs_format_' . date('Y-m-d_H-i-s') . '.xlsx';
        $export = new LaporanBpjsFormattedExport($transactions, $periodLabel);

        return Excel::download($export, $filename, ExcelFormat::XLSX);
    }
}
