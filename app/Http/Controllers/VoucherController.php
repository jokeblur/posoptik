<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherDana;
use App\Models\VoucherDanaLog;
use App\Models\VoucherSaldoLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::with('creator')->withCount('penjualans')->latest()->paginate(20);
        $user = auth()->user();
        $canManage = $user->isAdmin() || $user->isSuperAdmin();
        $dana = VoucherDana::utama();
        $danaLogs = VoucherDanaLog::with('user:id,name')->latest('id')->limit(10)->get();
        // Hanya voucher uang yang belum dipakai; sisa saldo voucher terpakai sudah hangus.
        $saldoBeredar = (float) Voucher::where('jenis_nominal', '!=', 'diskon')->doesntHave('penjualans')->sum('saldo');

        return view('voucher.index', compact('vouchers', 'canManage', 'dana', 'danaLogs', 'saldoBeredar'));
    }

    public function create()
    {
        return view('voucher.form', ['voucher' => new Voucher(), 'dana' => VoucherDana::utama()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $request->validate([
            'jumlah_voucher' => 'nullable|integer|min:1|max:100',
        ], [], ['jumlah_voucher' => 'jumlah voucher']);
        $jumlah = max(1, (int) $request->input('jumlah_voucher', 1));

        // Lebih dari satu: tiap voucher dapat kode unik KODE-001, KODE-002, ... (satu batch),
        // jadi satu kode hanya untuk satu orang.
        $kodeList = [$data['kode']];
        if ($jumlah > 1) {
            if (strlen($data['kode']) > 95) {
                return back()->withInput()->withErrors(['kode' => 'Kode voucher maksimal 95 karakter bila membuat lebih dari satu voucher.']);
            }
            $data['batch_kode'] = $data['kode'];
            $kodeList = $this->kodeBaruBatch($data['kode'], $jumlah);
        }

        // Desain dipakai bersama oleh semua voucher dalam batch.
        $desainBaru = $this->simpanDesain($request);
        $data = $desainBaru + $data;

        try {
            DB::transaction(function () use ($data, $kodeList) {
                $dana = VoucherDana::utama(true);

                foreach ($kodeList as $kode) {
                    $voucher = Voucher::create(['kode' => $kode] + $data + ['saldo' => 0, 'created_by' => auth()->id()]);

                    // Saldo awal voucher uang diambil dari dana voucher.
                    if (!$voucher->isDiskon() && (float) $voucher->nominal > 0) {
                        $dana->catat('ambil', (float) $voucher->nominal, auth()->id(), $voucher, 'Saldo awal voucher');
                        $voucher->catatSaldo('tambah', (float) $voucher->nominal, auth()->id(), null, 'Saldo awal');
                    }
                }
            });
        } catch (RuntimeException $e) {
            Storage::disk('public')->delete(array_values($desainBaru));

            return back()->withInput()->withErrors(['nominal' => $e->getMessage()]);
        }

        return redirect()->route('voucher.index')->with(
            'success',
            $jumlah > 1
                ? $jumlah . ' voucher berhasil dibuat (' . $kodeList[0] . ' s/d ' . end($kodeList) . ').'
                : 'Voucher berhasil dibuat.'
        );
    }

    /**
     * Kode unik dalam batch: BATCH-001, BATCH-002, ... (melewati kode yang sudah ada).
     */
    private function kodeBaruBatch(string $batch, int $jumlah): array
    {
        $sudahAda = Voucher::where('kode', 'like', str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $batch) . '-%')
            ->pluck('kode')
            ->flip();

        $kodeList = [];
        for ($n = 1; count($kodeList) < $jumlah; $n++) {
            $kode = $batch . '-' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            if (!isset($sudahAda[$kode])) {
                $kodeList[] = $kode;
            }
        }

        return $kodeList;
    }

    public function edit(Voucher $voucher)
    {
        return view('voucher.form', ['voucher' => $voucher, 'dana' => VoucherDana::utama()]);
    }

    public function print(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'semua' => 'nullable|boolean',
            'side' => 'nullable|in:front,back',
        ]);
        $semua = $request->boolean('semua') && $voucher->batch_kode;

        // Tiap kartu = satu voucher dengan kodenya sendiri (voucher sekali pakai, jadi tidak dicetak ganda).
        $vouchers = $semua
            ? Voucher::where('batch_kode', $voucher->batch_kode)->orderBy('kode')->get()
            : collect([$voucher]);

        return view('voucher.print', [
            'voucher' => $voucher,
            'vouchers' => $vouchers,
            'semua' => (bool) $semua,
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
        if ($voucher->jumlahPemakaian() > 0) {
            return back()->withErrors(['jumlah' => 'Voucher ' . $voucher->kode . ' sudah dipakai dan tidak bisa diisi lagi.']);
        }

        try {
            DB::transaction(function () use ($voucher, $validated) {
                $dana = VoucherDana::utama(true);
                $locked = Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail();
                $dana->catat('ambil', (float) $validated['jumlah'], auth()->id(), $locked, $validated['keterangan'] ?? 'Tambah saldo voucher');
                $locked->catatSaldo('tambah', (float) $validated['jumlah'], auth()->id(), null, $validated['keterangan'] ?? null);
                // Voucher belum dipakai: nominal (yang tercetak) ikut saldo.
                $locked->update(['nominal' => $locked->saldo]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['jumlah' => $e->getMessage()]);
        }

        return redirect()->route('voucher.index')->with(
            'success',
            'Saldo voucher ' . $voucher->kode . ' ditambah Rp ' . number_format((float) $validated['jumlah'], 0, ',', '.') . '.'
        );
    }

    /**
     * Isi atau tarik dana induk voucher.
     */
    public function dana(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'required|in:isi,tarik',
            'jumlah' => 'required|numeric|min:1|max:1000000000',
            'keterangan' => 'nullable|string|max:255',
        ], [], ['jumlah' => 'jumlah dana']);

        try {
            DB::transaction(function () use ($validated) {
                VoucherDana::utama(true)->catat(
                    $validated['jenis'],
                    (float) $validated['jumlah'],
                    auth()->id(),
                    null,
                    $validated['keterangan'] ?? null
                );
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['jumlah' => $e->getMessage()]);
        }

        return redirect()->route('voucher.index')->with(
            'success',
            'Dana voucher ' . ($validated['jenis'] === 'isi' ? 'ditambah' : 'ditarik')
                . ' Rp ' . number_format((float) $validated['jumlah'], 0, ',', '.') . '.'
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
                    'jumlah_label' => (in_array($log->jenis, ['pakai', 'kurang'], true) ? '-' : '+') . 'Rp ' . number_format($log->jumlah, 0, ',', '.'),
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
        $bisaEditSaldo = !$voucher->isDiskon() && $voucher->jumlahPemakaian() === 0;
        $saldoBaru = null;
        if ($bisaEditSaldo && $request->filled('saldo')) {
            $saldoBaru = round((float) $request->validate([
                'saldo' => 'numeric|min:0|max:1000000000',
            ], [], ['saldo' => 'saldo voucher'])['saldo'], 2);
        }

        // Jenis terkunci setelah dibuat. Nominal voucher uang mengikuti saldo yang diedit (voucher belum dipakai);
        // persen voucher diskon boleh diubah selama belum dipakai.
        $data['jenis_nominal'] = $voucher->jenis_nominal ?? 'uang';
        if ($saldoBaru !== null) {
            $data['nominal'] = $saldoBaru;
        } elseif (!$voucher->isDiskon() || $voucher->jumlahPemakaian() > 0) {
            $data['nominal'] = $voucher->nominal;
        }

        // Desain baru / dihapus berlaku untuk voucher ini dan satu batch-nya.
        $desainBaru = $this->simpanDesain($request);
        $desainUbah = $desainBaru;
        foreach (['desain_depan', 'desain_belakang'] as $kolom) {
            if (!isset($desainUbah[$kolom]) && $request->boolean('hapus_' . $kolom)) {
                $desainUbah[$kolom] = null;
            }
        }
        $desainLama = array_filter(array_intersect_key($voucher->only(['desain_depan', 'desain_belakang']), $desainUbah));

        try {
            DB::transaction(function () use ($voucher, $data, $saldoBaru, $desainUbah) {
                $dana = VoucherDana::utama(true);
                $locked = Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail();
                $locked->update($desainUbah + $data);

                if ($desainUbah && $locked->batch_kode) {
                    Voucher::where('batch_kode', $locked->batch_kode)->whereKeyNot($locked->id)->update($desainUbah);
                }

                // Saldo voucher uang yang belum dipakai boleh diedit: selisihnya diambil dari / kembali ke dana.
                if ($saldoBaru !== null) {
                    $selisih = round($saldoBaru - (float) $locked->saldo, 2);
                    if ($selisih > 0) {
                        $dana->catat('ambil', $selisih, auth()->id(), $locked, 'Edit saldo voucher');
                        $locked->catatSaldo('tambah', $selisih, auth()->id(), null, 'Edit saldo');
                    } elseif ($selisih < 0) {
                        $locked->catatSaldo('kurang', -$selisih, auth()->id(), null, 'Edit saldo');
                        $dana->catat('kembali', -$selisih, auth()->id(), $locked, 'Edit saldo voucher');
                    }
                }
            });
        } catch (RuntimeException $e) {
            Storage::disk('public')->delete(array_values($desainBaru));

            return back()->withInput()->withErrors(['saldo' => $e->getMessage()]);
        }

        $this->hapusDesainTakTerpakai(array_values($desainLama));

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    /**
     * Simpan file desain yang diunggah; mengembalikan [kolom => path] untuk sisi yang diunggah.
     */
    private function simpanDesain(Request $request): array
    {
        $request->validate([
            'desain_depan' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'desain_belakang' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [], ['desain_depan' => 'desain depan', 'desain_belakang' => 'desain belakang']);

        $paths = [];
        foreach (['desain_depan', 'desain_belakang'] as $kolom) {
            if ($request->hasFile($kolom)) {
                $paths[$kolom] = $request->file($kolom)->store('voucher-desain', 'public');
            }
        }

        return $paths;
    }

    /**
     * Hapus file desain yang sudah tidak dipakai voucher mana pun (desain bisa dipakai satu batch).
     */
    private function hapusDesainTakTerpakai(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            $masihDipakai = Voucher::where('desain_depan', $path)->orWhere('desain_belakang', $path)->exists();
            if (!$masihDipakai) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function destroy(Voucher $voucher)
    {
        DB::transaction(function () use ($voucher) {
            $dana = VoucherDana::utama(true);
            $this->hapusVoucher(Voucher::whereKey($voucher->id)->lockForUpdate()->firstOrFail(), $dana);
        });

        $this->hapusDesainTakTerpakai([$voucher->desain_depan, $voucher->desain_belakang]);

        return redirect()->route('voucher.index')->with('success', 'Voucher berhasil dihapus.');
    }

    /**
     * Hapus banyak voucher sekaligus (dipilih lewat checkbox di daftar voucher).
     */
    public function destroyBanyak(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ], ['ids.required' => 'Pilih minimal satu voucher untuk dihapus.']);

        $desain = [];
        $jumlah = DB::transaction(function () use ($validated, &$desain) {
            $dana = VoucherDana::utama(true);
            $vouchers = Voucher::whereIn('id', $validated['ids'])->lockForUpdate()->get();

            foreach ($vouchers as $voucher) {
                $desain[] = $voucher->desain_depan;
                $desain[] = $voucher->desain_belakang;
                $this->hapusVoucher($voucher, $dana);
            }

            return $vouchers->count();
        });

        $this->hapusDesainTakTerpakai($desain);

        return redirect()->route('voucher.index')->with('success', $jumlah . ' voucher berhasil dihapus.');
    }

    /**
     * Hapus satu voucher yang sudah dikunci; sisa saldo voucher uang kembali ke dana voucher.
     */
    private function hapusVoucher(Voucher $voucher, VoucherDana $dana): void
    {
        if (!$voucher->isDiskon() && (float) $voucher->saldo > 0) {
            $dana->catat('kembali', (float) $voucher->saldo, auth()->id(), $voucher, 'Voucher dihapus');
        }

        $voucher->delete();
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
