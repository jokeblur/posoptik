<?php

namespace App\Http\Controllers;

use App\Models\Lensa;
use Illuminate\Http\Request;
use App\Imports\LensaImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LensaExport;

class LensaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        return view('lensa.index', compact('branches'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        $query = Lensa::with('branch')->accessibleByUser($user)->orderBy('id', 'desc');
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        $lensa = $query->get();
        return datatables()->of($lensa)
            ->addColumn('select_all', function ($lensa) {
                return '<input type="checkbox" name="id[]" value="' . $lensa->id . '">';
            })
            ->addColumn('kode_lensa', function ($lensa) {
                return $lensa->kode_lensa;
            })
            ->addColumn('merk_lensa', function ($lensa) {
                return $lensa->merk_lensa;
            })
            ->addColumn('branch_name', function ($lensa) {
                return $lensa->branch ? $lensa->branch->name : '-';
            })
            ->addColumn('type', function ($lensa) {
                return $lensa->type ?? '-';
            })
            ->addColumn('index', function ($lensa) {
                return $lensa->index ?? '-';
            })
            ->addColumn('coating', function ($lensa) {
                return $lensa->coating ?? '-';
            })
            ->addColumn('harga_beli_lensa', function ($lensa) {
                return format_uang($lensa->harga_beli_lensa);
            })
            ->addColumn('harga_jual_lensa', function ($lensa) {
                return format_uang($lensa->harga_jual_lensa);
            })
            ->addColumn('stok', function ($lensa) {
                return format_uang($lensa->stok);
            })
            ->addIndexColumn()
            ->addColumn('aksi', function ($lensa) {
                return '<div class="btn-group">
                    <button onclick="editform(\'' . route('lensa.update', $lensa->id) . '\')" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button onclick="deleteData(\'' . route('lensa.destroy', $lensa->id) . '\')" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['aksi', 'select_all'])
            ->make(true);
    }

    public function create()
    {
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        return view('lensa.form', compact('branches'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $lensa = Lensa::latest()->first() ?? new Lensa();
        $id_baru = (int)$lensa->id + 1;
        
        $data = $request->all();
        $data['kode_lensa'] = 'L' . tambah_nol_didepan($id_baru, 5);
        
        // Logika baru yang membedakan peran Admin dan Kasir
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Untuk Admin, branch_id diambil dari form input
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
            $data['branch_id'] = $request->branch_id;
        } else {
            // Untuk Kasir, branch_id dipaksa dari profil user
            $data['branch_id'] = $user->branch_id;
        }
        
        Lensa::create($data);
        return response()->json(['message' => 'Lensa berhasil ditambahkan']);
    }

    public function show($id)
    {
        $lensa = Lensa::find($id);
        return response()->json($lensa);
    }

    public function edit($id)
    {
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        $lensa = Lensa::find($id);
        return view('lensa.form', compact('lensa', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $lensa = Lensa::find($id);
        $user = auth()->user();
        $data = $request->all();

        // Admin/Super Admin bisa mengubah semua data termasuk cabang
        // Kasir tidak bisa mengubah data cabang
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            unset($data['branch_id']);
        } else {
             $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
             $data['branch_id'] = $request->branch_id;
        }
        
        $lensa->update($data);
        return response()->json(['message' => 'Lensa berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $lensa = Lensa::find($id);
        $lensa->delete();
        return response(null, 204);
    }

    public function getData()
    {
        $lensa = Lensa::all();
        return response()->json($lensa);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);
        Excel::import(new LensaImport, $request->file('file'));
        return back()->with('success', 'Data lensa berhasil diimpor!');
    }

    public function export()
    {
        try {
            $filename = 'lensa_' . date('Y-m-d_H-i-s') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(new LensaExport, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengexport data lensa: ' . $e->getMessage());
        }
    }
}
