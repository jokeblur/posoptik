<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Helpers\WhatsAppHelper;

class PassetController extends Controller
{
    public function index()
    {
        return view('passet.index');
    }

    public function data()
    {
        // Semua user yang bisa akses halaman ini (passet, admin, super admin) bisa melihat semua pekerjaan
        $query = Transaksi::with('pasien', 'branch')
            ->whereIn('status_pengerjaan', ['Menunggu Pengerjaan', 'Selesai Dikerjakan']);

        // Optional filter by status (e.g., ?status=Menunggu Pengerjaan)
        if (request()->filled('status')) {
            $query->where('status_pengerjaan', request('status'));
        }

        // Prioritaskan yang menunggu, lalu terbaru
        $query->orderByRaw("CASE WHEN status_pengerjaan = 'Menunggu Pengerjaan' THEN 0 ELSE 1 END")
              ->orderBy('created_at', 'desc');

        return datatables()
            ->of($query)
            ->addIndexColumn()
            ->addColumn('tanggal', function ($transaksi) {
                return tanggal_indonesia($transaksi->created_at, false);
            })
            ->addColumn('tanggal_siap', function ($transaksi) {
                return $transaksi->tanggal_siap ? tanggal_indonesia($transaksi->tanggal_siap, false) : '-';
            })
            ->addColumn('pasien_name', function ($transaksi) {
                return $transaksi->pasien->nama_pasien ?? 'N/A';
            })
            ->addColumn('cabang_name', function ($transaksi) {
                return $transaksi->branch->name ?? 'Pusat';
            })
            ->addColumn('jenis_layanan', function ($transaksi) {
                return $transaksi->pasien_service_type ?: '-';
            })
            ->addColumn('jenis_transaksi', function ($transaksi) {
                return $transaksi->jenis_transaksi ?: '-';
            })
            ->editColumn('status_pengerjaan', function ($transaksi) {
                $statusClass = $transaksi->status_pengerjaan == 'Selesai Dikerjakan' ? 'label-success' : 'label-warning';
                return '<span class="label '. $statusClass .'">'. $transaksi->status_pengerjaan .'</span>';
            })
            ->addColumn('aksi', function ($transaksi) {
                if ($transaksi->status_pengerjaan == 'Menunggu Pengerjaan') {
                    return '
                    <div class="btn-group">
                        <button onclick="markAsSelesai(`'. route('passet.selesai', $transaksi->id) .'`)" class="btn btn-xs btn-primary btn-flat"><i class="fa fa-check"></i></button>
                    </div>
                    ';
                }
                return '';
            })
            ->orderColumn('tanggal', function ($q, $order) {
                $q->orderBy('created_at', $order);
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

        $notificationMessage = 'Status berhasil diubah menjadi Selesai.';

        // Kirim WhatsApp ke pasien bahwa kacamata sudah siap
        try {
            if ($transaksi->pasien && $transaksi->pasien->nohp) {
                $phoneNumber = WhatsAppHelper::normalizePhoneNumber($transaksi->pasien->nohp);
                $pesan = "👓 *Kacamata Anda Sudah Siap!*\n\n";
                $pesan .= "Hai *" . ($transaksi->pasien->nama_pasien ?? 'Pasien') . "*,\n\n";
                $pesan .= "Kacamata Anda sudah selesai dikerjakan dan siap untuk diambil. 🎉\n\n";
                $pesan .= "Silakan kunjungi toko kami untuk mengambil pesanan Anda.\n";
                $pesan .= "No. Kode: *" . $transaksi->kode_penjualan . "*\n\n";
                $pesan .= "Terima kasih telah memilih Optik Melati. 😊";

                if ($phoneNumber) {
                    $gatewayResult = WhatsAppHelper::sendViaGateway($phoneNumber, $pesan);
                    if ($gatewayResult['success']) {
                        $notificationMessage .= ' Notifikasi WhatsApp berhasil dikirim otomatis ke pasien.';
                    } else {
                        $notificationMessage .= ' Notifikasi WhatsApp gagal otomatis, cek konfigurasi gateway.';
                    }
                } else {
                    $notificationMessage .= ' Nomor WhatsApp pasien tidak valid.';
                }

                \Log::info('WhatsApp notification attempt for patient ready status', [
                    'transaksi_id' => $transaksi->id,
                    'pasien_id' => $transaksi->pasien_id,
                    'phone' => $phoneNumber,
                    'message' => $notificationMessage,
                ]);
            } else {
                $notificationMessage .= ' Nomor WhatsApp pasien belum tersedia.';
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send WhatsApp notification for patient', [
                'error' => $e->getMessage(),
                'transaksi_id' => $transaksi->id
            ]);
            $notificationMessage .= ' Terjadi error saat proses kirim notifikasi WhatsApp.';
        }

        return response()->json(['message' => $notificationMessage]);
    }
}
