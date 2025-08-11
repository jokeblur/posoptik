<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Penjualan as Transaksi; // Alias untuk kompatibilitas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BarcodeController extends Controller
{
    public function index()
    {
        return view('barcode.index');
    }

    public function scan()
    {
        return view('barcode.scan');
    }

    public function scanDirect($barcode)
    {
        // Log untuk debug
        Log::info('Scan Direct - Barcode: ' . $barcode);
        
        // Cari transaksi berdasarkan barcode
        $transaksi = Penjualan::with('user', 'branch', 'pasien', 'dokter', 'details.itemable')
            ->where('barcode', $barcode)
            ->first();

        Log::info('Scan Direct - Transaksi found: ' . ($transaksi ? 'Yes' : 'No'));

        if (!$transaksi) {
            Log::warning('Scan Direct - Transaksi tidak ditemukan untuk barcode: ' . $barcode);
            return view('barcode.scan_direct', ['error' => 'Transaksi tidak ditemukan untuk barcode: ' . $barcode]);
        }

        Log::info('Scan Direct - Transaksi ditemukan: ' . $transaksi->kode_penjualan);
        return view('barcode.scan_direct', ['transaksi' => $transaksi, 'barcode' => $barcode]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $barcode = $request->barcode;
        
        // Cari transaksi berdasarkan barcode atau kode_penjualan
        $transaksi = Penjualan::with('user', 'branch', 'pasien', 'dokter', 'details.itemable')
            ->where(function($query) use ($barcode) {
                $query->where('barcode', $barcode)
                      ->orWhere('kode_penjualan', 'LIKE', '%' . $barcode . '%');
            })
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaksi->id,
                'kode_penjualan' => $transaksi->kode_penjualan,
                'tanggal' => tanggal_indonesia($transaksi->created_at, false),
                'nama_pasien' => $transaksi->nama_pasien,
                'status_pengerjaan' => $transaksi->status_pengerjaan,
                'barcode' => $transaksi->barcode
            ],
            'message' => 'Transaksi ditemukan'
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'transaksi_id' => 'required|exists:penjualan,id',
            'status_pengerjaan' => 'required|in:Menunggu Pengerjaan,Sedang Dikerjakan,Selesai Dikerjakan,Sudah Diambil'
        ]);

        $transaksi = Penjualan::findOrFail($request->transaksi_id);
        $user = auth()->user();

        // Update status berdasarkan role
        switch ($request->status_pengerjaan) {
            case 'Sedang Dikerjakan':
                if (!in_array($user->role, ['passet bantu', 'admin', 'super admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status ini'
                    ], 403);
                }
                $transaksi->passet_by_user_id = $user->id;
                break;
                
            case 'Selesai Dikerjakan':
                if (!in_array($user->role, ['passet bantu', 'admin', 'super admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status ini'
                    ], 403);
                }
                $transaksi->waktu_selesai_dikerjakan = now();
                break;
                
            case 'Sudah Diambil':
                if (!in_array($user->role, ['kasir', 'admin', 'super admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status ini'
                    ], 403);
                }
                $transaksi->waktu_sudah_diambil = now();
                break;
        }

        $transaksi->status_pengerjaan = $request->status_pengerjaan;
        $transaksi->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'data' => $transaksi->fresh(['user', 'branch', 'pasien', 'dokter'])
        ]);
    }

    public function generateBarcode(Request $request)
    {
        $request->validate([
            'transaksi_id' => 'required|exists:penjualan,id'
        ]);

        $transaksi = Penjualan::findOrFail($request->transaksi_id);
        
        // Generate barcode jika belum ada
        if (!$transaksi->barcode) {
            $barcode = 'TRX' . date('Ymd') . str_pad($transaksi->id, 6, '0', STR_PAD_LEFT);
            $transaksi->barcode = $barcode;
            $transaksi->save();
        }

        return response()->json([
            'success' => true,
            'barcode' => $transaksi->barcode,
            'message' => 'Barcode berhasil dibuat'
        ]);
    }

    public function printBarcode($id)
    {
        $transaksi = Penjualan::with('user', 'branch', 'pasien')->findOrFail($id);
        
        // Generate barcode jika belum ada
        if (!$transaksi->barcode) {
            $barcode = 'TRX' . date('Ymd') . str_pad($transaksi->id, 6, '0', STR_PAD_LEFT);
            $transaksi->barcode = $barcode;
            $transaksi->save();
        }

        return view('barcode.print', compact('transaksi'));
    }

    public function bulkGenerateBarcode()
    {
        // Generate barcode untuk semua transaksi yang belum memiliki barcode
        $transaksis = Penjualan::whereNull('barcode')->get();
        
        foreach ($transaksis as $transaksi) {
            $barcode = 'TRX' . date('Ymd') . str_pad($transaksi->id, 6, '0', STR_PAD_LEFT);
            $transaksi->barcode = $barcode;
            $transaksi->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil generate ' . $transaksis->count() . ' barcode'
        ]);
    }
}
