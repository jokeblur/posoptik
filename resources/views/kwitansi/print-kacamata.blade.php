<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Kacamata</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 10px; background: #eef1f6; color: #555b68; font-family: Arial, Helvetica, sans-serif; }
        .toolbar { text-align: center; margin-bottom: 10px; }
        .btn { border: 0; border-radius: 6px; padding: 8px 14px; color: #fff; font-weight: 700; cursor: pointer; }
        .btn-print { background: #2f7cf3; }
        .btn-close { background: #6a717a; margin-left: 8px; }
        .kwitansi { width: 210mm; min-height: 105mm; margin: 0 auto; padding: 5mm 7mm; position: relative; background: #fff; border: .45mm solid #89909d; box-shadow: 0 5px 14px rgba(0,0,0,.12); }
        .kwitansi::before { content: ''; position: absolute; inset: 2mm; pointer-events: none; border: .3mm solid #aeb5c0; background: repeating-linear-gradient(0deg, transparent 0, transparent 1.5mm, rgba(125,135,150,.08) 1.6mm, transparent 1.8mm); }
        .inner { position: relative; z-index: 1; border: .25mm solid #b6bdc7; min-height: 94mm; padding: 4mm 6mm 3mm; }
        .header { text-align: left; padding-bottom: 2.5mm; border-bottom: .25mm dotted #929aa7; }
        .company-top { display: flex; justify-content: flex-start; align-items: center; gap: 2mm; }
        .logo { width: 17mm; height: 17mm; object-fit: contain; }
        .company-name { font-family: Georgia, serif; font-size: 7mm; font-weight: 700; letter-spacing: .6px; color: #687080; }
        .company-sub { margin-top: 1mm; font-family: Georgia, serif; font-size: 2.7mm; color: #737b89; }
        .company-phone { margin-top: 1.5mm; font-size: 2.7mm; color: #687080; }
        .meta { position: absolute; top: 5mm; right: 6mm; display: grid; grid-template-columns: 10mm 29mm; gap: 1mm; font-size: 3.1mm; }
        .meta-value { min-height: 4mm; border-bottom: .25mm dotted #737b86; }
        .content { display: grid; gap: 1.5mm; padding-top: 3mm; font-size: 3.3mm; }
        .line { display: grid; grid-template-columns: 39mm 1fr; align-items: end; gap: 2mm; }
        .line-value { min-height: 5mm; padding: 0 1mm .6mm; border-bottom: .25mm dotted #737b86; }
        .items { width: 100%; margin-top: 1mm; border-collapse: collapse; font-size: 3.3mm; }
        .items td { height: 6mm; padding: 1mm; border-bottom: .25mm dotted #737b86; }
        .items .number { width: 38mm; text-align: right; white-space: nowrap; }
        .total { display: flex; justify-content: flex-end; align-items: center; gap: 3mm; margin-top: 2mm; font-size: 3.6mm; font-weight: 700; }
        .total-value { min-width: 42mm; padding: 1.2mm 2mm; text-align: right; border-bottom: .25mm dotted #737b86; }
        .bottom-section { display: grid; grid-template-columns: 1fr auto; align-items: end; gap: 8mm; margin-top: 3mm; }
        .terbilang-bottom { align-self: end; }
        .terbilang-bottom .label { margin-bottom: 1mm; }
        .terbilang-bottom .line-value { min-height: 12mm; }
        .footer { display: flex; justify-content: flex-end; margin-top: 2mm; }
        .ttd-block { width: 46mm; text-align: center; font-size: 3.1mm; }
        .ttd-date { margin-bottom: 9mm; }
        .ttd-name { padding-top: 1mm; border-top: .25mm solid #737b86; }
        @media print {
            @page { size: A4 portrait; margin: 0; }
            body { padding: 0; background: #fff; }
            .toolbar { display: none; }
            .kwitansi { margin: 0; box-shadow: none; }
            .kwitansi, .inner, .items, .total-value, .logo { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $no = (string) ($data['nomor'] ?? '');
        $penerima = (string) ($data['penerima_dari'] ?? '');
        $untukPembayaran = (string) ($data['untuk_pembayaran'] ?? '');
        $namaPembuat = trim((string) ($data['nama_pembuat'] ?? ''));
        $tempatTanggal = trim((string) ($data['tempat_tanggal'] ?? '')) ?: 'Teluk Kuantan, ' . date('d-m-Y');
        $jumlahView = number_format($jumlah, 0, ',', '.') . ',-';
    @endphp

    <div class="toolbar">
        <button class="btn btn-print" onclick="window.print()">Print Kwitansi</button>
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
    </div>

    <div class="kwitansi">
        <div class="inner">
            <div class="header">
                <div class="company-top">
                    <img class="logo" src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" onerror="this.style.display='none';">
                    <div class="company-name">OPTIK MELATI</div>
                </div>
                <div class="company-sub">Jl. Perintis Kemerdekaan, Ruko Wisma Riau Serambi Tiga Teluk Kuantan</div>
                <div class="company-phone">0813 6654 5800 / 0812 6761 7701</div>
                <div class="company-sub">KWITANSI PEMBAYARAN KACAMATA</div>
            </div>

            <div class="meta">
                <strong>No.</strong><div class="meta-value">{{ $no }}</div>
            </div>

            <div class="content">
                <div class="line">
                    <strong>Telah terima dari</strong>
                    <div class="line-value">{{ $penerima }}</div>
                </div>
                <div class="line">
                    <strong>Untuk pembayaran</strong>
                    <div class="line-value">{{ $untukPembayaran }}</div>
                </div>

                <table class="items">
                    <tbody>
                        <tr>
                            <td>Frame / Binkai</td>
                            <td class="number">Rp {{ number_format($hargaFrame, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Lensa</td>
                            <td class="number">Rp {{ number_format($hargaLensa, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bottom-section">
                <div class="terbilang-bottom">
                    <div class="label"><strong>Uang sejumlah</strong></div>
                    <div class="line-value">{{ $data['terbilang'] ?? '' }}</div>
                </div>
                <div>
                    <div class="total"><span>Jumlah Rp</span><span class="total-value">{{ $jumlahView }}</span></div>
                    <div class="footer">
                        <div class="ttd-block">
                            <div class="ttd-date">{{ $tempatTanggal }}</div>
                            <div class="ttd-name">{{ $namaPembuat !== '' ? $namaPembuat : '(................................)' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
