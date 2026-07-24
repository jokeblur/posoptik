<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Barcode - {{ $penjualan->kode_penjualan }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: 120mm 80mm;
            margin: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 10mm;
            background: white;
            font-size: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80mm;
            width: 100%;
        }
        
        .container {
            text-align: center;
            padding: 15mm;
            background: white;
            border-radius: 8px;
            max-width: 100mm;
        }
        
        .header {
            margin-bottom: 10mm;
            text-align: center;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            color: #000;
        }
        
        .kode {
            font-size: 12px;
            font-weight: 600;
            margin: 3mm 0 5mm 0;
            color: #333;
        }
        
        .qr-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 5mm 0;
            background: white;
            padding: 5mm;
            border-radius: 4px;
        }
        
        .qr-container img,
        .qr-container svg {
            width: auto;
            height: auto;
            max-width: 60mm;
            max-height: 60mm;
        }
        
        .barcode {
            font-size: 11px;
            font-weight: 600;
            margin: 5mm 0 0 0;
            letter-spacing: 1px;
            font-family: 'Courier New', monospace;
            color: #000;
        }
        
        .footer {
            font-size: 8px;
            margin-top: 5mm;
            color: #666;
        }
        
        .button-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 15px;
            no-print: true;
        }
        
        button {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        button:hover {
            background: #0056b3;
        }
        
        .back-btn {
            background: #6c757d;
        }
        
        .back-btn:hover {
            background: #545b62;
        }
        
        @media print {
            body { 
                margin: 0;
                padding: 0;
                min-height: auto;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .button-container { display: none; }
        }
    </style>
</head>
<body>
    <div class="button-container" style="position: absolute; top: 10px; left: 10px; z-index: 1000;">
        <a href="{{ route('penjualan.show', $penjualan->id) }}" style="padding: 8px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">Kembali</a>
        <button onclick="window.print()" style="margin-left: 5px;">Cetak Barcode</button>
    </div>

    <div class="container">
        <div class="header">
            <h1 class="company-name">OPTIK MELATI</h1>
            <div class="kode">{{ $penjualan->kode_penjualan }}</div>
        </div>

        @if($penjualan->barcode)
        <div class="qr-container">
            {!! QrCode::size(150)->generate(url('/barcode/scan/' . $penjualan->barcode)) !!}
        </div>
        <div class="barcode">{{ $penjualan->barcode }}</div>
        @endif

        <div class="footer">
            <p>Tunjukkan kode QR ini untuk konfirmasi</p>
            <p>Scan untuk tracking pesanan Anda</p>
        </div>
    </div>
</body>
</html>
