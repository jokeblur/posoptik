<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Cetak Transaksi - {{ $penjualan->kode_penjualan }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <style>
        @page {
            size: A5;
            margin: 10mm;
        }
        
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 0;
            background: white;
            font-size: 10px;
            line-height: 1.2;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 5px;
            display: block;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .branch-name {
            font-size: 12px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .address {
            font-size: 9px;
            margin: 5px 0;
        }
        
        .transaction-info {
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 80px;
        }
        
        .info-value {
            text-align: right;
        }
        
        .qrcode-section {
            text-align: center;
            margin: 15px 0;
            padding: 10px 0;
            border: 1px solid #000;
        }
        
        .qrcode {
            margin: 10px auto;
            display: block;
        }
        
        .qrcode-label {
            font-size: 8px;
            margin: 5px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            font-size: 8px;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .total-section {
            margin-top: 15px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .total-value {
            text-align: right;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }
        
        .status-lunas { background-color: #28a745; }
        .status-belum { background-color: #ffc107; color: #000; }
        .status-waiting { background-color: #ffc107; color: #000; }
        .status-processing { background-color: #17a2b8; }
        .status-completed { background-color: #28a745; }
        .status-taken { background-color: #6f42c1; }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        /* Responsive untuk layar kecil */
        @media screen and (max-width: 600px) {
            body { font-size: 12px; }
            .barcode { font-size: 18px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Transaksi</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>

    <!-- Header -->
    <div class="header">
        <img src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" class="logo">
        <div class="company-name">OPTIK MELATI</div>
        <div class="branch-name">{{ $penjualan->branch->name ?? 'Cabang Utama' }}</div>
        <div class="address">
            {{ $penjualan->branch->alamat ?? 'Jl. Contoh No. 123, Kota, Provinsi' }}<br>
            Telp: {{ $penjualan->branch->telepon ?? '021-1234567' }}
        </div>
    </div>

    <!-- Informasi Transaksi -->
    <div class="transaction-info">
        <div class="info-row">
            <span class="info-label">No. Transaksi:</span>
            <span class="info-value">{{ $penjualan->kode_penjualan }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($penjualan->tanggal)->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kasir:</span>
            <span class="info-value">{{ $penjualan->user->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pasien:</span>
            <span class="info-value">{{ $penjualan->nama_pasien ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Dokter:</span>
            <span class="info-value">{{ $penjualan->dokter->nama_dokter ?? $penjualan->dokter_manual ?? 'N/A' }}</span>
        </div>
        @if($penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']))
        <div class="info-row">
            <span class="info-label">Jenis Layanan:</span>
            <span class="info-value">
                <span class="status-badge status-lunas">{{ strtoupper($penjualan->pasien->service_type) }}</span>
            </span>
        </div>
        @if($penjualan->pasien->no_bpjs)
        <div class="info-row">
            <span class="info-label">No. BPJS:</span>
            <span class="info-value">{{ $penjualan->pasien->no_bpjs }}</span>
        </div>
        @endif
        @endif
        <div class="info-row">
            <span class="info-label">Status Bayar:</span>
            <span class="info-value">
                <span class="status-badge {{ $penjualan->status == 'Lunas' ? 'status-lunas' : 'status-belum' }}">
                    {{ $penjualan->status }}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Status Kerja:</span>
            <span class="info-value">
                @php
                    $statusClass = [
                        'Menunggu Pengerjaan' => 'status-waiting',
                        'Sedang Dikerjakan' => 'status-processing',
                        'Selesai Dikerjakan' => 'status-completed',
                        'Sudah Diambil' => 'status-taken'
                    ];
                @endphp
                <span class="status-badge {{ $statusClass[$penjualan->status_pengerjaan] ?? 'status-waiting' }}">
                    {{ $penjualan->status_pengerjaan }}
                </span>
            </span>
        </div>
        @if($penjualan->tanggal_siap)
        <div class="info-row">
            <span class="info-label">Siap:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($penjualan->tanggal_siap)->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>

    <!-- QR Code Section -->
    @if($penjualan->barcode)
    <div class="qrcode-section">
        <div class="qrcode-label">SCAN QR CODE UNTUK UPDATE STATUS</div>
        <div class="qrcode">
            {!! QrCode::size(150)->generate(url('/barcode/scan/' . $penjualan->barcode)) !!}
        </div>
        <div class="qrcode-label">{{ $penjualan->barcode }}</div>
    </div>
    @endif

    <!-- Detail Produk -->
    @php
        $isBPJS = $penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']);
    @endphp
    
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: {{ $isBPJS ? '65%' : '45%' }};">Produk</th>
                <th style="width: 10%;">Qty</th>
                @if(!$isBPJS)
                <th style="width: 20%;">Harga</th>
                @endif
                <th style="width: {{ $isBPJS ? '20%' : '20%' }};">{{ $isBPJS ? 'Biaya BPJS' : 'Subtotal' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->details as $index => $detail)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>
                    @if($detail->itemable_type === 'App\\Models\\Frame')
                        {{ $detail->itemable->merk_frame ?? 'Frame' }}
                        @if($detail->itemable && $detail->itemable->jenis_frame)
                            <br><small>({{ $detail->itemable->jenis_frame }})</small>
                        @endif
                    @elseif($detail->itemable_type === 'App\\Models\\Lensa')
                        {{ $detail->itemable->merk_lensa ?? 'Lensa' }}
                    @elseif($detail->itemable_type === 'App\\Models\\Aksesoris')
                        {{ $detail->itemable->nama_aksesoris ?? 'Aksesoris' }}
                    @else
                        Produk tidak ditemukan
                    @endif
                </td>
                <td style="text-align: center;">{{ $detail->quantity }}</td>
                @if(!$isBPJS)
                <td style="text-align: right;">{{ number_format($detail->price, 0, ',', '.') }}</td>
                @endif
                <td style="text-align: right;">
                    @if($isBPJS)
                        {{ number_format($penjualan->bpjs_default_price, 0, ',', '.') }}
                    @else
                        {{ number_format($detail->subtotal, 0, ',', '.') }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
        @if($isBPJS)
            <div class="total-row">
                <span class="total-label">Biaya BPJS:</span>
                <span class="total-value">Rp {{ number_format($penjualan->bpjs_default_price, 0, ',', '.') }}</span>
            </div>
            @if($penjualan->details->sum('additional_cost') > 0)
            <div class="total-row">
                <span class="total-label">Biaya Tambahan:</span>
                <span class="total-value">Rp {{ number_format($penjualan->details->sum('additional_cost'), 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="total-row" style="font-size: 12px; font-weight: bold; border-top: 1px solid #000; padding-top: 5px;">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Dibayar:</span>
                <span class="total-value">Rp {{ number_format($penjualan->bayar, 0, ',', '.') }}</span>
            </div>
            @if($penjualan->kekurangan > 0)
            <div class="total-row">
                <span class="total-label">Kekurangan:</span>
                <span class="total-value">Rp {{ number_format($penjualan->kekurangan, 0, ',', '.') }}</span>
            </div>
            @endif
        @else
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">Rp {{ number_format($penjualan->details->sum('subtotal'), 0, ',', '.') }}</span>
            </div>
            @if($penjualan->details->sum('additional_cost') > 0)
            <div class="total-row">
                <span class="total-label">Biaya Tambahan:</span>
                <span class="total-value">Rp {{ number_format($penjualan->details->sum('additional_cost'), 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="total-row">
                <span class="total-label">Diskon:</span>
                <span class="total-value">Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</span>
            </div>
            <div class="total-row" style="font-size: 12px; font-weight: bold; border-top: 1px solid #000; padding-top: 5px;">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Dibayar:</span>
                <span class="total-value">Rp {{ number_format($penjualan->bayar, 0, ',', '.') }}</span>
            </div>
            @if($penjualan->kekurangan > 0)
            <div class="total-row">
                <span class="total-label">Kekurangan:</span>
                <span class="total-value">Rp {{ number_format($penjualan->kekurangan, 0, ',', '.') }}</span>
            </div>
            @endif
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="margin-bottom: 10px;">
            <strong>Terima Kasih Telah Mempercayai Optik Melati</strong>
        </div>
        <div>
            Barang yang sudah dibeli tidak dapat dikembalikan<br>
            Garansi sesuai ketentuan yang berlaku
        </div>
        <div style="margin-top: 10px;">
            Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>
</html> 