<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Status Refraksi - {{ $pasien->nama_pasien }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 100mm 160mm; /* Ukuran nota 10 x 16 cm, sama seperti nota half page penjualan */
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 1mm 3mm 3mm;
            background: white;
            font-size: 12px;
            line-height: 1.3;
            position: relative;
            min-height: 160mm;
            width: 100mm;
        }

        /* Background logo dengan efek watermark, sama seperti nota half page */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset("image/optik-melati.png") }}');
            background-size: 80mm 80mm;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.05;
            z-index: -1;
            pointer-events: none;
        }

        .print-container {
            width: 100mm;
            min-height: 160mm;
            margin: 0;
            position: relative;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            padding-top: 0;
            margin-top: 0;
            margin-bottom: 10px;
            line-height: 0;
        }

        .logo {
            width: 60px;
            height: 60px;
            display: block;
            margin: 0 auto 4px;
            object-fit: contain;
        }

        .header-info {
            text-align: center;
            line-height: 1.3;
        }

        .company-name {
            font-size: 15px;
            font-weight: 600;
            margin: 2px 0;
        }

        .address {
            font-size: 9.5px;
            margin: 2px 0;
        }

        .title {
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 6px 0 10px;
        }

        .pasien-info {
            background: #f8f9fa;
            padding: 10px 10px;
            margin: 8px 0;
            border-left: 3px solid #007bff;
            border-radius: 4px;
        }

        .info-row {
            display: flex;
            margin: 8px 0;
            font-size: 12.5px;
        }

        .info-label {
            font-weight: 600;
            min-width: 92px;
            flex-shrink: 0;
        }

        .info-value {
            flex: 1;
            border-bottom: 1px dotted #999;
            min-height: 15px;
            word-break: break-word;
        }

        .section-title {
            font-weight: 600;
            text-align: center;
            margin: 10px 0 6px;
            font-size: 11px;
            background: rgba(164, 25, 61, 0.85);
            padding: 5px;
            border-radius: 3px;
            color: #fff;
            text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        }

        .resep-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 4px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
        }

        .resep-table th,
        .resep-table td {
            padding: 8px 4px;
            border: 1px solid #6c757d;
            text-align: center;
            font-weight: 500;
        }

        .resep-table th {
            background: rgba(108, 117, 125, 0.8);
            font-weight: 600;
            color: #fff;
            text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        }

        .resep-table .eye-label {
            font-weight: 700;
            background: rgba(73, 80, 87, 0.85);
            color: #fff;
            text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        }

        .footer-signature {
            margin-top: 30px;
            text-align: right;
            font-size: 12px;
            padding-right: 6px;
        }

        .print-button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        @media print {
            html, body {
                margin: 0 !important;
                padding: 1mm 3mm 3mm !important;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print,
            .no-print * {
                display: none !important;
                visibility: hidden !important;
            }
            body::before {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="print-button-container no-print" style="text-align: left; margin-bottom: 20px; display: flex; gap: 10px; justify-content: flex-start;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Cetak Sekarang</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" class="logo">
            <div class="header-info">
               
                <div class="address">
                    Jalan H. Halim Komp. Waterpark Pelangi, Teluk Kuantan<br>
                    No.Hp : 0812 6761 7701
                </div>
            </div>
        </div>

        <div class="title">Kartu Status Refraksi</div>

        <!-- Data Pasien -->
        <div class="pasien-info">
            <div class="info-row">
                <span class="info-label">Nama Pasien</span>
                <span class="info-value">{{ $pasien->nama_pasien }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Umur</span>
                <span class="info-value">{{ $pasien->umur }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat</span>
                <span class="info-value">{{ $pasien->alamat }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Hp</span>
                <span class="info-value">{{ $pasien->nohp }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Anamnesa</span>
                <span class="info-value">{{ $pasien->anamnesa }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tgl Periksa</span>
                <span class="info-value">{{ optional($pasien->tanggal_periksa)->format('d/m/Y') ?? $pasien->tanggal_periksa }}</span>
            </div>
        </div>

        <!-- Resep Lensa -->
        <div class="section-title">Resep Lensa Pasien</div>
        <table class="resep-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Sph</th>
                    <th>Cyl</th>
                    <th>Axis</th>
                    <th>Add</th>
                    <th>PD</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="eye-label">R</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td class="eye-label">L</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <div class="footer-signature">
            Refraksionis Optisien
        </div>
    </div>
</body>
</html>
