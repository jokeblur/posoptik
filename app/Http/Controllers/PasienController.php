<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasienController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pasien.index');
    }

    public function data()
    {
        $pasien = Pasien::orderBy('id_pasien', 'desc')->get();

        return datatables()
            ->of($pasien)
            ->addIndexColumn()
            ->addColumn('aksi', function ($pasien) {
                return '
            <div class="btn-group">
                <button onclick="showDetail(`' . route('pasien.show', $pasien->id_pasien) . '`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-eye"></i></button>
                <button onclick="editform(`' . route('pasien.update', $pasien->id_pasien) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                <button onclick="deleteData(`' . route('pasien.destroy', $pasien->id_pasien) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
            </div>
            ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            $pasienData = $request->only(['nama_pasien', 'alamat', 'nohp', 'service_type']);
            $pasien = Pasien::create($pasienData);

            $prescriptionData = $request->only(['od_sph', 'od_cyl', 'od_axis', 'os_sph', 'os_cyl', 'os_axis', 'add', 'pd', 'catatan']);
            $prescriptionData['id_pasien'] = $pasien->id_pasien;
            $prescriptionData['tanggal'] = now();
            
            Prescription::create($prescriptionData);

            DB::commit();
            return response()->json(['message' => 'Data pasien berhasil disimpan'], 200);
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
        $pasien = Pasien::with('prescriptions')->find($id);
        return response()->json($pasien);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $pasien = Pasien::find($id);
        if ($pasien) {
            $pasien->delete();
            return response(null, 204);
        }
        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    public function getDetails($id)
    {
        $pasien = Pasien::with('prescriptions')->find($id);
        return response()->json($pasien);
    }
}
