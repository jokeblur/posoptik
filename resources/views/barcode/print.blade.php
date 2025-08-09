<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Print Barcode - {{ $transaksi->kode_penjualan }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .qrcode-container {
            text-align: center;
            margin: 20px 0;
        }
        .qrcode {
            margin: 10px auto;
            display: block;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table th,
        .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .info-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-waiting { background-color: #f39c12; color: white; }
        .status-processing { background-color: #3498db; color: white; }
        .status-completed { background-color: #27ae60; color: white; }
        .status-taken { background-color: #9b59b6; color: white; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Barcode</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>

    <div class="qrcode-container">
        <h2>QR Code Transaksi</h2>
        <div class="qrcode">
            {!! QrCode::size(200)->generate(url('/barcode/scan/' . $transaksi->barcode)) !!}
        </div>
        <p><strong>{{ $transaksi->kode_penjualan }}</strong></p>
        <p><small>{{ $transaksi->barcode }}</small></p>
        <p><small>Scan QR Code untuk update status pengerjaan</small></p>
    </div>

    <table class="info-table">
        <tr>
            <th>Informasi Transaksi</th>
            <th>Detail</th>
        </tr>
        <tr>
            <td>Kode Transaksi</td>
            <td>{{ $transaksi->kode_penjualan }}</td>
        </tr>
        <tr>
            <td>Barcode</td>
            <td><strong>{{ $transaksi->barcode }}</strong></td>
        </tr>
        <tr>
            <td>Tanggal Transaksi</td>
            <td>{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Pasien</td>
            <td>{{ $transaksi->nama_pasien ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>{{ $transaksi->user ? $transaksi->user->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td>Cabang</td>
            <td>{{ $transaksi->branch ? $transaksi->branch->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td>Total</td>
            <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Status Pembayaran</td>
            <td>
                <span class="status {{ $transaksi->status == 'Lunas' ? 'status-completed' : 'status-waiting' }}">
                    {{ $transaksi->status }}
                </span>
            </td>
        </tr>
        <tr>
            <td>Status Pengerjaan</td>
            <td>
                @php
                    $statusClass = [
                        'Menunggu Pengerjaan' => 'status-waiting',
                        'Sedang Dikerjakan' => 'status-processing',
                        'Selesai Dikerjakan' => 'status-completed',
                        'Sudah Diambil' => 'status-taken'
                    ];
                @endphp
                <span class="status {{ $statusClass[$transaksi->status_pengerjaan] ?? 'status-waiting' }}">
                    {{ $transaksi->status_pengerjaan }}
                </span>
            </td>
        </tr>
        <tr>
            <td>Tanggal Siap</td>
            <td>{{ $transaksi->tanggal_siap ? \Carbon\Carbon::parse($transaksi->tanggal_siap)->format('d/m/Y') : 'Belum ditentukan' }}</td>
        </tr>
    </table>

    <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
        <p>Scan barcode ini untuk melihat detail transaksi dan update status</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html> 