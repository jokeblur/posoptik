<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;

class PassetController extends Controller
{
    public function index()
    {
        return view('passet.index');
    }

    public function data()
    {
        // Semua user yang bisa akses halaman ini (passet, admin, super admin) bisa melihat semua pekerjaan
        $transaksi = Transaksi::with('pasien', 'branch')
            ->whereIn('status_pengerjaan', ['Menunggu Pengerjaan', 'Selesai Dikerjakan'])
            ->latest()
            ->get();

        return datatables()
            ->of($transaksi)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($transaksi) {
                return tanggal_indonesia($transaksi->created_at, false);
            })
            ->addColumn('pasien_name', function ($transaksi) {
                return $transaksi->pasien->nama_pasien ?? 'N/A';
            })
            ->addColumn('cabang_name', function ($transaksi) {
                return $transaksi->branch->name ?? 'Pusat';
            })
            ->editColumn('status_pengerjaan', function ($transaksi) {
                $statusClass = $transaksi->status_pengerjaan == 'Selesai Dikerjakan' ? 'label-success' : 'label-warning';
                return '<span class="label '. $statusClass .'">'. $transaksi->status_pengerjaan .'</span>';
            })
            ->addColumn('aksi', function ($transaksi) {
                if ($transaksi->status_pengerjaan == 'Menunggu Pengerjaan') {
                    return '
                    <div class="btn-group">
                        <button onclick="markAsSelesai(`'. route('passet.selesai', $transaksi->id) .'`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-check"></i> Tandai Selesai</button>
                    </div>
                    ';
                }
                return '';
            })
            ->rawColumns(['aksi', 'status_pengerjaan'])
            ->make(true);
    }

    public function markAsSelesai($id, Request $request)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->status_pengerjaan = 'Selesai Dikerjakan';
        // Jika admin/super admin dan ada user_id di request, pakai user_id tersebut
        $user = auth()->user();
        $isAdmin = isset($user->role) && (trim($user->role) === \App\Models\User::ROLE_ADMIN || trim($user->role) === \App\Models\User::ROLE_SUPER_ADMIN);
        if ($isAdmin) {
            $transaksi->passet_by_user_id = $request->input('user_id', auth()->id());
        } else {
            $transaksi->passet_by_user_id = auth()->id();
        }
        $transaksi->waktu_selesai_dikerjakan = now();
        $transaksi->save();

        return response()->json(['message' => 'Status berhasil diubah menjadi Selesai.']);
    }
}
