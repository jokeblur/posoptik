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

    public function markAsSelesai($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->status_pengerjaan = 'Selesai Dikerjakan';
        $transaksi->passet_by_user_id = auth()->id(); // Simpan ID user passet
        $transaksi->waktu_selesai_dikerjakan = now(); // Catat waktu selesai
        $transaksi->save();

        return response()->json(['message' => 'Status berhasil diubah menjadi Selesai.']);
    }
}
