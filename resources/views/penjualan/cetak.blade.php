<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            color: #000;
            width: 80mm; /* Lebar kertas printer POS */
            margin: 0;
            padding: 5px;
        }
        .container {
            width: 100%;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 10px;
        }
        h2 {
            margin: 0;
            font-size: 12pt;
        }
        p {
            margin: 2px 0;
        }
        .info-section {
            margin: 8px 0;
            padding: 5px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }
        .info-label {
            font-weight: bold;
            min-width: 60px;
        }
        .info-value {
            text-align: right;
            flex: 1;
        }
        .pasien-info {
            background: #f8f9fa;
            padding: 5px;
            margin: 5px 0;
            border-left: 3px solid #007bff;
        }
        .resep-info {
            background: #fff3cd;
            padding: 5px;
            margin: 5px 0;
            border-left: 3px solid #ffc107;
        }
        .resep-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 9pt;
        }
        .resep-table th, .resep-table td {
            padding: 2px 3px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .resep-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .resep-table .eye-label {
            font-weight: bold;
            background: #e9ecef;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.items th, table.items td {
            padding: 3px 0;
            border-bottom: 1px dashed #555;
        }
        table.items th {
            text-align: left;
        }
        table.items td.price {
            text-align: right;
        }
        .summary {
            margin-top: 10px;
        }
        .summary td {
            padding: 1px 0;
        }
        .summary .label {
            text-align: left;
        }
        .summary .value {
            text-align: right;
        }
        hr.dashed {
            border: 0;
            border-top: 1px dashed #555;
        }
        .qrcode-section {
            text-align: center;
            margin: 10px 0;
            padding: 5px 0;
            border: 1px dashed #555;
        }
        .qrcode {
            margin: 5px auto;
            display: block;
        }
        .qrcode-label {
            font-size: 8pt;
            margin: 3px 0;
        }
        @media print {
            @page {
                margin: 0;
                size: 80mm auto; /* Atur ukuran kertas saat print */
            }
            body {
                margin: 0.5cm;
            }
            .print-button-container {
                display: none; /* Hide button when printing */
            }
        }
    </style>
</head>
<body {{-- onload="window.print()" --}}>
    <div class="container">
        <div class="header">
            <h2>{{ $penjualan->branch->name ?? 'Optik Melati' }}</h2>
            @php
                $cleanAddress = preg_replace('/^[A-Z]{2}\d+\+[A-Z]{2}\d+,\s*/', '', $penjualan->branch->address ?? '');
            @endphp
            <p>{{ $cleanAddress }}</p>
            <p>Telp: {{ $penjualan->branch->phone ?? '' }}</p>
        </div>

        <hr class="dashed">
        
        <!-- Informasi Transaksi -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">No:</span>
                <span class="info-value">{{ $penjualan->kode_penjualan }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tgl:</span>
                <span class="info-value">{{ tanggal_indonesia($penjualan->tanggal, false) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir:</span>
                <span class="info-value">{{ $penjualan->user->name ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Informasi Pasien -->
        <div class="pasien-info">
            <div class="info-row">
                <span class="info-label">Pasien:</span>
                <span class="info-value">{{ $penjualan->pasien->nama_pasien ?? 'N/A' }}</span>
            </div>
            @if($penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']))
            <div class="info-row">
                <span class="info-label">Layanan:</span>
                <span class="info-value">{{ strtoupper($penjualan->pasien->service_type) }}</span>
            </div>
            @if($penjualan->pasien->no_bpjs)
            <div class="info-row">
                <span class="info-label">BPJS:</span>
                <span class="info-value">{{ $penjualan->pasien->no_bpjs }}</span>
            </div>
            @endif
            @endif
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ $penjualan->status_pengerjaan ?? 'Menunggu Pengerjaan' }}</span>
            </div>
        </div>

        <!-- Informasi Resep -->
        @if($penjualan->pasien && ($penjualan->pasien->resep_od_sph || $penjualan->pasien->resep_os_sph))
        <div class="resep-info">
            <div style="text-align: center; font-weight: bold; margin-bottom: 3px;">RESEP LENSA</div>
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
            <div style="text-align: center; margin-top: 3px;">
                <strong>PD: {{ $penjualan->pasien->resep_pd }}mm</strong>
            </div>
            @endif
            @if($penjualan->pasien->resep_dokter)
            <div style="text-align: center; margin-top: 3px; font-size: 9pt;">
                <em>Dokter: {{ $penjualan->pasien->resep_dokter }}</em>
            </div>
            @endif
        </div>
        @endif
        
        @if($penjualan->barcode)
        <div class="qrcode-section">
            <div class="qrcode-label">SCAN QR CODE UNTUK UPDATE STATUS</div>
            <div class="qrcode">
                {!! QrCode::size(100)->generate(url('/barcode/scan/' . $penjualan->barcode)) !!}
            </div>
            <div class="qrcode-label">{{ $penjualan->barcode }}</div>
        </div>
        @endif
        
        <hr class="dashed">

        @php
            $isBPJS = $penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']);
        @endphp
        
        @if($isBPJS)
        <!-- Untuk BPJS hanya tampilkan daftar produk tanpa harga -->
        <table class="items">
            <thead>
                <tr>
                    <th>Produk/Layanan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->details as $detail)
                <tr>
                    <td>
                        {{ $detail->itemable->merk_frame ?? $detail->itemable->merk_lensa ?? 'Produk' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <!-- Untuk non-BPJS tampilkan dengan harga -->
        <table class="items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="price">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->details as $detail)
                <tr>
                    <td>
                        {{ $detail->itemable->merk_frame ?? $detail->itemable->merk_lensa ?? 'Produk' }}
                        <br>
                        ({{ $detail->quantity }} x Rp {{ format_uang($detail->price) }})
                    </td>
                    <td class="price">
                        Rp {{ format_uang($detail->subtotal) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <hr class="dashed">

        @if(!$isBPJS)
        <!-- Hanya tampilkan summary untuk non-BPJS -->
        <table class="summary">
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">Rp {{ format_uang($penjualan->details->sum('subtotal')) }}</td>
            </tr>
            <tr>
                <td class="label">Diskon</td>
                <td class="value">Rp {{ format_uang($penjualan->diskon) }}</td>
            </tr>
            <tr>
                <td class="label"><strong>Total</strong></td>
                <td class="value"><strong>Rp {{ format_uang($penjualan->total) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Bayar</td>
                <td class="value">Rp {{ format_uang($penjualan->bayar) }}</td>
            </tr>
            <tr>
                <td class="label">Kekurangan</td>
                <td class="value">Rp {{ format_uang($penjualan->kekurangan) }}</td>
            </tr>
        </table>
        @else
        <!-- Untuk BPJS, tambahkan pesan khusus -->
        <div style="text-align: center; margin: 10px 0; font-style: italic;">
            <p>Layanan ditanggung oleh {{ strtoupper($penjualan->pasien->service_type) }}</p>
        </div>
        @endif

        <hr class="dashed">

        <div class="print-button-container" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Cetak Sekarang</button>
        </div>

        <div class="footer">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
        </div>
    </div>

    <script>
        // Sedikit delay untuk memastikan semua konten termuat sebelum print
        /*
        setTimeout(function () { 
            window.print();
            // window.close(); // Uncomment baris ini jika ingin tab otomatis tertutup setelah print
        }, 500);
        */
    </script>
</body>
</html> 