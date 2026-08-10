<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;

class PasienApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pasien::with('prescriptions');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('nama_pasien', 'like', '%' . $q . '%')
                    ->orWhere('nohp', 'like', '%' . $q . '%')
                    ->orWhere('no_bpjs', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('id_pasien')->paginate((int) $request->input('per_page', 20)),
        ]);
    }

    public function show(Pasien $pasien)
    {
        $pasien->load(['prescriptions']);

        return response()->json([
            'success' => true,
            'data' => $pasien,
        ]);
    }
}