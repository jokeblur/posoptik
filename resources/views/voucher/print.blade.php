<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher {{ $semua ? $voucher->batch_kode : $voucher->kode }}</title>
    @php
        // Kertas 16,5 x 21,5 cm isi 3 voucher 16,5 x 7 cm (3 x 7 cm = 21 cm; sisa 5 mm jadi jarak antar voucher).
        $kertasList = [
            'voucher' => ['w' => 165, 'h' => 215, 'isi' => 3, 'card' => 165, 'ch' => 70, 'label' => '16,5 x 21,5 cm'],
        ];
        $kertas = 'voucher';
        $k = $kertasList['voucher'];
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500&family=Marcellus&family=Space+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        :root { --merah: #b32a42; --merah-tua: #8f1d31; --krem: #f6eee0; --teks: #5e4a4a; }
        body { margin: 0; padding: 8mm; background: #eef1f6; color: #273142; font-family: 'Jost', Arial, sans-serif; }
        .toolbar { margin-bottom: 8px; text-align: center; font-family: Arial, sans-serif; }
        .toolbar a, .toolbar button { display: inline-block; border: 0; border-radius: 4px; padding: 8px 14px; color: #fff; background: #2676d9; font-weight: 700; cursor: pointer; text-decoration: none; }
        /* Semua voucher rata tengah (depan & belakang). */
        .sheet { width: {{ $k['w'] }}mm; height: {{ $k['h'] }}mm; margin: 0 auto 6mm; overflow: hidden; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.15); display: flex; flex-direction: column; align-items: center; justify-content: space-evenly; }
        /* Sisi belakang: hanya gambar desain yang dicerminkan (mirror), tulisan tetap normal. */
        .sheet.back-sheet .desain-img { transform: scaleX(-1); }

        .voucher-card { position: relative; overflow: hidden; width: {{ $k['card'] }}mm; height: {{ $k['ch'] }}mm; flex: 0 0 {{ $k['ch'] }}mm; }
        /* Bingkai garis ganda seperti desain. */
        .voucher-card::before, .voucher-card::after { content: ''; position: absolute; pointer-events: none; z-index: 2; }
        .voucher-card::before { inset: 2.6mm; border: .25mm solid currentColor; }
        .voucher-card::after { inset: 3.7mm; border: .25mm solid currentColor; }

        /* ===== Depan ===== */
        .front-card { background: var(--krem); color: var(--merah); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 6mm 10mm 5mm; }
        /* Logo bunga di atas tulisan, dipotong dari logo-voucher.webp (2000x2000 px, latar transparan; area isi x 225-1760, y 625-1374). */
        .logo-voucher { flex: 0 0 auto; width: 36mm; height: 17.57mm; background-image: url('{{ asset('image/logo-voucher.webp') }}'); background-repeat: no-repeat; background-size: 46.91mm auto; background-position: -5.28mm -14.66mm; }
        .voucher-card.has-desain.desain-gelap .logo-voucher { filter: drop-shadow(0 0 .4mm #fff) drop-shadow(0 0 .4mm #fff); }
        .label-voucher { margin-top: 3.2mm; font-size: 2.3mm; letter-spacing: 1.5mm; padding-left: 1.5mm; color: var(--merah-tua); }
        .nominal { margin-top: 1mm; font-family: 'Marcellus', Georgia, serif; font-size: 14.5mm; line-height: 1; color: var(--merah); white-space: nowrap; }
        .sub { margin-top: 1.6mm; font-size: 2.7mm; color: var(--teks); }
        .no-berlaku { margin-top: 3mm; font-family: 'Space Mono', 'Courier New', monospace; font-size: 2.15mm; letter-spacing: .25mm; color: var(--merah-tua); }

        /* ===== Belakang ===== */
        .back-card { background: var(--merah); color: var(--krem); padding: 8.5mm 9.5mm 0; }
        .back-body { display: flex; gap: 6mm; }
        .back-kiri { flex: 1; min-width: 0; }
        .back-title { font-family: 'Marcellus', Georgia, serif; font-size: 5.2mm; line-height: 1.1; color: var(--krem); }
        .terms { margin: 2.2mm 0 0; padding-left: 4.4mm; font-size: 2.6mm; line-height: 1.55; color: var(--krem); max-height: 27mm; overflow: hidden; }
        .back-kanan { flex: 0 0 20mm; text-align: center; }
        .qr-box { width: 20mm; height: 20mm; background: var(--krem); padding: 1.3mm; }
        .qr-box svg { width: 100%; height: 100%; display: block; }
        .qr-kode { margin-top: 1.3mm; font-family: 'Space Mono', 'Courier New', monospace; font-size: 2.4mm; color: var(--krem); word-break: break-all; }
        .back-footer { position: absolute; left: 9.5mm; right: 9.5mm; bottom: 7.2mm; padding-top: 2.6mm; border-top: .2mm solid rgba(246,238,224,.75); display: flex; justify-content: space-between; gap: 4mm; font-size: 2.6mm; color: var(--krem); }
        .back-footer span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ===== Desain upload: gambar jadi latar penuh, tulisan tetap dicetak di atasnya ===== */
        .desain-img { position: absolute; inset: 0; z-index: 0; width: 100%; height: 100%; object-fit: cover; }
        .voucher-card.has-desain { background: transparent; }
        .voucher-card.has-desain::before, .voucher-card.has-desain::after { display: none; }
        .voucher-card.has-desain > :not(.desain-img):not(.back-footer) { position: relative; z-index: 1; }
        .voucher-card.has-desain .back-footer { z-index: 1; border-top-color: currentColor; }
        /* Warna tulisan menyesuaikan terang/gelapnya desain (diatur script di bawah). */
        .voucher-card.has-desain .t { color: #111827; text-shadow: 0 0 .6mm #fff, 0 0 .6mm #fff, 0 0 1.2mm rgba(255,255,255,.9); }
        .voucher-card.has-desain.desain-gelap .t { color: #fff; text-shadow: 0 0 .6mm #000, 0 0 .6mm #000, 0 0 1.2mm rgba(0,0,0,.85); }

        @media print {
            @page { size: {{ $k['w'] }}mm {{ $k['h'] }}mm; margin: 0; }
            body { padding: 0; background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0 auto; box-shadow: none; page-break-after: always; break-after: page; }
            .sheet:last-child { page-break-after: auto; break-after: auto; }
            .voucher-card, .voucher-card * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        // QR kode voucher (SVG dari server, bisa di-scan di Cek Voucher / form penjualan).
        $qrSvg = function ($kode) {
            $svg = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->margin(0)->color(42, 10, 18)->backgroundColor(246, 238, 224)->generate($kode);
            return preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);
        };
        $bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalIndo = function ($tanggal) use ($bulanIndo) {
            return $tanggal->day . ' ' . $bulanIndo[$tanggal->month] . ' ' . $tanggal->year;
        };
        // Syarat & ketentuan dari isian voucher, jadi daftar bernomor (penomoran / bullet di teks dibuang).
        // Bila kosong, hanya kalimat umum (tidak mengarang aturan sendiri).
        $syaratDefault = ['Syarat dan ketentuan mengikuti kebijakan Optik Melati.'];
        $syaratList = function ($card) use ($syaratDefault) {
            $baris = collect(preg_split('/\r\n|\r|\n/', (string) $card->syarat_ketentuan))
                ->map(function ($b) { return trim(preg_replace('/^\s*(?:[-*•·]+|\d+[.)])\s*/u', '', $b)); })
                ->filter()
                ->values()
                ->all();
            return $baris ?: $syaratDefault;
        };
        // Footer belakang: alamat & kontak cabang (Instagram dari OPTIK_INSTAGRAM di .env).
        $branch = $branch ?? null;
        $alamatToko = optional($branch)->address ? preg_replace('/^[A-Z0-9]{2,}\+[A-Z0-9]{2,},\s*/', '', $branch->address) : 'Optik Melati';
        $kontakToko = collect([
            optional($branch)->phone ? 'WA ' . $branch->phone : null,
            config('app.instagram') ? '@' . ltrim(config('app.instagram'), '@') : null,
        ])->filter()->implode(' · ');
        $side = $side ?? 'front';
        $isBack = $side === 'back';
        // Desain dipakai bersama satu batch: encode sekali per file.
        $desainCache = [];
        $desainOf = function ($card, string $sisi) use (&$desainCache) {
            $path = $sisi === 'belakang' ? $card->desain_belakang : $card->desain_depan;
            if (!$path) {
                return null;
            }
            if (!array_key_exists($path, $desainCache)) {
                $desainCache[$path] = $card->desainDataUri($sisi);
            }
            return $desainCache[$path];
        };
    @endphp

    <div class="toolbar">
        <strong style="display:block; margin-bottom:6px;">{{ $isBack ? 'Preview Print Belakang' : 'Preview Print Depan' }} (kertas {{ $k['label'] }})</strong>
        <small style="display:block; margin-bottom:6px;">{{ $vouchers->count() }} voucher, {{ $k['isi'] }} voucher ({{ str_replace(',0', '', number_format($k['card'] / 10, 1, ',', '')) }} x {{ str_replace(',0', '', number_format($k['ch'] / 10, 1, ',', '')) }} cm) per lembar kertas {{ $k['label'] }}. Print depan dulu, balik kertas pada sisi panjang, lalu print belakang. Saat print pilih Margin: None dan Scale 100%.</small>
        <div style="margin-bottom:6px;">
            Kertas:
            @foreach ($kertasList as $kode => $info)
                <a href="{{ route('voucher.print', ['voucher' => $voucher, 'semua' => $semua ? 1 : 0, 'side' => $side, 'kertas' => $kode]) }}" style="background:{{ $kertas === $kode ? '#198754' : '#6b7280' }};">{{ $info['label'] }} ({{ $info['isi'] }} voucher)</a>
            @endforeach
        </div>
        <a href="{{ route('voucher.print', ['voucher' => $voucher, 'semua' => $semua ? 1 : 0, 'side' => 'front', 'kertas' => $kertas]) }}">Preview Depan</a>
        <a href="{{ route('voucher.print', ['voucher' => $voucher, 'semua' => $semua ? 1 : 0, 'side' => 'back', 'kertas' => $kertas]) }}" style="background:#1559a6;">Preview Belakang</a>
        <button onclick="window.print()" style="margin-left:5px; background:#198754;">Print Halaman Ini</button>
        <button onclick="window.close()" style="background:#6b7280; margin-left:5px;">Tutup</button>
    </div>

    @foreach ($vouchers->chunk($k['isi']) as $sheetVouchers)
    <div class="sheet{{ $isBack ? ' back-sheet' : '' }}">
    @foreach ($sheetVouchers as $card)
        @php $desain = $desainOf($card, $isBack ? 'belakang' : 'depan'); @endphp
        @if (!$isBack)
            <div class="voucher-card front-card{{ $desain ? ' has-desain' : '' }}">
                @if ($desain)
                    <img class="desain-img" src="{{ $desain }}" alt="Desain voucher">
                @endif
                <div class="logo-voucher" role="img" aria-label="Optik Melati"></div>
                <div class="label-voucher t">{{ ($card->jenis_nominal ?? 'uang') === 'diskon' ? 'VOUCER DISKON' : 'VOUCER BELANJA' }}</div>
                <div class="nominal t">
                    @if (($card->jenis_nominal ?? 'uang') === 'diskon')
                        Diskon {{ number_format((float) $card->nominal, 0, ',', '.') }}%
                    @else
                        Rp{{ number_format((float) $card->nominal, 0, ',', '.') }}
                    @endif
                </div>
                <div class="sub t">Potongan untuk pembelian kacamata</div>
                <div class="no-berlaku t">
                    No. {{ $card->kode }}@if ($card->berlaku_sampai) &middot; Berlaku s.d. {{ $tanggalIndo($card->berlaku_sampai) }}@endif
                </div>
            </div>
        @else
            <div class="voucher-card back-card{{ $desain ? ' has-desain' : '' }}">
                @if ($desain)
                    <img class="desain-img" src="{{ $desain }}" alt="Desain voucher">
                @endif
                <div class="back-body">
                    <div class="back-kiri">
                        <div class="back-title t">Syarat &amp; Ketentuan</div>
                        <ol class="terms t">
                            @foreach ($syaratList($card) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    </div>
                    <div class="back-kanan">
                        <div class="qr-box">{!! $qrSvg($card->kode) !!}</div>
                        <div class="qr-kode t">{{ $card->kode }}</div>
                    </div>
                </div>
                <div class="back-footer t">
                    <span>{{ $alamatToko }}</span>
                    <span>{{ $kontakToko }}</span>
                </div>
            </div>
        @endif
    @endforeach
    </div>
    @endforeach
    <script>
        // Hitung rata-rata kecerahan tiap gambar desain, lalu pilih warna tulisan yang kontras.
        (function () {
            const cache = {};
            function kecerahan(img) {
                if (cache[img.src] !== undefined) {
                    return cache[img.src];
                }
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = 40;
                    canvas.height = 20;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
                    let total = 0;
                    for (let i = 0; i < data.length; i += 4) {
                        total += 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                    }
                    cache[img.src] = total / (data.length / 4);
                } catch (e) {
                    cache[img.src] = 255;
                }
                return cache[img.src];
            }
            function terapkan(img) {
                img.closest('.voucher-card').classList.toggle('desain-gelap', kecerahan(img) < 140);
            }
            document.querySelectorAll('.desain-img').forEach(function (img) {
                if (img.complete && img.naturalWidth) {
                    terapkan(img);
                } else {
                    img.addEventListener('load', function () { terapkan(img); });
                }
            });
        })();
    </script>
</body>
</html>
