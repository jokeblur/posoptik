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
            $data = Aksesoris::query()->with('branch');
            if (!($user->isSuperAdmin() || $user->isAdmin())) {
                $data = $data->where('branch_id', $user->branch_id);
            }
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('branch_name', function ($row) {
                    return $row->branch->name ?? '-';
                })
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
        try {
            $user = auth()->user();

            // Default modal (harga beli) ditentukan super admin saat setup produk.
            if (!$user->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk aksesoris baru harus dibuat super admin agar default modal terisi dulu.'
                ], 403);
            }

            $validated = $request->validate([
                'nama_produk' => 'required|string|max:255',
                'harga_beli' => 'required|integer|min:0',
                'harga_jual' => 'required|integer|min:0',
                'stok' => 'required|integer|min:0',
                'branch_id' => 'required|exists:branches,id',
            ]);

            $data = $validated;
            Aksesoris::create($data);
            return response()->json(['success' => true, 'message' => 'Aksesoris berhasil ditambahkan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $aksesoris = Aksesoris::find($id);
            if (!$aksesoris) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $rules = [
                'nama_produk' => 'required|string|max:255',
                'harga_jual' => 'required|integer|min:0',
                'stok' => 'required|integer|min:0',
                'branch_id' => 'nullable|exists:branches,id',
            ];

            if ($user->isSuperAdmin()) {
                $rules['harga_beli'] = 'required|integer|min:0';
                $rules['branch_id'] = 'required|exists:branches,id';
            }

            $validated = $request->validate($rules);

            $data = $validated;

            if (!$user->isSuperAdmin()) {
                unset($data['harga_beli']);
                $data['branch_id'] = $user->branch_id;
            }

            $aksesoris->update($data);
            return response()->json(['success' => true, 'message' => 'Aksesoris berhasil diupdate']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $aksesoris = Aksesoris::findOrFail($id);
            $aksesoris->delete();
            return response()->json(['success' => true, 'message' => 'Aksesoris berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $aksesoris = Aksesoris::findOrFail($id);
        return response()->json($aksesoris);
    }
} 