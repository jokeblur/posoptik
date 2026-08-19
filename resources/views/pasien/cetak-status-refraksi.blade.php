<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Status Refraksi - {{ $pasien->nama_pasien }}</title>
    <style>
        @media print {
            @page {
                margin: 0;
                size: 100mm 160mm; /* Ukuran nota 10 x 16 cm */
            }
            body {
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            width: 100mm;
            height: 160mm;
            margin: 0 auto;
            padding: 6mm 7mm;
            background: white;
        }

        .container {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 4mm;
        }

        .logo {
            width: 16mm;
            height: 16mm;
            display: block;
            margin: 0 auto 2mm;
            object-fit: contain;
        }

        .company-name {
            margin: 0;
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1px;
            color: #a4193d;
        }

        .company-address {
            margin: 1mm 0 0;
            font-size: 7.5pt;
            font-style: italic;
            color: #555;
        }

        hr.divider {
            border: 0;
            border-top: 1.5px solid #a4193d;
            margin: 3mm 0 4mm;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 6mm;
        }

        .form-row {
            display: flex;
            margin: 3mm 0;
            align-items: flex-end;
        }

        .form-label {
            width: 30mm;
            flex-shrink: 0;
            font-size: 10pt;
        }

        .form-colon {
            width: 3mm;
            flex-shrink: 0;
        }

        .form-value {
            flex: 1;
            border-bottom: 1px dotted #666;
            min-height: 13px;
            padding-bottom: 1px;
            word-break: break-word;
            font-size: 10pt;
        }

        .resep-badge {
            text-align: center;
            background: #a4193d;
            color: #fff;
            font-weight: bold;
            font-size: 10pt;
            padding: 2mm 0;
            border-radius: 4px;
            margin: 6mm 0 3mm;
        }

        .resep-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 6mm;
        }

        .resep-table th,
        .resep-table td {
            border: 1px solid #333;
            padding: 3mm 1mm;
            text-align: center;
        }

        .resep-table th {
            background: #e9e2e5;
            font-weight: bold;
        }

        .resep-table td:first-child {
            background: #f5f2f3;
            font-weight: bold;
        }

        .footer-signature {
            margin-top: 14mm;
            text-align: right;
            font-size: 10pt;
            padding-right: 4mm;
        }

        .print-button {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #a4193d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Cetak</button>

    <div class="container">
        <div class="header">
            <img src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" class="logo">
            <p class="company-name">OPTIK MELATI</p>
            <p class="company-address">Jalan H. Hakim, Teluk Kuantan</p>
            <p class="company-address">No.Hp : 0812 5678 703</p>
        </div>

        <hr class="divider">

        <div class="title">Kartu Status Refraksi</div>

        <div class="form-row">
            <span class="form-label">Nama Pasien</span>
            <span class="form-colon">:</span>
            <span class="form-value">{{ $pasien->nama_pasien }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">Umur</span>
            <span class="form-colon">:</span>
            <span class="form-value">{{ $pasien->umur }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">Alamat</span>
            <span class="form-colon">:</span>
            <span class="form-value">{{ $pasien->alamat }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">No. Hp</span>
            <span class="form-colon">:</span>
            <span class="form-value">{{ $pasien->nohp }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">Anamnesa</span>
            <span class="form-colon">:</span>
            <span class="form-value">{{ $pasien->anamnesa }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">Tanggal Periksa</span>
            <span class="form-colon">:</span>
            <span class="form-value">{{ optional($pasien->tanggal_periksa)->format('d/m/Y') ?? $pasien->tanggal_periksa }}</span>
        </div>

        <div class="resep-badge">Resep Lensa Pasien</div>

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
                    <td>R</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>L</td>
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
