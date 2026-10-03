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
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 8mm; background: #eef1f6; color: #273142; font-family: Arial, Helvetica, sans-serif; }
        .toolbar { margin-bottom: 8px; text-align: center; }
        .toolbar a, .toolbar button { display: inline-block; border: 0; border-radius: 4px; padding: 8px 14px; color: #fff; background: #2676d9; font-weight: 700; cursor: pointer; text-decoration: none; }
        /* Semua voucher rata tengah (depan & belakang). */
        .sheet { width: {{ $k['w'] }}mm; height: {{ $k['h'] }}mm; margin: 0 auto 6mm; overflow: hidden; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.15); display: flex; flex-direction: column; align-items: center; justify-content: space-evenly; }
        /* Sisi belakang: hanya gambar desain yang dicerminkan (mirror), tulisan tetap normal. */
        .sheet.back-sheet .desain-img { transform: scaleX(-1); }
        .voucher-card { position: relative; overflow: hidden; width: {{ $k['card'] }}mm; height: {{ $k['ch'] }}mm; flex: 0 0 {{ $k['ch'] }}mm; padding: {{ $k['ch'] < 70 ? '4mm' : '6mm' }} 9mm; border: .3mm dashed #68758a; background: #fff; }
        .voucher-card::after { content: ''; position: absolute; right: -14mm; bottom: -18mm; width: 62mm; height: 62mm; border: 7mm solid rgba(38, 118, 217, .08); border-radius: 50%; }
        .card-header { display: flex; align-items: center; justify-content: space-between; gap: 4mm; padding-bottom: 3mm; border-bottom: .3mm solid #d7e0ed; }
        .brand-wrap { display: flex; align-items: center; gap: 3mm; }
        .logo { width: 13mm; height: 13mm; object-fit: contain; }
        .brand { color: #1559a6; font-family: Georgia, serif; font-size: 6.5mm; font-weight: 700; }
        .voucher-label { color: #6b7686; font-size: 3.6mm; font-weight: 700; letter-spacing: .5px; }
        .code { position: relative; z-index: 1; margin-top: 4mm; color: #17243a; font-size: 6.5mm; font-weight: 700; letter-spacing: 1px; }
        .nominal { position: relative; z-index: 1; margin-top: 1.5mm; color: #1559a6; font-size: 10mm; font-weight: 700; }
        .validity { position: relative; z-index: 1; margin-top: 2mm; color: #586577; font-size: 3.6mm; }
        .back-card { display: flex; flex-direction: column; justify-content: center; text-align: center; }
        .back-card .back-title { color: #1559a6; font-family: Georgia, serif; font-size: 5.5mm; font-weight: 700; }
        .back-card .back-code { margin-top: 1mm; color: #586577; font-size: 3.4mm; }
        .terms { position: relative; z-index: 1; margin-top: 3mm; padding: 2.5mm 4mm; border: .25mm dotted #9ba8b8; color: #4c5869; font-size: 3.3mm; line-height: 1.3; text-align: left; white-space: pre-line; max-height: 38mm; overflow: hidden; }
        /* Voucher dengan desain upload: gambar jadi latar penuh, semua tulisan tetap dicetak di atasnya. */
        .voucher-card.has-desain::after { display: none; }
        .desain-img { position: absolute; inset: 0; z-index: 0; width: 100%; height: 100%; object-fit: cover; }
        .voucher-card.has-desain > :not(.desain-img) { position: relative; z-index: 1; }
        /* Warna tulisan di atas desain menyesuaikan terang/gelapnya gambar (diatur script di bawah).
           Default & desain terang: tulisan gelap dengan bayangan putih; desain gelap: tulisan putih dengan bayangan hitam. */
        .voucher-card.has-desain .brand, .voucher-card.has-desain .voucher-label, .voucher-card.has-desain .code,
        .voucher-card.has-desain .nominal, .voucher-card.has-desain .validity, .voucher-card.has-desain .back-title,
        .voucher-card.has-desain .back-code, .voucher-card.has-desain .terms {
            color: #111827;
            text-shadow: 0 0 .6mm #fff, 0 0 .6mm #fff, 0 0 1.2mm rgba(255,255,255,.9);
        }
        .voucher-card.has-desain .nominal { color: #0b3f7a; }
        .voucher-card.has-desain .card-header { border-bottom-color: rgba(17,24,39,.35); }
        .voucher-card.has-desain .terms { border-color: rgba(17,24,39,.45); }
        .voucher-card.has-desain.desain-gelap .brand, .voucher-card.has-desain.desain-gelap .voucher-label, .voucher-card.has-desain.desain-gelap .code,
        .voucher-card.has-desain.desain-gelap .nominal, .voucher-card.has-desain.desain-gelap .validity, .voucher-card.has-desain.desain-gelap .back-title,
        .voucher-card.has-desain.desain-gelap .back-code, .voucher-card.has-desain.desain-gelap .terms {
            color: #fff;
            text-shadow: 0 0 .6mm #000, 0 0 .6mm #000, 0 0 1.2mm rgba(0,0,0,.85);
        }
        .voucher-card.has-desain.desain-gelap .nominal { color: #ffe27a; }
        .voucher-card.has-desain.desain-gelap .card-header { border-bottom-color: rgba(255,255,255,.5); }
        .voucher-card.has-desain.desain-gelap .terms { border-color: rgba(255,255,255,.6); }
        @media print {
            @page { size: {{ $k['w'] }}mm {{ $k['h'] }}mm; margin: 0; }
            body { padding: 0; background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0 auto; box-shadow: none; page-break-after: always; break-after: page; }
            .sheet:last-child { page-break-after: auto; break-after: auto; }
            .voucher-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $terms = 'Syarat dan ketentuan mengikuti kebijakan Optik Melati.';
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
            <div class="voucher-card{{ $desain ? ' has-desain' : '' }}">
                @if ($desain)
                    <img class="desain-img" src="{{ $desain }}" alt="Desain voucher">
                @endif
                <div class="card-header">
                    <div class="brand-wrap">
                        <img class="logo" src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" onerror="this.style.display='none';">
                        <div class="brand">OPTIK MELATI</div>
                    </div>
                    <div class="voucher-label">VOUCHER PROMO</div>
                </div>
                <div class="code">{{ $card->kode }}</div>
                <div class="nominal">
                    @if (($card->jenis_nominal ?? 'uang') === 'diskon')
                        {{ number_format((float) $card->nominal, 0, ',', '.') }}% OFF
                    @else
                        Rp {{ number_format((float) $card->nominal, 0, ',', '.') }}
                    @endif
                </div>
                <div class="validity">
                    Masa berlaku:
                    {{ $card->berlaku_mulai ? $card->berlaku_mulai->format('d/m/Y') : 'sekarang' }}
                    s/d
                    {{ $card->berlaku_sampai ? $card->berlaku_sampai->format('d/m/Y') : 'selamanya' }}
                </div>
            </div>
        @else
            <div class="voucher-card back-card{{ $desain ? ' has-desain' : '' }}">
                @if ($desain)
                    <img class="desain-img" src="{{ $desain }}" alt="Desain voucher">
                @endif
                <div class="back-title">SYARAT DAN KETENTUAN</div>
                <div class="back-code">Voucher {{ $card->kode }}</div>
                <div class="terms">{{ $card->syarat_ketentuan ?: $terms }}</div>
                <div class="back-code">OPTIK MELATI</div>
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
