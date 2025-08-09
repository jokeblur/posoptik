<?php

namespace App\Http\Controllers;

use App\Models\Aksesoris;
use Illuminate\Http\Request;

class AksesorisController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $data = Aksesoris::query();
            if (!($user->isSuperAdmin() || $user->isAdmin())) {
                $data = $data->where('branch_id', $user->branch_id);
            }
            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('harga_beli', function($row) {
                    return number_format($row->harga_beli, 0, ',', '.');
                })
                ->editColumn('harga_jual', function($row) {
                    return number_format($row->harga_jual, 0, ',', '.');
                })
                ->editColumn('stok', function($row) {
                    return (int) $row->stok;
                })
                ->addColumn('aksi', function ($row) {
                    return '
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-info btn-edit-aksesoris" data-id="'.$row->id.'"><i class="fa fa-pencil"></i></button>
                            <form action="'.route('aksesoris.destroy', $row->id).'" method="POST" style="display:inline;">
                                '.csrf_field().method_field('DELETE').'
                                <button class="btn btn-xs btn-danger btn-flat" onclick="return confirm(\'Yakin hapus?\')"><i class="fa fa-trash"></i></button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
        return view('aksesoris.index');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $data['branch_id'] = $user->branch_id;
        }
        Aksesoris::create($data);
        return response()->json(['success' => true, 'message' => 'Aksesoris berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $aksesoris = Aksesoris::find($id);
        $data = $request->all();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $data['branch_id'] = $user->branch_id;
        }
        $aksesoris->update($data);
        return response()->json(['success' => true, 'message' => 'Aksesoris berhasil diupdate']);
    }

    public function destroy($id)
    {
        $aksesoris = Aksesoris::findOrFail($id);
        $aksesoris->delete();
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $aksesoris = Aksesoris::findOrFail($id);
        return response()->json($aksesoris);
    }
} 