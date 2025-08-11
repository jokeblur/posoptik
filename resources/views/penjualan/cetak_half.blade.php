<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Cetak Transaksi - {{ $penjualan->kode_penjualan }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <style>
        @page {
            size: 100mm 150mm; /* 10cm x 15cm */
            margin: 5mm;
        }
        
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 0;
            background: white;
            font-size: 9px;
            line-height: 1.1;
            position: relative;
            min-height: 150mm;
            width: 100mm;
        }
        
        /* Background logo with watermark effect */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset("image/optik-melati.png") }}');
            background-size: 80mm 80mm; /* Logo size */
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.1;
            z-index: -1;
            pointer-events: none;
        }
        
        .header {
            display: flex;
            align-items: flex-start;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
            gap: 10px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
            margin-top: 5px;
        }
        
        .header-info {
            flex: 1;
            text-align: left;
        }
        
        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin: 3px 0;
        }
        
        .branch-name {
            font-size: 10px;
            font-weight: bold;
            margin: 3px 0;
        }
        
        .address {
            font-size: 8px;
            margin: 3px 0;
        }
        
        .transaction-info {
            margin-bottom: 12px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 70px;
        }
        
        .info-value {
            text-align: right;
        }
        
        .pasien-info {
            background: #f8f9fa;
            padding: 5px;
            margin: 8px 0;
            border-left: 3px solid #007bff;
            border-radius: 3px;
        }
        
        .resep-info {
            background: #fff3cd;
            padding: 5px;
            margin: 8px 0;
            border-left: 3px solid #ffc107;
            border-radius: 3px;
        }
        
        .resep-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 8px;
        }
        
        .resep-table th, .resep-table td {
            padding: 1px 2px;
            border: 1px solid #ddd;
            text-align: center;
        }
        
        .resep-table th {
            background: #e9ecef;
            font-weight: bold;
        }
        
        .resep-table .eye-label {
            font-weight: bold;
            background: #dee2e6;
        }
        
        .section-title {
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
            font-size: 9px;
            background: #e9ecef;
            padding: 2px;
            border-radius: 2px;
        }

        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: left;
            font-size: 7px;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .total-section {
            margin-top: 12px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .total-value {
            text-align: right;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
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
            body { 
                margin: 0; 
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none; }
            
            /* Ensure background logo prints */
            body::before {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Responsive untuk layar kecil */
        @media screen and (max-width: 600px) {
            body { 
                font-size: 10px; 
                width: 100%;
                min-height: auto;
            }
            .barcode { font-size: 16px; }
        }
        
        /* Container untuk memastikan ukuran tetap */
        .print-container {
            width: 100mm;
            min-height: 150mm;
            margin: 0 auto;
            position: relative;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left {
            text-align: left;
            flex-grow: 1;
        }

        .footer-right {
            text-align: right;
            flex-shrink: 0;
        }

        .qrcode-small {
            text-align: center;
            margin-top: 8px;
        }

        .qrcode-label-small {
            font-size: 7px;
            margin-bottom: 3px;
        }

        .qrcode-image {
            margin: 0 auto 3px;
            display: block;
        }

        .qrcode-barcode {
            font-size: 7px;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Transaksi</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" class="logo">
            <div class="header-info">
                <div class="company-name">OPTIK MELATI</div>
                <div class="branch-name">{{ $penjualan->branch->name ?? 'Cabang Utama' }}</div>
                <div class="address">
                    @php
                        $cleanAddress = preg_replace('/^[A-Z]{2}\d+\+[A-Z]{2}\d+,\s*/', '', $penjualan->branch->address ?? '');
                    @endphp
                    {{ $cleanAddress }}<br>
                    Telp: {{ $penjualan->branch->phone ?? '' }}
                </div>
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

        <!-- Informasi Pasien -->
        @if($penjualan->pasien)
        <div class="pasien-info">
            <div class="section-title">INFORMASI PASIEN</div>
            <div class="info-row">
                <span class="info-label">Nama:</span>
                <span class="info-value">{{ $penjualan->pasien->nama_pasien ?? 'N/A' }}</span>
            </div>
            @if($penjualan->pasien->alamat)
            <div class="info-row">
                <span class="info-label">Alamat:</span>
                <span class="info-value">{{ $penjualan->pasien->alamat }}</span>
            </div>
            @endif
            @if($penjualan->pasien->kontak)
            <div class="info-row">
                <span class="info-label">Kontak:</span>
                <span class="info-value">{{ $penjualan->pasien->kontak }}</span>
            </div>
            @endif
        </div>
        @endif

        <!-- Informasi Resep -->
        @if($penjualan->pasien && ($penjualan->pasien->resep_od_sph || $penjualan->pasien->resep_os_sph))
        <div class="resep-info">
            <div class="section-title">RESEP LENSA</div>
            <table class="resep-table">
                <thead>
                    <tr>
                        <th class="eye-label">Mata</th>
                        <th>SPH</th>
                        <th>CYL</th>
                        <th>AXIS</th>
                        <th>ADD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="eye-label">OD</td>
                        <td>{{ $penjualan->pasien->resep_od_sph ?? '-' }}</td>
                        <td>{{ $penjualan->pasien->resep_od_cyl ?? '-' }}</td>
                        <td>{{ $penjualan->pasien->resep_od_axis ?? '-' }}</td>
                        <td rowspan="2">{{ $penjualan->pasien->resep_add ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="eye-label">OS</td>
                        <td>{{ $penjualan->pasien->resep_os_sph ?? '-' }}</td>
                        <td>{{ $penjualan->pasien->resep_os_cyl ?? '-' }}</td>
                        <td>{{ $penjualan->pasien->resep_os_axis ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
            @if($penjualan->pasien->resep_pd)
            <div style="text-align: center; margin-top: 3px; font-size: 8px;">
                <strong>PD: {{ $penjualan->pasien->resep_pd }}mm</strong>
            </div>
            @endif
            @if($penjualan->pasien->resep_dokter)
            <div style="text-align: center; margin-top: 3px; font-size: 8px;">
                <em>Dokter: {{ $penjualan->pasien->resep_dokter }}</em>
            </div>
            @endif
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
                    <th style="width: {{ $isBPJS ? '75%' : '45%' }};">Produk</th>
                    <th style="width: {{ $isBPJS ? '25%' : '10%' }};">Qty</th>
                    @if(!$isBPJS)
                    <th style="width: 20%;">Harga</th>
                    <th style="width: 20%;">Subtotal</th>
                    @endif
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
                    <td style="text-align: right;">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Section - Hanya untuk non-BPJS -->
        @if(!$isBPJS)
        <div class="total-section">
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
            <div class="total-row" style="font-size: 10px; font-weight: bold; border-top: 1px solid #000; padding-top: 3px;">
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
        </div>
        @else
        <!-- Pesan khusus untuk BPJS -->
        <div style="text-align: center; margin: 12px 0; font-style: italic; padding: 8px; border: 1px dashed #000;">
            <strong>Layanan ditanggung oleh {{ strtoupper($penjualan->pasien->service_type) }}</strong>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <div style="margin-bottom: 8px;">
                        <strong>Terima Kasih Telah Mempercayai Optik Melati</strong>
                    </div>
                    <div>
                        Barang yang sudah dibeli tidak dapat dikembalikan<br>
                        Garansi sesuai ketentuan yang berlaku
                    </div>
                    <div style="margin-top: 8px;">
                        Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                    </div>
                </div>
                @if($penjualan->barcode)
                <div class="footer-right">
                    <div class="qrcode-small">
                        <div class="qrcode-label-small">SCAN QR CODE</div>
                        <div class="qrcode-image">
                            {!! QrCode::size(60)->generate(url('/barcode/scan/' . $penjualan->barcode)) !!}
                        </div>
                        <div class="qrcode-barcode">{{ $penjualan->barcode }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html> 