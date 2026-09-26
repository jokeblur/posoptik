<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherSaldoLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::with('creator')->withCount('penjualans')->latest()->paginate(20);
        $user = auth()->user();
        $canManage = $user->isAdmin() || $user->isSuperAdmin();

        return view('voucher.index', compact('vouchers', 'canManage'));
    }

    public function create()
    {
        return view('voucher.form', ['voucher' => new Voucher()]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $data = $this->validatedData($request);
            $voucher = Voucher::create($data + ['saldo' => 0, 'created_by' => auth()->id()]);

            if (!$voucher->isDiskon() && (float) $voucher->nominal > 0) {
                $voucher->catatSaldo('tambah', (float) $voucher->nominal, auth()->id(), null, 'Saldo awal');
            }
        });

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher)
    {
        return view('voucher.form', compact('voucher'));
    }

    public function print(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'copies' => 'required|integer|min:1|max:50',
            'side' => 'nullable|in:front,back',
        ]);

        return view('voucher.print', [
            'voucher' => $voucher,
            'copies' => (int) $validated['copies'],
            'side' => $validated['side'] ?? 'front',
        ]);
    }

    public function tambahSaldo(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'jumlah' => 'required|numeric|min:1|max:1000000000',
            'keterangan' => 'nullable|string|max:255',
        ], [], ['jumlah' => 'jumlah saldo']);

        if ($voucher->isDiskon()) {
            return back()->withErrors(['jumlah' => 'Voucher diskon (%) tidak memiliki saldo.']);
        }

        DB::transaction(function () use ($voucher, $validated) {
            $locked = Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail();
            $locked->catatSaldo('tambah', (float) $validated['jumlah'], auth()->id(), null, $validated['keterangan'] ?? null);
        });

        return redirect()->route('voucher.index')->with(
            'success',
            'Saldo voucher ' . $voucher->kode . ' ditambah Rp ' . number_format((float) $validated['jumlah'], 0, ',', '.') . '.'
        );
    }

    public function check(Request $request)
    {
        $kode = strtoupper(trim((string) $request->query('kode', '')));

        if ($kode === '') {
            return response()->json([
                'found' => false,
                'message' => 'Kode voucher wajib diisi.',
            ], 422);
        }

        $voucher = Voucher::with(['creator', 'saldoLogs' => function ($query) {
            $query->with(['penjualan:id,kode_penjualan', 'user:id,name'])->limit(10);
        }])->withCount('penjualans')->where('kode', $kode)->first();

        if (!$voucher) {
            return response()->json([
                'found' => false,
                'kode' => $kode,
                'message' => 'Voucher dengan kode ' . $kode . ' tidak ditemukan.',
            ], 404);
        }

        $user = auth()->user();
        $terakhirDipakai = $voucher->penjualans()->latest('id')->first();

        return response()->json([
            'found' => true,
            'status' => $voucher->statusInfo(),
            'voucher' => [
                'id' => $voucher->id,
                'kode' => $voucher->kode,
                'jenis_nominal' => $voucher->isDiskon() ? 'diskon' : 'uang',
                'nominal' => (float) $voucher->nominal,
                'nominal_label' => $voucher->nominalLabel(),
                'saldo' => $voucher->isDiskon() ? null : (float) $voucher->saldo,
                'saldo_label' => $voucher->saldoLabel(),
                'jumlah_pemakaian' => $voucher->jumlahPemakaian(),
                'berlaku_mulai' => $voucher->berlaku_mulai ? $voucher->berlaku_mulai->format('d-m-Y') : '-',
                'berlaku_sampai' => $voucher->berlaku_sampai ? $voucher->berlaku_sampai->format('d-m-Y') : '-',
                'syarat_ketentuan' => $voucher->syarat_ketentuan ?: '-',
                'dibuat_oleh' => optional($voucher->creator)->name ?: '-',
                'edit_url' => ($user->isAdmin() || $user->isSuperAdmin()) ? route('voucher.edit', $voucher) : null,
            ],
            'dipakai_di' => $terakhirDipakai ? [
                'kode_penjualan' => $terakhirDipakai->kode_penjualan,
                'tanggal' => $terakhirDipakai->tanggal ? $terakhirDipakai->tanggal->format('d-m-Y') : '-',
                'potongan_label' => 'Rp ' . number_format((float) $terakhirDipakai->voucher_potongan, 0, ',', '.'),
                'url' => route('penjualan.show', $terakhirDipakai->id),
            ] : null,
            'riwayat' => $voucher->saldoLogs->map(function (VoucherSaldoLog $log) {
                return [
                    'tanggal' => $log->created_at ? $log->created_at->format('d-m-Y H:i') : '-',
                    'jenis' => $log->jenis,
                    'jenis_label' => VoucherSaldoLog::JENIS_LABEL[$log->jenis] ?? $log->jenis,
                    'jumlah_label' => ($log->jenis === 'pakai' ? '-' : '+') . 'Rp ' . number_format($log->jumlah, 0, ',', '.'),
                    'saldo_label' => 'Rp ' . number_format($log->saldo_sesudah, 0, ',', '.'),
                    'keterangan' => $log->keterangan ?: (optional($log->penjualan)->kode_penjualan ?? '-'),
                    'oleh' => optional($log->user)->name ?: '-',
                ];
            })->values(),
        ]);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $this->validatedData($request, $voucher);

        // Jenis & nilai terkunci setelah dibuat: saldo voucher uang diubah lewat "Tambah Saldo"
        // agar riwayat saldonya tetap cocok. Persen voucher diskon boleh diubah selama belum dipakai.
        $data['jenis_nominal'] = $voucher->jenis_nominal ?? 'uang';
        if (!$voucher->isDiskon() || $voucher->jumlahPemakaian() > 0) {
            $data['nominal'] = $voucher->nominal;
        }

        $voucher->update($data);

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge([
            'kode' => strtoupper(trim((string) $request->input('kode'))),
        ]);

        $jenis = $voucher ? ($voucher->jenis_nominal ?? 'uang') : $request->input('jenis_nominal', 'uang');
        $nominalRule = $jenis === 'diskon' ? 'required|numeric|min:1|max:100' : 'required|numeric|min:0';

        return $request->validate([
            'kode' => [
                'required',
                'string',
                'max:100',
                Rule::unique('vouchers', 'kode')->ignore($voucher ? $voucher->id : null),
            ],
            'jenis_nominal' => $voucher ? 'nullable|in:uang,diskon' : 'required|in:uang,diskon',
            'nominal' => $voucher && $jenis !== 'diskon' ? 'nullable|numeric|min:0' : $nominalRule,
            'syarat_ketentuan' => 'nullable|string|max:5000',
            'berlaku_mulai' => 'nullable|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:berlaku_mulai',
            'aktif' => 'nullable|boolean',
        ]) + [
            'aktif' => $request->boolean('aktif'),
            'jenis_nominal' => $jenis,
        ];
    }
}
