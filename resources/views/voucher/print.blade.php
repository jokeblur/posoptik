<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher {{ $semua ? $voucher->batch_kode : $voucher->kode }}</title>
    <style>
        /* Kertas 14,8 x 21,4 cm isi 3 voucher ukuran 15 x 7 cm. Voucher 2 mm lebih lebar dari kertas,
           jadi dibuat di tengah dan 1 mm di sisi kiri & kanan keluar kertas (isi voucher tetap aman). */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 8mm; background: #eef1f6; color: #273142; font-family: Arial, Helvetica, sans-serif; }
        .toolbar { margin-bottom: 8px; text-align: center; }
        .toolbar a, .toolbar button { display: inline-block; border: 0; border-radius: 4px; padding: 8px 14px; color: #fff; background: #2676d9; font-weight: 700; cursor: pointer; text-decoration: none; }
        .sheet { width: 148mm; height: 214mm; margin: 0 auto 6mm; overflow: hidden; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.15); display: flex; flex-direction: column; align-items: center; justify-content: space-evenly; }
        .voucher-card { position: relative; overflow: hidden; width: 150mm; height: 70mm; flex: 0 0 70mm; padding: 6mm 9mm; border: .3mm dashed #68758a; background: #fff; }
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
        /* Voucher dengan desain upload: gambar jadi latar penuh, kode unik dicetak di atasnya. */
        .voucher-card.has-desain { padding: 0; border-style: solid; border-color: transparent; }
        .voucher-card.has-desain::after { display: none; }
        .desain-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .desain-kode { position: absolute; right: 4mm; bottom: 3mm; z-index: 1; padding: 1mm 3mm; border-radius: 1.5mm; background: rgba(255,255,255,.9); color: #17243a; font-size: 4mm; font-weight: 700; letter-spacing: .8px; }
        @media print {
            @page { size: 148mm 214mm; margin: 0; }
            body { padding: 0; background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; page-break-after: always; break-after: page; }
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
        <strong style="display:block; margin-bottom:6px;">{{ $isBack ? 'Preview Print Belakang' : 'Preview Print Depan' }} (kertas 14,8 x 21,4 cm)</strong>
        <small style="display:block; margin-bottom:6px;">{{ $vouchers->count() }} voucher, 3 voucher (15 x 7 cm) per lembar kertas 14,8 x 21,4 cm. Print depan dulu, balik kertas pada sisi panjang, lalu print belakang. Saat print pilih Margin: None dan Scale 100%.</small>
        <a href="{{ route('voucher.print', ['voucher' => $voucher, 'semua' => $semua ? 1 : 0, 'side' => 'front']) }}">Preview Depan</a>
        <a href="{{ route('voucher.print', ['voucher' => $voucher, 'semua' => $semua ? 1 : 0, 'side' => 'back']) }}" style="background:#1559a6;">Preview Belakang</a>
        <button onclick="window.print()" style="margin-left:5px; background:#198754;">Print Halaman Ini</button>
        <button onclick="window.close()" style="background:#6b7280; margin-left:5px;">Tutup</button>
    </div>

    @foreach ($vouchers->chunk(3) as $sheetVouchers)
    <div class="sheet">
    @foreach ($sheetVouchers as $card)
        @php $desain = $desainOf($card, $isBack ? 'belakang' : 'depan'); @endphp
        @if ($desain)
            <div class="voucher-card has-desain">
                <img class="desain-img" src="{{ $desain }}" alt="Desain voucher">
                @unless ($isBack)
                    <div class="desain-kode">{{ $card->kode }}</div>
                @endunless
            </div>
        @elseif (!$isBack)
            <div class="voucher-card">
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
            <div class="voucher-card back-card">
                <div class="back-title">SYARAT DAN KETENTUAN</div>
                <div class="back-code">Voucher {{ $card->kode }}</div>
                <div class="terms">{{ $card->syarat_ketentuan ?: $terms }}</div>
                <div class="back-code">OPTIK MELATI</div>
            </div>
        @endif
    @endforeach
    </div>
    @endforeach
</body>
</html>
