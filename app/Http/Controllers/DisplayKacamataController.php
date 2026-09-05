<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\DisplayMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisplayKacamataController extends Controller
{
    private const ACTIVE_STATUSES = [
        'Sudah Di Kerjakan',
        'Kirim WA',
    ];

    public function index()
    {
        return view('display.kacamata', [
            'branchName' => optional(auth()->user()->branch)->name ?: 'Optik Melati',
        ]);
    }

    public function media()
    {
        return response()->json(DisplayMedia::query()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get(['id', 'judul', 'tipe', 'path'])
            ->map(fn (DisplayMedia $item) => [
                'id' => $item->id,
                'judul' => $item->judul,
                'tipe' => $item->tipe,
                'url' => route('display.kacamata.media.file', $item),
            ])->values());
    }

    public function mediaFile(DisplayMedia $displayMedia)
    {
        abort_unless($displayMedia->is_active, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($displayMedia->path), 404);

        return response()->file($disk->path($displayMedia->path), [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function data(Request $request)
    {
        $user = $request->user();
        $branchId = $user->isSuperAdmin() || $user->isAdmin()
            ? ($request->integer('branch_id') ?: session('active_branch_id') ?: $user->branch_id)
            : $user->branch_id;

        $query = Penjualan::query()
            ->with('pasien')
            ->whereIn('status_pengerjaan', self::ACTIVE_STATUSES)
            ->latest('updated_at')
            ->limit(40);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereRaw('1 = 0');
        }

        $orders = $query->get()->map(function (Penjualan $order) {
            return [
                'id' => $order->id,
                'code' => $order->kode_penjualan ?: ('TRX-' . $order->id),
                'patient' => $this->maskedPatientName($order->nama_pasien),
                'status' => $order->status_pengerjaan,
                'payment_status' => $order->status ?: 'Belum Lunas',
                'created_at' => optional($order->created_at)->format('d/m/Y'),
                'service_type' => $order->pasien?->service_type ?: 'UMUM',
                'updated_at' => optional($order->updated_at)->format('H:i'),
                'urls' => [
                    'send_wa' => route('penjualan.update_status_pengerjaan', $order->id),
                    'take' => route('penjualan.diambil', $order->id),
                    'pay' => route('penjualan.lunas', $order->id),
                ],
            ];
        })->values();

        $counts = collect(self::ACTIVE_STATUSES)->mapWithKeys(function (string $status) use ($orders) {
            return [$status => $orders->where('status', $status)->count()];
        });

        return response()->json([
            'updated_at' => now()->format('d/m/Y H:i'),
            'orders' => $orders,
            'counts' => $counts,
            'total' => $orders->count(),
        ]);
    }

    private function maskedPatientName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

        if (!$parts) {
            return 'Pasien';
        }

        $firstName = $parts[0];
        $lastInitial = count($parts) > 1 ? strtoupper(substr(end($parts), 0, 1)) . '.' : '';

        return trim($firstName . ' ' . $lastInitial);
    }
}