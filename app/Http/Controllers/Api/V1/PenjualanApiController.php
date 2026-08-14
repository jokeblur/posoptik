<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use Illuminate\Http\Request;

class PenjualanApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjualan::with(['user', 'branch', 'pasien', 'details.itemable', 'comments.user']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('kode_penjualan', 'like', $q . '%')
                    ->orWhere('barcode', 'like', $q . '%')
                    ->orWhereHas('pasien', function ($pasienQuery) use ($q) {
                        $pasienQuery->where('nama_pasien', 'like', $q . '%');
                    });
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('status_pengerjaan')) {
            $query->where('status_pengerjaan', $request->status_pengerjaan);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('tanggal')->paginate((int) $request->input('per_page', 20)),
        ]);
    }

    public function show(Penjualan $penjualan)
    {
        $penjualan->load(['user', 'branch', 'pasien', 'details.itemable', 'comments.user']);

        return response()->json([
            'success' => true,
            'data' => $penjualan,
        ]);
    }
}