<?php

namespace App\Http\Controllers;

use App\Models\Kwitansi;
use Illuminate\Http\Request;

class KwitansiController extends Controller
{
    public function index()
    {
        $kwitansis = Kwitansi::with('creator')->latest()->paginate(20);

        return view('kwitansi.index', compact('kwitansis'));
    }

    public function create()
    {
        return view('kwitansi.create');
    }

    public function createKacamata()
    {
        return view('kwitansi.create-kacamata');
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'nomor' => 'nullable|string|max:100',
            'tempat_tanggal' => 'nullable|string|max:150',
            'penerima_dari' => 'required|string|max:255',
            'untuk_pembayaran' => 'nullable|string|max:255',
            'nama_pembuat' => 'nullable|string|max:255',
            'jumlah' => 'nullable|numeric|min:0',
        ]);

        $jumlah = isset($validated['jumlah']) ? (float) $validated['jumlah'] : null;
        $validated['terbilang'] = '';

        if ($jumlah !== null && $jumlah > 0) {
            $validated['terbilang'] = ucfirst(preg_replace('/\s+/', ' ', trim($this->terbilang((int) round($jumlah))))) . ' rupiah';
        }

        Kwitansi::create([
            'jenis' => 'umum',
            'nomor' => $validated['nomor'] ?? null,
            'tempat_tanggal' => $validated['tempat_tanggal'] ?? null,
            'penerima_dari' => $validated['penerima_dari'],
            'untuk_pembayaran' => $validated['untuk_pembayaran'] ?? null,
            'nama_pembuat' => $validated['nama_pembuat'] ?? null,
            'jumlah' => $jumlah,
            'terbilang' => $validated['terbilang'],
            'created_by' => auth()->id(),
        ]);

        return view('kwitansi.print', [
            'data' => $validated,
            'jumlah' => $jumlah,
        ]);
    }

    public function printKacamata(Request $request)
    {
        $validated = $request->validate([
            'nomor' => 'nullable|string|max:100',
            'tempat_tanggal' => 'nullable|string|max:150',
            'penerima_dari' => 'required|string|max:255',
            'nama_pembuat' => 'nullable|string|max:255',
            'untuk_pembayaran' => 'required|string|max:255',
            'harga_frame' => 'required|numeric|min:0',
            'harga_lensa' => 'required|numeric|min:0',
        ]);

        $hargaFrame = (float) $validated['harga_frame'];
        $jumlahFrame = 1;
        $hargaLensa = (float) $validated['harga_lensa'];
        $jumlahLensa = 1;
        $totalFrame = $hargaFrame * $jumlahFrame;
        $totalLensa = $hargaLensa * $jumlahLensa;
        $jumlah = $totalFrame + $totalLensa;

        $validated['terbilang'] = $jumlah > 0
            ? ucfirst(preg_replace('/\s+/', ' ', trim($this->terbilang((int) round($jumlah))))) . ' rupiah'
            : '';

        Kwitansi::create([
            'jenis' => 'kacamata',
            'nomor' => $validated['nomor'] ?? null,
            'tempat_tanggal' => $validated['tempat_tanggal'] ?? null,
            'penerima_dari' => $validated['penerima_dari'],
            'untuk_pembayaran' => $validated['untuk_pembayaran'],
            'nama_pembuat' => $validated['nama_pembuat'] ?? null,
            'harga_frame' => $hargaFrame,
            'harga_lensa' => $hargaLensa,
            'jumlah' => $jumlah,
            'terbilang' => $validated['terbilang'],
            'created_by' => auth()->id(),
        ]);

        return view('kwitansi.print-kacamata', [
            'data' => $validated,
            'hargaFrame' => $hargaFrame,
            'jumlahFrame' => $jumlahFrame,
            'totalFrame' => $totalFrame,
            'hargaLensa' => $hargaLensa,
            'jumlahLensa' => $jumlahLensa,
            'totalLensa' => $totalLensa,
            'jumlah' => $jumlah,
        ]);
    }

    private function terbilang(int $nilai): string
    {
        $angka = [
            0 => '',
            1 => 'satu',
            2 => 'dua',
            3 => 'tiga',
            4 => 'empat',
            5 => 'lima',
            6 => 'enam',
            7 => 'tujuh',
            8 => 'delapan',
            9 => 'sembilan',
            10 => 'sepuluh',
            11 => 'sebelas',
        ];

        if ($nilai < 12) {
            return $angka[$nilai];
        }

        if ($nilai < 20) {
            return $this->terbilang($nilai - 10) . ' belas';
        }

        if ($nilai < 100) {
            return $this->terbilang((int) floor($nilai / 10)) . ' puluh ' . $this->terbilang($nilai % 10);
        }

        if ($nilai < 200) {
            return 'seratus ' . $this->terbilang($nilai - 100);
        }

        if ($nilai < 1000) {
            return $this->terbilang((int) floor($nilai / 100)) . ' ratus ' . $this->terbilang($nilai % 100);
        }

        if ($nilai < 2000) {
            return 'seribu ' . $this->terbilang($nilai - 1000);
        }

        if ($nilai < 1000000) {
            return $this->terbilang((int) floor($nilai / 1000)) . ' ribu ' . $this->terbilang($nilai % 1000);
        }

        if ($nilai < 1000000000) {
            return $this->terbilang((int) floor($nilai / 1000000)) . ' juta ' . $this->terbilang($nilai % 1000000);
        }

        if ($nilai < 1000000000000) {
            return $this->terbilang((int) floor($nilai / 1000000000)) . ' miliar ' . $this->terbilang($nilai % 1000000000);
        }

        return (string) $nilai;
    }
}
