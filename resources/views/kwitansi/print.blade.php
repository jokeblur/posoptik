<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> </title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2430;
            padding: 10px;
        }

        .toolbar {
            text-align: center;
            margin-bottom: 10px;
        }

        .btn {
            border: 0;
            border-radius: 6px;
            padding: 8px 14px;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-print {
            background: #2f7cf3;
        }

        .btn-close {
            background: #6a717a;
            margin-left: 8px;
        }

        .kwitansi {
            width: 210mm;
            height: 80mm;
            margin: 0 auto;
            background: #ffffff;
            border: 0.35mm solid #202020;
            display: grid;
            grid-template-columns: 66mm 1fr;
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        .stub-left {
            border-right: 0.35mm dashed #5a5a5a;
            padding: 2.2mm 2.8mm 2.3mm;
            background: #fcfcfc;
            display: grid;
            grid-template-rows: auto auto auto 1fr auto;
            gap: 1.2mm;
        }

        .stub-brand {
            font-size: 3.2mm;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: #003c8f;
            border-bottom: 0.3mm solid #d0d7e5;
            padding-bottom: 1mm;
        }

        .stub-title {
            font-size: 4mm;
            font-weight: 700;
            line-height: 1.05;
            margin-top: 0.6mm;
            color: #1f2430;
        }

        .stub-row .label,
        .stub-amount .label,
        .label {
            font-size: 3.1mm;
            line-height: 1.25;
            margin-bottom: 0.8mm;
            color: #2f3542;
        }

        .stub-row .value,
        .stub-amount .value,
        .value-line {
            border-bottom: 0.25mm dotted #2f3542;
            min-height: 5.6mm;
            padding: 0 1mm 0.6mm;
            display: flex;
            align-items: flex-end;
            font-size: 3.55mm;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stub-row .value.multi,
        .value-line.multi {
            min-height: 6.3mm;
            white-space: normal;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            text-overflow: clip;
        }

        .stub-amount .value {
            justify-content: flex-start;
            font-weight: 700;
            background: #f7eef4;
        }

        .main-right {
            padding: 2.2mm 3.2mm 2.4mm;
            display: grid;
            grid-template-rows: auto 1fr auto;
            gap: 1.3mm;
            background:
                linear-gradient(135deg, rgba(5, 52, 115, 0.035), rgba(5, 52, 115, 0) 34mm),
                #ffffff;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr 72mm;
            align-items: start;
            column-gap: 3mm;
            padding-bottom: 1.2mm;
            border-bottom: 0.25mm solid #d7dce6;
        }

        .company {
            display: grid;
            gap: 0.9mm;
        }

        .company-top {
            display: flex;
            align-items: center;
            gap: 2mm;
        }

        .logo {
            width: 9.5mm;
            height: 9.5mm;
            object-fit: contain;
            flex: 0 0 auto;
            border: 0.2mm solid #ccd5e6;
            border-radius: 1.4mm;
            padding: 0.8mm;
            background: #fff;
        }

        .company-name {
            font-size: 4.8mm;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #003c8f;
        }

        .company-sub {
            font-size: 2.8mm;
            color: #4a5568;
            letter-spacing: 0.2px;
        }

        .meta {
            display: grid;
            grid-template-columns: 26mm 1fr;
            column-gap: 1.2mm;
            row-gap: 0.9mm;
            align-content: start;
        }

        .meta-label {
            font-size: 3.1mm;
            color: #2f3542;
            font-weight: 600;
        }

        .meta-value {
            font-size: 3.5mm;
            border-bottom: 0.25mm dotted #2f3542;
            min-height: 4.9mm;
            padding-bottom: 0.6mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .content {
            display: grid;
            gap: 1.3mm;
            align-content: start;
        }

        .row {
            display: grid;
            grid-template-columns: 34mm 1fr;
            column-gap: 2mm;
            align-items: end;
        }

        .row .label {
            margin-bottom: 0;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2.6mm;
            align-items: end;
            padding-top: 1mm;
            border-top: 0.25mm solid #d7dce6;
        }

        .row-amount .value-line {
            justify-content: flex-start;
            font-weight: 700;
            font-size: 3.7mm;
            background: #f7eef4;
        }

        .footer .row-amount {
            grid-template-columns: auto auto;
            justify-content: start;
            column-gap: 1.4mm;
            align-items: end;
            align-self: end;
        }

        .footer .row-amount .value-line {
            width: max-content;
            min-height: auto;
            padding: 0 0.8mm 0.4mm;
            justify-self: start;
        }

        .ttd-block {
            justify-self: end;
            text-align: center;
            width: 42mm;
            align-self: end;
        }

        .ttd-date {
            font-size: 3.1mm;
            margin-bottom: 8.5mm;
            color: #2f3542;
        }

        .ttd-name-line {
            border-top: 0.25mm solid #2f3542;
            padding-top: 0.8mm;
            font-size: 3mm;
            color: #2f3542;
        }

        .row-terbilang .value-line {
            background: #f7eef4;
        }

        .terbilang-box .value-line {
            background: #f7eef4;
        }

        .jumlah-box .label {
            text-align: right;
            margin-bottom: 0.7mm;
        }

        .jumlah-box .value-line {
            justify-content: flex-end;
            font-weight: 700;
            font-size: 3.3mm;
        }

        .muted {
            color: #4e5a6d;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .kwitansi {
                box-shadow: none;
                margin: 0;
            }

            .kwitansi,
            .value-line,
            .stub-row .value,
            .stub-amount .value,
            .logo {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @php
        $no = (string) ($data['nomor'] ?? '');
        $penerima = (string) ($data['penerima_dari'] ?? '');
        $untuk1 = (string) ($data['untuk_pembayaran'] ?? '');
        $namaPembuat = trim((string) ($data['nama_pembuat'] ?? ''));
        $terbilang = (string) ($data['terbilang'] ?? '');
        $jumlahView = $jumlah !== null ? number_format($jumlah, 0, ',', '.') . ',-' : '';
        $ttdTempatTanggal = trim((string) ($data['tempat_tanggal'] ?? ''));
        if ($ttdTempatTanggal === '') {
            $ttdTempatTanggal = 'Teluk Kuantan, ' . date('d-m-Y');
        }
    @endphp

    <div class="toolbar">
        <button class="btn btn-print" onclick="window.print()">Print Kwitansi</button>
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
    </div>

    <div class="kwitansi">
        <div class="stub-left">
            <div class="stub-brand">OPTIK MELATI</div>

            <div class="stub-row">
                <div class="label">No.</div>
                <div class="value">{{ $no }}</div>
            </div>

            <div class="stub-row">
                <div class="label">Penerima</div>
                <div class="value multi">{{ $penerima }}</div>
            </div>

            <div class="stub-amount">
                <div class="label">Uang Sejumlah Rp</div>
                <div class="value">{{ $jumlahView }}</div>
            </div>
        </div>

        <div class="main-right">
            <div class="header">
                <div class="company">
                    <div class="company-top">
                        <img class="logo" src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" onerror="this.style.display='none';">
                        <div class="company-name">OPTIK MELATI</div>
                    </div>
                </div>
                <div class="meta">
                    <div class="meta-label">No.</div>
                    <div class="meta-value">{{ $no }}</div>
                </div>
            </div>

            <div class="content">
                <div class="row">
                    <div class="label">Telah terima dari</div>
                    <div class="value-line multi">{{ $penerima }}</div>
                </div>

                <div class="row row-terbilang">
                    <div class="label">Uang Sejumlah</div>
                    <div class="value-line multi">{{ $terbilang }}</div>
                </div>
                <div class="row">
                    <div class="label">Untuk pembayaran</div>
                    <div class="value-line multi">{{ $untuk1 }}</div>
                </div>

                
            </div>

            <div class="footer">
                <div class="row row-amount">
                    <div class="label">Rp</div>
                    <div class="value-line">{{ $jumlahView }}</div>
                </div>

                <div class="ttd-block">
                    <div class="ttd-date">{{ $ttdTempatTanggal }}</div>
                    <div class="ttd-name-line">{{ $namaPembuat !== '' ? $namaPembuat : '(................................)' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
