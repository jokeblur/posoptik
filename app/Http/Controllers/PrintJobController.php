<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Penjualan;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Print dari tablet lewat PC: tablet mengirim job ke antrian, halaman Print Agent
 * (dibuka di Chrome PC cabang dengan --kiosk-printing) mengambil job dan mencetaknya.
 */
class PrintJobController extends Controller
{
    /** Job yang belum diambil setelah sekian jam dianggap kedaluwarsa agar tidak tiba-tiba tercetak. */
    private const KEDALUWARSA_JAM = 12;

    /**
     * Tablet: kirim nota penjualan ke antrian printer PC cabang transaksi tersebut.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'penjualan_id' => 'required|integer',
            'jenis' => 'required|in:' . implode(',', array_keys(PrintJob::JENIS)),
        ]);

        $user = auth()->user();
        $penjualan = Penjualan::findOrFail($validated['penjualan_id']);

        if (!$user->isSuperAdmin() && !$user->isAdmin() && (int) $penjualan->branch_id !== (int) $user->branch_id) {
            return response()->json(['message' => 'Transaksi ini bukan milik cabang Anda.'], 403);
        }

        $job = PrintJob::create([
            'branch_id' => $penjualan->branch_id,
            'penjualan_id' => $penjualan->id,
            'jenis' => $validated['jenis'],
            'judul' => (PrintJob::JENIS[$validated['jenis']] ?? 'Nota') . ' ' . $penjualan->kode_penjualan,
            'dibuat_oleh' => $user->id,
        ]);

        return response()->json([
            'message' => 'Nota dikirim ke printer PC.',
            'job' => $job->load('pembuat:id,name')->toAgentArray(),
            'status_url' => route('print-jobs.show', $job),
            'agent_aktif' => $this->agentAktif($penjualan->branch_id),
        ], 201);
    }

    /**
     * Tablet: cek status job yang sudah dikirim.
     */
    public function show(PrintJob $printJob)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && (int) $printJob->branch_id !== (int) $user->branch_id) {
            abort(403);
        }

        return response()->json(['job' => $printJob->load('pembuat:id,name')->toAgentArray()]);
    }

    /**
     * PC: halaman Print Agent.
     */
    public function agent()
    {
        $branchId = $this->agentBranchId();

        return view('print-agent.index', [
            'branch' => $branchId ? Branch::find($branchId) : null,
        ]);
    }

    /**
     * PC: ambil satu job berikutnya untuk cabang agent (sekaligus menandai agent masih aktif).
     */
    public function ambil()
    {
        $branchId = $this->agentBranchId();
        if (!$branchId) {
            return response()->json(['message' => 'Cabang untuk Print Agent tidak ditemukan.'], 422);
        }

        cache()->put($this->agentCacheKey($branchId), now()->timestamp, now()->addMinutes(5));

        PrintJob::where('branch_id', $branchId)
            ->where('status', 'menunggu')
            ->where('created_at', '<', now()->subHours(self::KEDALUWARSA_JAM))
            ->update(['status' => 'gagal', 'pesan' => 'Kedaluwarsa: tidak diambil PC dalam ' . self::KEDALUWARSA_JAM . ' jam.', 'selesai_at' => now()]);

        $job = DB::transaction(function () use ($branchId) {
            $job = PrintJob::where('branch_id', $branchId)
                ->where(function ($query) {
                    $query->where('status', 'menunggu')
                        ->orWhere(function ($stuck) {
                            $stuck->where('status', 'proses')
                                ->where('diambil_at', '<', now()->subSeconds(PrintJob::BATAS_PROSES_DETIK));
                        });
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($job) {
                $job->update(['status' => 'proses', 'diambil_at' => now(), 'dicetak_oleh' => auth()->id()]);
            }

            return $job;
        });

        return response()->json([
            'job' => $job ? $job->load('pembuat:id,name')->toAgentArray() : null,
            'riwayat' => PrintJob::with('pembuat:id,name')
                ->where('branch_id', $branchId)
                ->latest('id')
                ->limit(15)
                ->get()
                ->map->toAgentArray()
                ->values(),
        ]);
    }

    /**
     * PC: laporkan hasil cetak sebuah job.
     */
    public function selesai(Request $request, PrintJob $printJob)
    {
        $validated = $request->validate([
            'berhasil' => 'required|boolean',
            'pesan' => 'nullable|string|max:255',
        ]);

        if ((int) $printJob->branch_id !== (int) $this->agentBranchId()) {
            abort(403);
        }

        $printJob->update([
            'status' => $validated['berhasil'] ? 'selesai' : 'gagal',
            'pesan' => $validated['pesan'] ?? null,
            'selesai_at' => now(),
        ]);

        return response()->json(['job' => $printJob->toAgentArray()]);
    }

    /**
     * Cetak ulang job yang gagal / sudah selesai.
     */
    public function ulang(PrintJob $printJob)
    {
        if ((int) $printJob->branch_id !== (int) $this->agentBranchId()) {
            abort(403);
        }

        $printJob->update(['status' => 'menunggu', 'pesan' => null, 'diambil_at' => null, 'selesai_at' => null]);

        return response()->json(['job' => $printJob->toAgentArray()]);
    }

    /**
     * Cabang yang dilayani Print Agent: cabang user, atau cabang aktif untuk admin/super admin.
     */
    private function agentBranchId(): ?int
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $branchId = session('active_branch_id') ?: $user->branch_id;
        } else {
            $branchId = $user->branch_id;
        }

        return $branchId ? (int) $branchId : null;
    }

    private function agentCacheKey(int $branchId): string
    {
        return 'print_agent_aktif_' . $branchId;
    }

    /**
     * Agent dianggap aktif bila mengecek antrian dalam 30 detik terakhir.
     */
    private function agentAktif(?int $branchId): bool
    {
        if (!$branchId) {
            return false;
        }
        $terakhir = cache()->get($this->agentCacheKey($branchId));

        return $terakhir && (now()->timestamp - (int) $terakhir) <= 30;
    }
}
