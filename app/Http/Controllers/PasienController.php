<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PasienExport;
use App\Imports\PasienImport;
use Maatwebsite\Excel\Facades\Excel;

class PasienController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dokters = \App\Models\Dokter::all();
        return view('pasien.index', compact('dokters'));
    }

    public function data()
    {
        $pasien = Pasien::orderBy('id_pasien', 'desc')->get();

        return datatables()
            ->of($pasien)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($pasien) {
                return '<input type="checkbox" name="selected_pasien[]" value="' . $pasien->id_pasien . '">';
            })
            ->addColumn('aksi', function ($pasien) {
                return '
            <div class="btn-group">
                <button onclick="showDetail(`' . route('pasien.show', $pasien->id_pasien) . '`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-eye"></i></button>
                <a href="' . route('pasien.cetak-resep', $pasien->id_pasien) . '" target="_blank" class="btn btn-xs btn-warning btn-flat"><i class="fa fa-print"></i></a>
                <button onclick="editform(`' . route('pasien.update', $pasien->id_pasien) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                <button onclick="deleteData(`' . route('pasien.destroy', $pasien->id_pasien) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
            </div>
            ';
            })
            ->rawColumns(['checkbox', 'aksi'])
            ->addColumn('id_pasien', function ($pasien) {
                return $pasien->id_pasien;
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dokters = \App\Models\Dokter::all();
        return view('pasien.form', compact('dokters'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $pasienData = $request->only(['nama_pasien', 'alamat', 'nohp', 'service_type', 'no_bpjs']);
            $pasien = Pasien::create($pasienData);

            $prescriptionData = $request->only(['od_sph', 'od_cyl', 'od_axis', 'os_sph', 'os_cyl', 'os_axis', 'add', 'pd', 'catatan', 'dokter_id']);
            $prescriptionData['id_pasien'] = $pasien->id_pasien;
            $prescriptionData['tanggal'] = now();
            if ($request->input('dokter_id') === 'manual') {
                $prescriptionData['dokter_id'] = null;
                $prescriptionData['dokter_manual'] = $request->input('dokter_manual');
            } else {
                $prescriptionData['dokter_manual'] = null;
            }
            Prescription::create($prescriptionData);

            DB::commit();
            return response()->json(['message' => 'Data pasien berhasil disimpan'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    public function storeAndRedirect(Request $request)
    {
        DB::beginTransaction();
        try {
            $pasienData = $request->only(['nama_pasien', 'alamat', 'nohp', 'service_type', 'no_bpjs']);
            $pasien = Pasien::create($pasienData);

            $prescriptionData = $request->only(['od_sph', 'od_cyl', 'od_axis', 'os_sph', 'os_cyl', 'os_axis', 'add', 'pd', 'catatan', 'dokter_id']);
            $prescriptionData['id_pasien'] = $pasien->id_pasien;
            $prescriptionData['tanggal'] = now();
            if ($request->input('dokter_id') === 'manual') {
                $prescriptionData['dokter_id'] = null;
                $prescriptionData['dokter_manual'] = $request->input('dokter_manual');
            } else {
                $prescriptionData['dokter_manual'] = null;
            }
            Prescription::create($prescriptionData);

            DB::commit();
            return response()->json([
                'message' => 'Data pasien berhasil disimpan',
                'redirect_url' => route('penjualan.create', ['pasien_id' => $pasien->id_pasien])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pasien = Pasien::with(['prescriptions.dokter'])->findOrFail($id);
        if (request()->ajax()) {
            $data = $pasien->toArray();
            // Tambahkan nama dokter ke setiap prescription dan pastikan tidak ada duplikasi
            if (!empty($data['prescriptions'])) {
                // Sort prescriptions by date to ensure proper order
                usort($data['prescriptions'], function($a, $b) {
                    return strtotime($a['tanggal']) - strtotime($b['tanggal']);
                });
                
                foreach ($data['prescriptions'] as $i => $rx) {
                    $data['prescriptions'][$i]['dokter_nama'] = $rx['dokter']['nama_dokter'] ?? '-';
                }
            }
            return response()->json($data);
        }
        return view('pasien.detail', compact('pasien'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pasien = Pasien::findOrFail($id);
        $dokters = \App\Models\Dokter::all();
        return view('pasien.form', compact('pasien', 'dokters'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pasien = Pasien::findOrFail($id);
            $pasienData = $request->only(['nama_pasien', 'alamat', 'nohp', 'service_type']);
            $pasien->update($pasienData);

            if ($request->filled('od_sph')) {
                $prescriptionData = $request->only(['od_sph', 'od_cyl', 'od_axis', 'os_sph', 'os_cyl', 'os_axis', 'add', 'pd', 'catatan']);
                $prescriptionData['id_pasien'] = $pasien->id_pasien;
                $prescriptionData['tanggal'] = now();
                
                Prescription::create($prescriptionData);
            }

            DB::commit();
            return response()->json(['message' => 'Data pasien berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pasien = Pasien::find($id);
            if ($pasien) {
                // Hapus semua prescriptions yang terkait dengan pasien ini
                $pasien->prescriptions()->delete();
                
                // Hapus data pasien
                $pasien->delete();
                
                DB::commit();
                return response()->json(['message' => 'Data pasien dan riwayat resep berhasil dihapus'], 200);
            }
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }

    public function getDetails($id)
    {
        $pasien = Pasien::with(['prescriptions.dokter'])->find($id);
        
        if (!$pasien) {
            return response()->json(['error' => 'Pasien tidak ditemukan'], 404);
        }
        
        $data = $pasien->toArray();
        
        // Tambahkan nama dokter ke setiap prescription dan pastikan tidak ada duplikasi
        if (!empty($data['prescriptions'])) {
            // Sort prescriptions by date to ensure proper order
            usort($data['prescriptions'], function($a, $b) {
                return strtotime($b['tanggal']) - strtotime($a['tanggal']); // Urutkan dari yang terbaru
            });
            
            foreach ($data['prescriptions'] as $i => $rx) {
                $data['prescriptions'][$i]['dokter_nama'] = $rx['dokter']['nama_dokter'] ?? ($rx['dokter_manual'] ?? '-');
                // Format tanggal untuk display
                $data['prescriptions'][$i]['tanggal'] = date('d/m/Y', strtotime($rx['tanggal']));
            }
            
            // Ambil dokter dari resep terakhir untuk ditampilkan di detail pasien
            $latestPrescription = $data['prescriptions'][0];
            $data['dokter_nama'] = $latestPrescription['dokter_nama'];
            $data['dokter_id'] = $latestPrescription['dokter_id'];
        } else {
            $data['dokter_nama'] = '-';
            $data['dokter_id'] = null;
        }
        
        return response()->json($data);
    }

    public function export()
    {
        return Excel::download(new PasienExport, 'pasien.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);
        try {
            Excel::import(new PasienImport, $request->file('file'));
            return back()->with('success', 'Import data pasien berhasil');
        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete multiple patients
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:pasien,id_pasien'
        ]);

        DB::beginTransaction();
        try {
            $ids = $request->input('ids');
            $deletedCount = 0;

            foreach ($ids as $id) {
                $pasien = Pasien::find($id);
                if ($pasien) {
                    // Hapus semua prescriptions yang terkait
                    $pasien->prescriptions()->delete();
                    
                    // Hapus data pasien
                    $pasien->delete();
                    $deletedCount++;
                }
            }

            DB::commit();
            return response()->json([
                'message' => "Berhasil menghapus {$deletedCount} data pasien beserta riwayat resepnya."
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cetak resep pasien
     */
    public function cetakResep($id)
    {
        $pasien = Pasien::with(['prescriptions.dokter'])->findOrFail($id);
        
        // Ambil resep terbaru
        $latestPrescription = $pasien->prescriptions->sortByDesc('tanggal')->first();
        
        if (!$latestPrescription) {
            abort(404, 'Tidak ada resep untuk pasien ini');
        }
        
        return view('pasien.cetak-resep', compact('pasien', 'latestPrescription'));
    }

    /**
     * Cetak resep pasien ukuran A4
     */
    public function cetakResepA4($id)
    {
        $pasien = Pasien::with(['prescriptions.dokter'])->findOrFail($id);
        
        // Ambil resep terbaru
        $latestPrescription = $pasien->prescriptions->sortByDesc('tanggal')->first();
        
        if (!$latestPrescription) {
            abort(404, 'Tidak ada resep untuk pasien ini');
        }
        
        return view('pasien.cetak-resep-a4', compact('pasien', 'latestPrescription'));
    }
}
