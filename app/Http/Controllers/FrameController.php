<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Frame;
use App\Models\Sales;
use App\Imports\FrameImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FrameExport;
use Illuminate\Support\Facades\Log;

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
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $query = Frame::with('branch', 'sales')->orderBy('id', 'desc');
        } else {
            $query = Frame::with('branch', 'sales')->accessibleByUser($user)->orderBy('id', 'desc');
        }
        if ($request->filled('jenis_frame')) {
            $query->where('jenis_frame', $request->jenis_frame);
        }
        $frame = $query->get();
        return datatables()
            ->of($frame)
            ->addColumn('checkbox', function ($frame) {
                return '<input type="checkbox" name="selected_frame[]" value="' . $frame->id . '">';
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
            ->rawColumns(['aksi', 'checkbox'])
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
        try {
        $request->validate([
                'file' => 'required|mimes:xlsx,xls'
        ]);

            // Debug: Log file info
            \Log::info('Import file info:', [
                'filename' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
                'mime' => $request->file('file')->getMimeType()
            ]);

        Excel::import(new FrameImport, $request->file('file'));
            
            return response()->json([
                'success' => true,
                'message' => 'Data frame berhasil diimport!'
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            \Log::error('Frame import validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error validasi: ' . $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Frame import error: ' . $e->getMessage());
            \Log::error('Frame import error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal import data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            return Excel::download(new FrameExport, 'frame_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('Frame export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportFull()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            return Excel::download(new FrameExport, 'frame_lengkap_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('Frame export full error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template Excel for frame import
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new \App\Exports\FrameTemplateExport, 
                'template_frame.xlsx',
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal download template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete multiple frames
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:frames,id'
        ]);

        try {
            $ids = $request->input('ids');
            $deletedCount = 0;

            foreach ($ids as $id) {
                $frame = Frame::find($id);
                if ($frame) {
                    $frame->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'message' => "Berhasil menghapus {$deletedCount} data frame."
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
