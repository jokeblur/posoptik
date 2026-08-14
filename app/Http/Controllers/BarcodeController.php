<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Penjualan as Transaksi; // Alias untuk kompatibilitas
use App\Helpers\WhatsAppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BarcodeController extends Controller
{
    private const WORK_STATUS_SEDANG_MENGERJAKAN = 'Sedang Mengerjakan';
    private const WORK_STATUS_LENSA_DI_PESAN = 'Lensa Di Pesan';
    private const WORK_STATUS_LENSA_DATANG = 'Lensa Datang';
    private const WORK_STATUS_SUDAH_DI_KERJAKAN = 'Sudah Di Kerjakan';
    private const WORK_STATUS_KIRIM_WA = 'Kirim WA';
    private const WORK_STATUS_SUDAH_DI_AMBIL = 'Sudah Di Ambil';

    private function buildReadyPickupMessage(Penjualan $penjualan): string
    {
        $namaPasien = $penjualan->nama_pasien ?: 'Pelanggan';
        $kode = $penjualan->kode_penjualan ?: '-';
        $cabang = $penjualan->branch->name ?? 'Optik Melati';

        return "Halo Bapak/Ibu {$namaPasien},\n\n"
            . "Kami informasikan bahwa kacamata Anda dengan nomor nota *{$kode}* telah selesai dikerjakan dan sudah dapat diambil di *{$cabang}*.\n\n"
            . "*Jam Operasional*\n"
            . "Senin - Sabtu: 08.00 - 16.30 WIB\n"
            . "Istirahat: 12.30 - 13.30 WIB\n"
            . "Minggu: Tutup\n\n"
            . "Mohon melakukan pengambilan pada jam operasional. Kami tidak melayani pengambilan di luar jam kerja.\n\n"
            . "Terima kasih atas kepercayaan Anda kepada Optik Melati. Kami tunggu kedatangannya.";
    }

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
        $transaksi = Penjualan::with('user', 'branch', 'pasien.prescriptions', 'dokter', 'details.itemable')
            ->where(function($query) use ($barcode) {
                $query->where('barcode', $barcode)
                      ->orWhere('kode_penjualan', 'LIKE', $barcode . '%');
            })
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        $latestPrescription = optional($transaksi->pasien)->prescriptions
            ? $transaksi->pasien->prescriptions->sortBy('tanggal')->last()
            : null;

        $transactionPayload = [
            'id' => $transaksi->id,
            'kode_penjualan' => $transaksi->kode_penjualan,
            'tanggal' => tanggal_indonesia($transaksi->created_at, false),
            'nama_pasien' => $transaksi->pasien->nama_pasien ?? $transaksi->nama_pasien,
            'nohp' => $transaksi->pasien->nohp ?? null,
            'service_type' => $transaksi->pasien->service_type ?? $transaksi->pasien_service_type ?? '-',
            'no_bpjs' => $transaksi->pasien->no_bpjs ?? '-',
            'status' => $transaksi->status ?? 'Belum Lunas',
            'total' => (float) ($transaksi->total ?? 0),
            'bayar' => (float) ($transaksi->bayar ?? 0),
            'kekurangan' => (float) ($transaksi->kekurangan ?? 0),
            'status_pengerjaan' => $transaksi->status_pengerjaan,
            'barcode' => $transaksi->barcode,
            'resep_terakhir' => $latestPrescription ? [
                'tanggal' => tanggal_indonesia($latestPrescription->tanggal, false),
                'od_sph' => $latestPrescription->od_sph,
                'od_cyl' => $latestPrescription->od_cyl,
                'od_axis' => $latestPrescription->od_axis,
                'os_sph' => $latestPrescription->os_sph,
                'os_cyl' => $latestPrescription->os_cyl,
                'os_axis' => $latestPrescription->os_axis,
                'add' => $latestPrescription->add,
                'pd' => $latestPrescription->pd,
            ] : null,
        ];

        return response()->json([
            'success' => true,
            'transaction' => $transactionPayload,
            // Backward compatibility for legacy mobile views expecting response.data
            'data' => array_merge($transactionPayload, [
                'pasien' => [
                    'nama_pasien' => $transactionPayload['nama_pasien'],
                ],
            ]),
            'message' => 'Transaksi ditemukan'
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'transaksi_id' => 'required|exists:penjualan,id',
            'status_pengerjaan' => 'required|in:Sedang Mengerjakan,Lensa Di Pesan,Lensa Datang,Sudah Di Kerjakan,Kirim WA,Sudah Di Ambil',
            'nohp' => 'nullable|string|max:30',
        ]);

        $transaksi = Penjualan::with(['pasien', 'branch'])->findOrFail($request->transaksi_id);
        $user = auth()->user();

        // Update status berdasarkan role
        switch ($request->status_pengerjaan) {
            case self::WORK_STATUS_SEDANG_MENGERJAKAN:
            case self::WORK_STATUS_LENSA_DI_PESAN:
            case self::WORK_STATUS_LENSA_DATANG:
                if (!in_array($user->role, ['passet bantu', 'admin', 'super admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status ini'
                    ], 403);
                }
                $transaksi->passet_by_user_id = $user->id;
                break;
                
            case self::WORK_STATUS_SUDAH_DI_KERJAKAN:
                if (!in_array($user->role, ['passet bantu', 'admin', 'super admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status ini'
                    ], 403);
                }
                $transaksi->waktu_selesai_dikerjakan = now();
                break;

            case self::WORK_STATUS_KIRIM_WA:
                if (!in_array($user->role, ['kasir', 'admin', 'super admin', 'passet bantu'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status ini'
                    ], 403);
                }
                break;
                
            case self::WORK_STATUS_SUDAH_DI_AMBIL:
                if (!in_array($user->role, ['kasir', 'admin', 'super admin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk mengubah status ini'
                    ], 403);
                }

                if (($transaksi->status ?? 'Belum Lunas') !== 'Lunas') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Transaksi belum lunas. Status Sudah Di Ambil hanya untuk transaksi yang sudah Lunas.'
                    ], 422);
                }

                $transaksi->waktu_sudah_diambil = now();
                break;
        }

        $transaksi->status_pengerjaan = $request->status_pengerjaan;

        if ($request->status_pengerjaan === self::WORK_STATUS_KIRIM_WA) {
            if (!$transaksi->pasien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan pada transaksi ini. Tidak bisa kirim WA.',
                ], 422);
            }

            $inputNoHp = trim((string) $request->input('nohp', ''));
            if ($inputNoHp !== '') {
                $normalizedNoHp = WhatsAppHelper::normalizePhoneNumber($inputNoHp);
                $transaksi->pasien->nohp = $normalizedNoHp ?: $inputNoHp;
                $transaksi->pasien->save();
            }

            if (trim((string) ($transaksi->pasien->nohp ?? '')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor HP pasien belum ada. Isi nomor HP untuk status Kirim WA.',
                ], 422);
            }
        }

        $transaksi->save();

        $whatsapp = null;
        if ($request->status_pengerjaan === self::WORK_STATUS_KIRIM_WA) {
            $phone = WhatsAppHelper::normalizePhoneNumber($transaksi->pasien?->nohp ?? null);
            if ($phone) {
                $message = $this->buildReadyPickupMessage($transaksi);
                $gateway = WhatsAppHelper::sendViaGateway($phone, $message);
                $whatsapp = [
                    'success' => (bool) ($gateway['success'] ?? false),
                    'message' => $gateway['message'] ?? 'Notifikasi WhatsApp diproses.',
                    'link' => WhatsAppHelper::buildShareLink($phone, $message),
                ];
            } else {
                $whatsapp = [
                    'success' => false,
                    'message' => 'Nomor WhatsApp pasien tidak tersedia.',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'data' => $transaksi->fresh(['user', 'branch', 'pasien', 'dokter']),
            'whatsapp' => $whatsapp,
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
