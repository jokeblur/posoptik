<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\GajiKaryawan;
use App\Models\Branch;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $branches = Branch::all()->pluck('name', 'id');
        return view('karyawan.index', compact('branches'));
    }

    public function data(Request $request)
    {
        $query = Karyawan::with('branch');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $karyawan = $query->orderBy('nama')->get();

        return datatables()->of($karyawan)
            ->addIndexColumn()
            ->addColumn('branch_name', fn($k) => $k->branch->name ?? '-')
            ->addColumn('gaji_pokok_fmt', fn($k) => 'Rp ' . number_format($k->gaji_pokok, 0, ',', '.'))
            ->addColumn('status_badge', function ($k) {
                $cls = $k->status === 'aktif' ? 'success' : 'danger';
                return '<span class="label label-' . $cls . '">' . ucfirst($k->status) . '</span>';
            })
            ->addColumn('aksi', function ($k) {
                return '
                    <button onclick="showGaji(' . $k->id . ',\'' . addslashes($k->nama) . '\')" class="btn btn-xs btn-info btn-flat">
                        <i class="fa fa-money"></i> Gaji
                    </button>
                    <button onclick="editKaryawan(' . $k->id . ')" class="btn btn-xs btn-warning btn-flat">
                        <i class="fa fa-pencil"></i> Edit
                    </button>
                    <button onclick="hapusKaryawan(' . $k->id . ')" class="btn btn-xs btn-danger btn-flat">
                        <i class="fa fa-trash"></i> Hapus
                    </button>';
            })
            ->rawColumns(['status_badge', 'aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:100',
            'jabatan'       => 'required|string|max:100',
            'tanggal_masuk' => 'required|date',
            'gaji_pokok'    => 'required|numeric|min:0',
        ]);

        Karyawan::create($request->only([
            'nama', 'jabatan', 'branch_id', 'no_hp', 'email',
            'tanggal_masuk', 'status', 'gaji_pokok', 'catatan',
        ]));

        return response()->json(['success' => true, 'message' => 'Karyawan berhasil disimpan']);
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load('branch');
        return response()->json($karyawan);
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nama'          => 'required|string|max:100',
            'jabatan'       => 'required|string|max:100',
            'tanggal_masuk' => 'required|date',
            'gaji_pokok'    => 'required|numeric|min:0',
        ]);

        $karyawan->update($request->only([
            'nama', 'jabatan', 'branch_id', 'no_hp', 'email',
            'tanggal_masuk', 'status', 'gaji_pokok', 'catatan',
        ]));

        return response()->json(['success' => true, 'message' => 'Karyawan berhasil diupdate']);
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();
        return response()->json(['success' => true, 'message' => 'Karyawan berhasil dihapus']);
    }

    // ---- GAJI ----

    public function gajiData(Request $request, Karyawan $karyawan)
    {
        $gaji = GajiKaryawan::with('createdBy')
            ->where('karyawan_id', $karyawan->id)
            ->orderByDesc('tahun')->orderByDesc('bulan')
            ->get();

        return datatables()->of($gaji)
            ->addIndexColumn()
            ->addColumn('periode', fn($g) => $g->nama_bulan . ' ' . $g->tahun)
            ->addColumn('gaji_pokok_fmt', fn($g) => 'Rp ' . number_format($g->gaji_pokok, 0, ',', '.'))
            ->addColumn('bonus_fmt', fn($g) => 'Rp ' . number_format($g->bonus, 0, ',', '.'))
            ->addColumn('tunjangan_fmt', fn($g) => 'Rp ' . number_format($g->tunjangan, 0, ',', '.'))
            ->addColumn('potongan_fmt', fn($g) => 'Rp ' . number_format($g->potongan, 0, ',', '.'))
            ->addColumn('total_fmt', fn($g) => 'Rp ' . number_format($g->total_gaji, 0, ',', '.'))
            ->addColumn('aksi', fn($g) => '
                <button onclick="hapusGaji(' . $g->id . ')" class="btn btn-xs btn-danger btn-flat">
                    <i class="fa fa-trash"></i> Hapus
                </button>')
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function gajiStore(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'bulan'      => 'required|integer|between:1,12',
            'tahun'      => 'required|integer|min:2000',
            'gaji_pokok' => 'required|numeric|min:0',
            'bonus'      => 'nullable|numeric|min:0',
            'tunjangan'  => 'nullable|numeric|min:0',
            'potongan'   => 'nullable|numeric|min:0',
        ]);

        $gajiPokok  = $request->gaji_pokok;
        $bonus      = $request->bonus ?? 0;
        $tunjangan  = $request->tunjangan ?? 0;
        $potongan   = $request->potongan ?? 0;
        $total      = $gajiPokok + $bonus + $tunjangan - $potongan;

        GajiKaryawan::create([
            'karyawan_id' => $karyawan->id,
            'bulan'       => $request->bulan,
            'tahun'       => $request->tahun,
            'gaji_pokok'  => $gajiPokok,
            'bonus'       => $bonus,
            'tunjangan'   => $tunjangan,
            'potongan'    => $potongan,
            'total_gaji'  => $total,
            'keterangan'  => $request->keterangan,
            'created_by'  => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Data gaji berhasil disimpan']);
    }

    public function gajiDestroy(GajiKaryawan $gaji)
    {
        $gaji->delete();
        return response()->json(['success' => true, 'message' => 'Data gaji berhasil dihapus']);
    }
}
