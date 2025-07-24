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
        @media print {
            @page {
                margin: 0;
                size: 80mm auto; /* Atur ukuran kertas saat print */
            }
            body {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h2>{{ $penjualan->branch->name ?? 'Optik Melati' }}</h2>
            <p>{{ $penjualan->branch->address ?? '' }}</p>
            <p>Telp: {{ $penjualan->branch->phone ?? '' }}</p>
        </div>

        <hr class="dashed">
        <p>No: {{ $penjualan->kode_penjualan }}</p>
        <p>Tgl: {{ tanggal_indonesia($penjualan->tanggal, false) }}</p>
        <p>Kasir: {{ $penjualan->user->name ?? 'N/A' }}</p>
        <p>Pasien: {{ $penjualan->nama_pasien ?? 'N/A' }}</p>
        <hr class="dashed">

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
                        {{ $detail->itemable->merk_frame ?? $detail->itemable->merk_lensa ?? $detail->itemable->nama_produk ?? 'Produk' }}
                        <br>
                        ({{ $detail->quantity }} x Rp {{ format_uang($detail->price) }})
                    </td>
                    <td class="price">Rp {{ format_uang($detail->subtotal) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="dashed">

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

        <hr class="dashed">

        <div class="footer">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
        </div>
    </div>

    <script>
        // Sedikit delay untuk memastikan semua konten termuat sebelum print
        setTimeout(function () { 
            window.print();
            // window.close(); // Uncomment baris ini jika ingin tab otomatis tertutup setelah print
        }, 500);
    </script>
</body>
</html> 