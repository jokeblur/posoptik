<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Frame;
use App\Models\Sales;
use App\Imports\FrameImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FrameExport;

class FrameController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())) {
                return $next($request);
            }
            // Jika bukan admin atau super admin, bisa diganti dengan redirect atau abort
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        })->only(['store', 'update', 'destroy']);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sales = Sales::all()->pluck('nama_sales', 'id_sales');
        $branches = \App\Models\Branch::all()->pluck('name', 'id');
        return view('frame.index', compact('sales', 'branches'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        
        $query = Frame::with('branch', 'sales')
            ->accessibleByUser($user)
            ->orderBy('id', 'desc');

        if ($request->filled('jenis_frame')) {
            $query->where('jenis_frame', $request->jenis_frame);
        }

        $frame = $query->get();

        return datatables()
            ->of($frame)
            ->addColumn('select_all', function ($frame) {
                return '<input type="checkbox" name="id[]" value="' . $frame->id . '">';
            })
            ->addColumn('branch_name', function ($frame) {
                return $frame->branch?->name ?? '-';
            })
            ->addColumn('sales_name', function ($frame) {
                return $frame->sales?->nama_sales ?? '-';
            })
            ->addColumn('harga_beli_frame', function ($frame) {
                return format_uang($frame->harga_beli_frame);
            })
            ->addColumn('harga_jual_frame', function ($frame) {
                return format_uang($frame->harga_jual_frame);
            })
            ->addColumn('stok', function ($frame) {
                return format_uang($frame->stok);
            })
            ->addColumn('jenis_frame', function($frame) {
                return $frame->jenis_frame;
            })
            ->addIndexColumn()
            ->addColumn('aksi', function ($frame) {
                return '<div class="btn-group">
                    <button onclick="editform(`' . route('frame.update', $frame->id) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button onclick="deleteData(`' . route('frame.destroy', $frame->id) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['aksi', 'select_all'])
            ->make(true);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $frame = Frame::latest()->first() ?? new Frame();
        $id_baru = (int)$frame->id + 1;
        $kode_frame = 'FR' . tambah_nol_didepan($id_baru, 6);
        
        $data = $request->all();
        $data['kode_frame'] = $kode_frame;

        // Logika baru yang membedakan peran Admin dan Kasir
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Untuk Admin, branch_id diambil dari form input
            // Validasi untuk memastikan cabang dipilih
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
            ]);
            $data['branch_id'] = $request->branch_id;
        } else {
            // Untuk Kasir, branch_id dipaksa dari profil user
            $data['branch_id'] = $user->branch_id;
        }

        Frame::create($data);
        return response()->json(['success' => true, 'message' => 'Frame berhasil ditambahkan']);
    }

    public function show($id)
    {
        $frame = Frame::find($id);
        return response()->json($frame);
    }

    public function edit($id)
    {
        $frame = Frame::find($id);
        return response()->json($frame);
    }

    public function update(Request $request, $id)
    {
        $frame = Frame::find($id);
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

        $frame->update($data);

        return response()->json('Data berhasil disimpan', 200);
    }

    public function destroy($id)
    {
        $frame = Frame::find($id);
        $frame->delete();
        return response(null, 204);
    }

    public function getData()
    {
        $frame = Frame::all();
        return response()->json($frame);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);
        Excel::import(new FrameImport, $request->file('file'));
        return back()->with('success', 'Data frame berhasil diimpor!');
    }

    public function export()
    {
        try {
            $filename = 'frame_' . date('Y-m-d_H-i-s') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(new FrameExport, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengexport data frame: ' . $e->getMessage());
        }
    }
}
