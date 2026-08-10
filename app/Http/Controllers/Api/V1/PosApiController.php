<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\TransactionComment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosApiController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $queryText = trim($request->q);
        $limit = (int) $request->input('limit', 20);

        $transactions = Penjualan::with(['user', 'branch', 'pasien'])
            ->where(function ($query) use ($queryText) {
                $query->where('kode_penjualan', 'like', '%' . $queryText . '%')
                    ->orWhere('barcode', 'like', '%' . $queryText . '%')
                    ->orWhere('nama_pasien_manual', 'like', '%' . $queryText . '%')
                    ->orWhereHas('pasien', function ($pasienQuery) use ($queryText) {
                        $pasienQuery->where('nama_pasien', 'like', '%' . $queryText . '%');
                    });
            })
            ->orderByDesc('tanggal')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Hasil pencarian ditemukan',
            'data' => $transactions->map(function ($transaction) {
                return $this->transformTransaction($transaction);
            })->values(),
        ]);
    }

    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'branch_id' => 'nullable|integer',
        ]);

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $query = Penjualan::with(['user', 'branch', 'pasien'])
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $transactions = $query->orderByDesc('tanggal')->get();

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_revenue' => (float) $transactions->sum('total'),
            'total_paid' => (float) $transactions->sum('bayar'),
            'total_outstanding' => (float) $transactions->sum('kekurangan'),
        ];

        $byBranch = $transactions
            ->groupBy(function ($transaction) {
                return optional($transaction->branch)->name ?? 'Tanpa Cabang';
            })
            ->map(function ($items, $branchName) {
                return [
                    'branch_name' => $branchName,
                    'total_transactions' => $items->count(),
                    'total_revenue' => (float) $items->sum('total'),
                    'total_paid' => (float) $items->sum('bayar'),
                    'total_outstanding' => (float) $items->sum('kekurangan'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dimuat',
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => $summary,
            'by_branch' => $byBranch,
            'data' => $transactions->map(function ($transaction) {
                return $this->transformTransaction($transaction);
            })->values(),
        ]);
    }

    public function comments(Request $request)
    {
        $query = TransactionComment::with(['user', 'penjualan.branch', 'penjualan.pasien'])
            ->orderByDesc('id');

        if ($request->filled('penjualan_id')) {
            $query->where('penjualan_id', $request->penjualan_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(100)->get()->map(function ($comment) {
                return $this->transformComment($comment);
            })->values(),
        ]);
    }

    public function storeComment(Request $request)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualan,id',
            'comment' => 'required|string|max:1000',
        ]);

        $comment = TransactionComment::create([
            'penjualan_id' => $request->penjualan_id,
            'user_id' => $request->user()->id,
            'comment' => trim($request->comment),
        ]);

        $comment->load(['user', 'penjualan.branch', 'penjualan.pasien']);

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil disimpan',
            'data' => $this->transformComment($comment),
        ], 201);
    }

    private function transformTransaction(Penjualan $transaction): array
    {
        return [
            'id' => $transaction->id,
            'kode_penjualan' => $transaction->kode_penjualan,
            'tanggal' => optional($transaction->tanggal)->format('Y-m-d'),
            'nama_pasien' => $transaction->nama_pasien,
            'branch_id' => $transaction->branch_id,
            'branch_name' => optional($transaction->branch)->name,
            'kasir' => optional($transaction->user)->name,
            'status' => $transaction->status,
            'status_pengerjaan' => $transaction->status_pengerjaan,
            'total' => (float) ($transaction->total ?? 0),
            'bayar' => (float) ($transaction->bayar ?? 0),
            'kekurangan' => (float) ($transaction->kekurangan ?? 0),
            'barcode' => $transaction->barcode,
            'signature_date' => optional($transaction->signature_date)->toDateTimeString(),
            'created_at' => optional($transaction->created_at)->toDateTimeString(),
            'updated_at' => optional($transaction->updated_at)->toDateTimeString(),
        ];
    }

    private function transformComment(TransactionComment $comment): array
    {
        return [
            'id' => $comment->id,
            'penjualan_id' => $comment->penjualan_id,
            'kode_penjualan' => optional($comment->penjualan)->kode_penjualan,
            'comment' => $comment->comment,
            'user_id' => $comment->user_id,
            'user_name' => optional($comment->user)->name,
            'branch_name' => optional(optional($comment->penjualan)->branch)->name,
            'patient_name' => optional(optional($comment->penjualan)->pasien)->nama_pasien,
            'created_at' => optional($comment->created_at)->toDateTimeString(),
        ];
    }
}