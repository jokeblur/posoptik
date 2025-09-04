<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Cetak Transaksi - {{ $penjualan->kode_penjualan }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 100mm 150mm; /* 10cm x 15cm */
            margin: 3mm;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            font-size: 10px;
            line-height: 1.2;
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
            opacity: 0.05;
            z-index: -1;
            pointer-events: none;
        }
        
        .header {
            display: flex;
            align-items: flex-start;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
            gap: 8px;
        }
        
        .logo {
            width: 45px;
            height: 45px;
            flex-shrink: 0;
            margin-top: 3px;
        }
        
        .header-info {
            flex: 1;
            text-align: left;
        }
        
        .company-name {
            font-size: 12px;
            font-weight: 600;
            margin: 2px 0;
        }
        
        .branch-name {
            font-size: 10px;
            font-weight: 500;
            margin: 2px 0;
        }
        
        .address {
            font-size: 8px;
            margin: 2px 0;
        }
        
        .transaction-info {
            margin-bottom: 8px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }
        
        .info-label {
            font-weight: 600;
            min-width: 70px;
        }
        
        .info-value {
            text-align: right;
        }
        
        .pasien-info {
            background: #f8f9fa;
            padding: 5px;
            margin: 6px 0;
            border-left: 3px solid #007bff;
            border-radius: 4px;
        }
        
        .resep-info {
            background: #fff3cd;
            padding: 5px;
            margin: 6px 0;
            border-left: 3px solid #ffc107;
            border-radius: 4px;
        }
        
        .pengerjaan-info {
            background: rgba(220, 53, 69, 0.1);
            padding: 5px;
            margin: 6px 0;
            border-left: 3px solid rgba(220, 53, 69, 0.6);
            border-radius: 4px;
        }
        
        .resep-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 9px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-radius: 4px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .resep-table th, .resep-table td {
            padding: 3px 4px;
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
        
        .resep-table tbody tr:nth-child(even) {
            background-color: rgba(248, 249, 250, 0.6);
        }
        
        .resep-table tbody tr:nth-child(odd) {
            background-color: rgba(255, 255, 255, 0.4);
        }
        
        .resep-table tbody tr:hover {
            background-color: rgba(255, 193, 7, 0.2);
        }
        
        .section-title {
            font-weight: 600;
            text-align: center;
            margin: 5px 0;
            font-size: 9px;
            background: rgba(108, 117, 125, 0.7);
            padding: 3px;
            border-radius: 3px;
            color: #fff;
            text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        }

        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: left;
            font-size: 8px;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: 600;
            text-align: center;
        }
        
        .total-section {
            margin-top: 8px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        
        .total-label {
            font-weight: 600;
        }
        
        .total-value {
            text-align: right;
        }
        
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 600;
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
                font-size: 11px; 
                width: 100%;
                min-height: auto;
            }
            .barcode { font-size: 14px; }
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
            margin-top: 5px;
        }

        .qrcode-label-small {
            font-size: 7px;
            margin-bottom: 2px;
            font-weight: 600;
        }

        .qrcode-image {
            margin: 0 auto 2px;
            display: block;
        }

        .qrcode-barcode {
            font-size: 7px;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="print-button-container no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Cetak Sekarang</button>
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
            <!-- <div class="info-row">
                <span class="info-label">📅 Tanggal Transaksi:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($penjualan->tanggal)->format('d/m/Y H:i') }}</span>
            </div> -->
            <div class="info-row">
                <span class="info-label">📅 Tanggal Hari Ini:</span>
                <span class="info-value" style="font-weight: 600; color: #17a2b8;">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir:</span>
                <span class="info-value">{{ $penjualan->user->name ?? 'N/A' }}</span>
            </div>
            <!-- <div class="info-row">
                <span class="info-label">Pasien:</span>
                <span class="info-value">{{ $penjualan->nama_pasien ?? 'N/A' }}</span>
            </div> -->
            <!-- <div class="info-row">
                <span class="info-label">Dokter:</span>
                <span class="info-value">{{ $penjualan->dokter->nama_dokter ?? $penjualan->dokter_manual ?? 'N/A' }}</span>
            </div> -->
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
            <!-- <div class="info-row">
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
            </div> -->
            @if($penjualan->tanggal_siap)
            <div class="info-row">
                <span class="info-label">📅 Siap:</span>
                <span class="info-value" style="font-weight: 600; color: #28a745;">
                    {{ \Carbon\Carbon::parse($penjualan->tanggal_siap)->format('d/m/Y H:i') }}
                </span>
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

        <!-- Informasi Pengerjaan -->
        <!-- <div class="pengerjaan-info">
            <div class="section-title">⚙️ INFORMASI PENGERJAAN</div>
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
            <div class="info-row" style="background: rgba(40, 167, 69, 0.1); padding: 3px; border-radius: 3px; border: 1px solid rgba(40, 167, 69, 0.3);">
                <span class="info-label">📅 Tanggal Siap:</span>
                <span class="info-value" style="font-weight: 700; color: #28a745; font-size: 11px;">
                    {{ \Carbon\Carbon::parse($penjualan->tanggal_siap)->format('d/m/Y H:i') }}
                </span>
            </div>
            @php
                $tanggal_transaksi = \Carbon\Carbon::parse($penjualan->tanggal);
                $tanggal_siap = \Carbon\Carbon::parse($penjualan->tanggal_siap);
                $durasi_pengerjaan = $tanggal_transaksi->diffInHours($tanggal_siap);
                $durasi_hari = $tanggal_transaksi->diffInDays($tanggal_siap);
            @endphp
            <div class="info-row" style="background: rgba(255, 193, 7, 0.1); padding: 3px; border-radius: 3px; border: 1px solid rgba(255, 193, 7, 0.3); margin-top: 3px;">
                <span class="info-label">⏱️ Durasi Pengerjaan:</span>
                <span class="info-value" style="font-weight: 600; color: #856404; font-size: 10px;">
                    @if($durasi_hari > 0)
                        {{ $durasi_hari }} hari {{ $durasi_pengerjaan % 24 }} jam
                    @else
                        {{ $durasi_pengerjaan }} jam
                    @endif
                </span>
            </div>
            @endif
            @if($penjualan->tanggal_siap)
            @php
                $sekarang = \Carbon\Carbon::now();
                $status_waktu = $sekarang->gt($tanggal_siap) ? 'Terlambat' : 'Tepat Waktu';
                $warna_status = $sekarang->gt($tanggal_siap) ? '#dc3545' : '#28a745';
            @endphp
            <div class="info-row" style="background: rgba(108, 117, 125, 0.1); padding: 3px; border-radius: 3px; border: 1px solid rgba(108, 117, 125, 0.3); margin-top: 3px;">
                <span class="info-label">🕐 Status Waktu:</span>
                <span class="info-value" style="font-weight: 600; color: {{ $warna_status }}; font-size: 10px;">
                    {{ $status_waktu }}
                </span>
            </div>
            @endif
            @if($penjualan->tanggal_siap)
            <div class="info-row" style="background: rgba(108, 117, 125, 0.1); padding: 3px; border-radius: 3px; border: 1px solid rgba(108, 117, 125, 0.3); margin-top: 3px;">
                <span class="info-label">🕐 Status Waktu:</span>
                <span class="info-value" style="font-weight: 600; color: {{ \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($penjualan->tanggal_siap)) ? '#dc3545' : '#28a745' }}; font-size: 10px;">
                    {{ \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($penjualan->tanggal_siap)) ? 'Terlambat' : 'Tepat Waktu' }}
                </span>
            </div>
            @endif
            @if($penjualan->waktu_sudah_diambil)
            <div class="info-row">
                <span class="info-label">📤 Diambil:</span>
                <span class="info-value" style="font-weight: 600; color: #6f42c1;">
                    {{ \Carbon\Carbon::parse($penjualan->waktu_sudah_diambil)->format('d/m/Y H:i') }}
                </span>
            </div>
            @endif
        </div> -->

        <!-- Informasi Resep -->
        @if($penjualan->pasien)
        <div class="resep-info">
            <div class="section-title">📋 RESEP LENSA PASIEN</div>
            
            @php
                // Coba ambil data resep dari prescriptions jika ada
                $latestPrescription = $penjualan->pasien->prescriptions()->latest('tanggal')->first();
                
                // Jika tidak ada prescription, gunakan data manual dari penjualan
                $od_sph = $latestPrescription->od_sph ?? $penjualan->resep_od_sph ?? '-';
                $od_cyl = $latestPrescription->od_cyl ?? $penjualan->resep_od_cyl ?? '-';
                $od_axis = $latestPrescription->od_axis ?? $penjualan->resep_od_axis ?? '-';
                $os_sph = $latestPrescription->os_sph ?? $penjualan->resep_os_sph ?? '-';
                $os_cyl = $latestPrescription->os_cyl ?? $penjualan->resep_os_cyl ?? '-';
                $os_axis = $latestPrescription->os_axis ?? $penjualan->resep_os_axis ?? '-';
                $add = $latestPrescription->add ?? $penjualan->resep_add ?? '-';
                $pd = $latestPrescription->pd ?? $penjualan->resep_pd ?? '-';
                $dokter = $latestPrescription->dokter->nama_dokter ?? $latestPrescription->dokter_manual ?? $penjualan->dokter->nama_dokter ?? $penjualan->dokter_manual ?? '-';
                $tanggal = $latestPrescription->tanggal ?? $penjualan->tanggal ?? '-';
            @endphp
            
            <table class="resep-table">
                <thead>
                    <tr>
                        <th class="eye-label">👁️ Mata</th>
                        <th>🔍 SPH</th>
                        <th>🔬 CYL</th>
                        <th>📐 AXIS</th>
                        <th>➕ ADD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="eye-label">OD (Kanan)</td>
                        <td>{{ $od_sph }}</td>
                        <td>{{ $od_cyl }}</td>
                        <td>{{ $od_axis }}</td>
                        <td rowspan="2" style="background: #e8f5e8; font-weight: 600;">{{ $add }}</td>
                    </tr>
                    <tr>
                        <td class="eye-label">OS (Kiri)</td>
                        <td>{{ $os_sph }}</td>
                        <td>{{ $os_cyl }}</td>
                        <td>{{ $os_axis }}</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 5px; padding: 4px; background: rgba(255, 193, 7, 0.1); border-radius: 3px; border: 1px solid rgba(255, 193, 7, 0.3);">
                @if($pd && $pd != '-')
                <div style="text-align: center; margin-bottom: 3px; font-size: 8px; font-weight: 600; color: #495057;">
                    📏 <strong>PD (Pupillary Distance): {{ $pd }}mm</strong>
                </div>
                @endif
                @if($dokter && $dokter != '-')
                <div style="text-align: center; margin-bottom: 3px; font-size: 8px; font-weight: 500; color: #6c757d;">
                    👨‍⚕️ <em>Dokter: {{ $dokter }}</em>
                </div>
                @endif
                @if($tanggal && $tanggal != '-')
                <div style="text-align: center; font-size: 7px; color: #6c757d;">
                    📅 <em>Tanggal Resep: {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</em>
                </div>
                @endif
            </div>
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
            <div class="total-row" style="font-size: 9px; font-weight: bold; border-top: 1px solid #000; padding-top: 2px;">
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
        <div style="text-align: center; margin: 8px 0; font-style: italic; padding: 5px; border: 1px dashed #000; font-size: 8px;">
            <strong>Layanan ditanggung oleh {{ strtoupper($penjualan->pasien->service_type) }}</strong>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <div style="margin-bottom: 5px;">
                        <strong>Terima Kasih Telah Mempercayai Optik Melati</strong>
                    </div>
                    <div>
                        Barang yang sudah dibeli tidak dapat dikembalikan<br>
                        Garansi sesuai ketentuan yang berlaku
                    </div>
                    <div style="margin-top: 5px;">
                        Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                    </div>
                </div>
                @if($penjualan->barcode)
                <div class="footer-right">
                    <div class="qrcode-small">
                        <div class="qrcode-label-small">SCAN QR CODE</div>
                        <div class="qrcode-image">
                            {!! QrCode::size(45)->generate(url('/barcode/scan/' . $penjualan->barcode)) !!}
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