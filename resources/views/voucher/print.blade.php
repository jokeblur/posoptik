<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher {{ $voucher->kode }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 8mm; background: #eef1f6; color: #273142; font-family: Arial, Helvetica, sans-serif; }
        .toolbar { margin-bottom: 8px; text-align: center; }
        .toolbar a, .toolbar button { display: inline-block; border: 0; border-radius: 4px; padding: 8px 14px; color: #fff; background: #2676d9; font-weight: 700; cursor: pointer; text-decoration: none; }
        .print-page { width: 196mm; height: 280mm; margin: 0 auto; page-break-after: always; }
        .sheet { display: grid; grid-template-columns: repeat(2, 95.5mm); grid-template-rows: repeat(5, 52mm); gap: 3mm; width: 194mm; height: 272mm; direction: ltr; }
        .front-page .sheet { margin-left: 0; margin-right: auto; }
        .back-page .sheet { margin-left: auto; margin-right: 0; }
        /* Sisi belakang dicerminkan kiri-kanan untuk balik kertas pada sisi panjang. */
        .back-sheet { direction: rtl; }
        .voucher-card { position: relative; overflow: hidden; padding: 4mm 5mm; border: .45mm dashed #68758a; border-radius: 2mm; background: #fff; direction: ltr; }
        .voucher-card::after { content: ''; position: absolute; right: -10mm; bottom: -12mm; width: 45mm; height: 45mm; border: 5mm solid rgba(38, 118, 217, .08); border-radius: 50%; }
        .card-header { display: flex; align-items: center; justify-content: space-between; gap: 4mm; padding-bottom: 2mm; border-bottom: .3mm solid #d7e0ed; }
        .brand-wrap { display: flex; align-items: center; gap: 2mm; }
        .logo { width: 10mm; height: 10mm; object-fit: contain; }
        .brand { color: #1559a6; font-family: Georgia, serif; font-size: 4.5mm; font-weight: 700; }
        .voucher-label { color: #6b7686; font-size: 2.8mm; font-weight: 700; letter-spacing: .4px; }
        .code { margin-top: 2mm; color: #17243a; font-size: 5mm; font-weight: 700; letter-spacing: 1px; }
        .nominal { margin-top: 1mm; color: #1559a6; font-size: 6mm; font-weight: 700; }
        .validity { margin-top: 1mm; color: #586577; font-size: 2.8mm; }
        .back-card { display: flex; flex-direction: column; justify-content: center; text-align: center; }
        .back-card .back-title { color: #1559a6; font-family: Georgia, serif; font-size: 4.2mm; font-weight: 700; }
        .back-card .back-code { margin-top: 1mm; color: #586577; font-size: 2.8mm; }
        .terms { position: relative; z-index: 1; margin-top: 3mm; padding: 2mm 3mm; border: .25mm dotted #9ba8b8; color: #4c5869; font-size: 2.7mm; line-height: 1.25; text-align: left; white-space: pre-line; }
        .terms strong { display: block; margin-bottom: .7mm; color: #273142; font-size: 2.8mm; }
        @media print {
            @page { size: A4 portrait; margin: 0; }
            body { padding: 7mm; background: #fff; }
            .toolbar { display: none; }
            .print-page { width: 196mm; height: 280mm; margin: 0; }
            .sheet { width: 194mm; height: 272mm; }
            .voucher-card { break-inside: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $pages = (int) ceil($copies / 10);
        $terms = $voucher->syarat_ketentuan ?: 'Syarat dan ketentuan mengikuti kebijakan Optik Melati.';
        $side = $side ?? 'front';
        $isBack = $side === 'back';
    @endphp

    <div class="toolbar">
        <strong style="display:block; margin-bottom:6px;">{{ $isBack ? 'Preview Print Belakang' : 'Preview Print Depan' }}</strong>
        <small style="display:block; margin-bottom:6px;">Print depan dulu, balik kertas kiri-kanan pada sisi panjang, lalu print belakang dengan jumlah yang sama.</small>
        <a href="{{ route('voucher.print', ['voucher' => $voucher, 'copies' => $copies, 'side' => 'front']) }}">Preview Depan</a>
        <a href="{{ route('voucher.print', ['voucher' => $voucher, 'copies' => $copies, 'side' => 'back']) }}" style="background:#1559a6;">Preview Belakang</a>
        <button onclick="window.print()" style="margin-left:5px; background:#198754;">Print Halaman Ini</button>
        <button onclick="window.close()" style="background:#6b7280; margin-left:5px;">Tutup</button>
    </div>

    @for ($page = 0; $page < $pages; $page++)
        @if (!$isBack)
            <div class="print-page front-page">
                <div class="sheet">
                    @for ($slot = 0; $slot < 10 && (($page * 10) + $slot) < $copies; $slot++)
                        <div class="voucher-card">
                            <div class="card-header">
                                <div class="brand-wrap">
                                    <img class="logo" src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" onerror="this.style.display='none';">
                                    <div class="brand">OPTIK MELATI</div>
                                </div>
                                <div class="voucher-label">VOUCHER PROMO</div>
                            </div>
                            <div class="code">{{ $voucher->kode }}</div>
                            <div class="nominal">Rp {{ number_format((float) $voucher->nominal, 0, ',', '.') }}</div>
                            <div class="validity">
                                Masa berlaku:
                                {{ $voucher->berlaku_mulai ? $voucher->berlaku_mulai->format('d/m/Y') : 'sekarang' }}
                                s/d
                                {{ $voucher->berlaku_sampai ? $voucher->berlaku_sampai->format('d/m/Y') : 'selamanya' }}
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @else
            <div class="print-page back-page">
                <div class="sheet back-sheet">
                    @for ($slot = 0; $slot < 10 && (($page * 10) + $slot) < $copies; $slot++)
                        <div class="voucher-card back-card">
                            <div class="back-title">SYARAT DAN KETENTUAN</div>
                            <div class="back-code">Voucher {{ $voucher->kode }}</div>
                            <div class="terms">{{ $terms }}</div>
                            <div class="back-code">OPTIK MELATI</div>
                        </div>
                    @endfor
                </div>
            </div>
        @endif
    @endfor
</body>
</html>
