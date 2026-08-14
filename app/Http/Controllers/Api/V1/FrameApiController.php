<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use Illuminate\Http\Request;

class FrameApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Frame::with(['branch', 'sales']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('kode_frame', 'like', $q . '%')
                    ->orWhere('merk_frame', 'like', $q . '%')
                    ->orWhere('jenis_frame', 'like', $q . '%');
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('sales_id')) {
            $query->where('id_sales', $request->sales_id);
        }

        if ($request->filled('jenis_frame')) {
            $query->whereRaw('LOWER(TRIM(COALESCE(jenis_frame, ""))) = ?', [strtolower(trim($request->jenis_frame))]);
        }

        if ($request->filled('stok_min')) {
            $query->where('stok', '>=', (int) $request->stok_min);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('id')->paginate((int) $request->input('per_page', 20)),
        ]);
    }

    public function show(Frame $frame)
    {
        $frame->load(['branch', 'sales']);

        return response()->json([
            'success' => true,
            'data' => $frame,
        ]);
    }
}