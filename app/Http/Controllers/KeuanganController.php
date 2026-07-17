<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\Branch;
use Illuminate\Http\Request;

class KeuanganController extends Controller
{
    public function index()
    {
        $branches = Branch::all()->pluck('name', 'id');
        return view('keuangan.index', compact('branches'));
    }

    public function data(Request $request)
    {
        $user  = auth()->user();
        $query = Keuangan::with('branch', 'createdBy');

        if (!$user->isSuperAdmin()) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        $keuangan = $query->orderByDesc('tanggal')->orderByDesc('id')->get();

        return datatables()->of($keuangan)
            ->addIndexColumn()
            ->addColumn('tanggal_fmt', fn($k) => $k->tanggal->format('d/m/Y'))
            ->addColumn('jenis_badge', function ($k) {
                $cls = $k->jenis === 'pemasukan' ? 'success' : 'danger';
                $lbl = $k->jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran';
                return '<span class="label label-' . $cls . '">' . $lbl . '</span>';
            })
            ->addColumn('jumlah_fmt', fn($k) => 'Rp ' . number_format($k->jumlah, 0, ',', '.'))
            ->addColumn('branch_name', fn($k) => $k->branch->name ?? '-')
            ->addColumn('created_by_name', fn($k) => $k->createdBy->name ?? '-')
            ->addColumn('aksi', fn($k) => '
                <button onclick="editKeuangan(' . $k->id . ')" class="btn btn-xs btn-warning btn-flat">
                    <i class="fa fa-pencil"></i> Edit
                </button>
                <button onclick="hapusKeuangan(' . $k->id . ')" class="btn btn-xs btn-danger btn-flat">
                    <i class="fa fa-trash"></i> Hapus
                </button>')
            ->rawColumns(['jenis_badge', 'aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'  => 'required|date',
            'jenis'    => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string|max:100',
            'jumlah'   => 'required|numeric|min:0',
        ]);

        $data              = $request->only(['tanggal', 'jenis', 'kategori', 'jumlah', 'keterangan', 'branch_id']);
        $data['created_by'] = auth()->id();

        Keuangan::create($data);

        return response()->json(['success' => true, 'message' => 'Data keuangan berhasil disimpan']);
    }

    public function show(Keuangan $keuangan)
    {
        return response()->json($keuangan);
    }

    public function update(Request $request, Keuangan $keuangan)
    {
        $request->validate([
            'tanggal'  => 'required|date',
            'jenis'    => 'required|in:pemasukan,pengeluaran',
            'kategori' => 'required|string|max:100',
            'jumlah'   => 'required|numeric|min:0',
        ]);

        $keuangan->update($request->only(['tanggal', 'jenis', 'kategori', 'jumlah', 'keterangan', 'branch_id']));

        return response()->json(['success' => true, 'message' => 'Data keuangan berhasil diupdate']);
    }

    public function destroy(Keuangan $keuangan)
    {
        $keuangan->delete();
        return response()->json(['success' => true, 'message' => 'Data keuangan berhasil dihapus']);
    }

    public function summary(Request $request)
    {
        $user  = auth()->user();
        $query = Keuangan::query();

        if (!$user->isSuperAdmin()) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal', $request->bulan)->whereYear('tanggal', $request->tahun);
        }

        $pemasukan   = (clone $query)->where('jenis', 'pemasukan')->sum('jumlah');
        $pengeluaran = (clone $query)->where('jenis', 'pengeluaran')->sum('jumlah');
        $saldo       = $pemasukan - $pengeluaran;

        return response()->json(compact('pemasukan', 'pengeluaran', 'saldo'));
    }
}
