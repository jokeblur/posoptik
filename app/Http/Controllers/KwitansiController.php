<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KwitansiController extends Controller
{
    public function create()
    {
        return view('kwitansi.create');
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

        return view('kwitansi.print', [
            'data' => $validated,
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
