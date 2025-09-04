<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resep Kartu - {{ $pasien->nama_pasien }}</title>
    <style>
        @media print {
            @page {
                size: 88mm 56mm;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            font-size: 10px;
            line-height: 1.3;
        }
        
        .prescription-card {
            width: 88mm;
            height: 56mm;
            background: white;
            position: relative;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        
        /* Background floral pattern */
        .prescription-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(200, 200, 200, 0.1) 1px, transparent 1px),
                radial-gradient(circle at 80% 20%, rgba(200, 200, 200, 0.1) 1px, transparent 1px),
                radial-gradient(circle at 40% 40%, rgba(200, 200, 200, 0.05) 1px, transparent 1px);
            background-size: 20px 20px, 30px 30px, 15px 15px;
            z-index: 0;
        }
        
        /* Background logos - multiple random positions focused on bottom right to center */
        .prescription-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}'),
                url('{{ asset("image/logoabu.png") }}');
            background-size: 
                12mm 12mm,
                10mm 10mm,
                11mm 11mm,
                9mm 9mm,
                13mm 13mm,
                10mm 10mm,
                12mm 12mm,
                11mm 11mm,
                9mm 9mm,
                10mm 10mm;
            background-repeat: no-repeat;
            background-position: 
                60mm 35mm,
                65mm 40mm,
                70mm 38mm,
                55mm 45mm,
                75mm 42mm,
                50mm 48mm,
                68mm 50mm,
                45mm 35mm,
                58mm 52mm,
                72mm 48mm;
            opacity: 0.08;
            z-index: 0;
        }
        
        /* Additional random logo elements */
        .prescription-card .bg-logo-1 {
            position: absolute;
            top: 12mm;
            right: 8mm;
            width: 6mm;
            height: 6mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.06;
            z-index: 0;
            transform: rotate(25deg);
        }
        
        .prescription-card .bg-logo-2 {
            position: absolute;
            top: 25mm;
            left: 15mm;
            width: 8mm;
            height: 8mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.05;
            z-index: 0;
            transform: rotate(-30deg);
        }
        
        .prescription-card .bg-logo-3 {
            position: absolute;
            top: 18mm;
            right: 20mm;
            width: 5mm;
            height: 5mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.07;
            z-index: 0;
            transform: rotate(45deg);
        }
        
        /* Additional bottom right to center focused logos */
        .prescription-card .bg-logo-4 {
            position: absolute;
            top: 30mm;
            right: 5mm;
            width: 10mm;
            height: 10mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.06;
            z-index: 0;
            transform: rotate(-20deg);
        }
        
        .prescription-card .bg-logo-5 {
            position: absolute;
            top: 35mm;
            right: 15mm;
            width: 9mm;
            height: 9mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.05;
            z-index: 0;
            transform: rotate(30deg);
        }
        
        .prescription-card .bg-logo-6 {
            position: absolute;
            top: 40mm;
            right: 8mm;
            width: 11mm;
            height: 11mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.07;
            z-index: 0;
            transform: rotate(-45deg);
        }
        
        .prescription-card .bg-logo-7 {
            position: absolute;
            top: 45mm;
            right: 12mm;
            width: 8mm;
            height: 8mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.06;
            z-index: 0;
            transform: rotate(15deg);
        }
        
        .prescription-card .bg-logo-8 {
            position: absolute;
            top: 50mm;
            right: 6mm;
            width: 10mm;
            height: 10mm;
            background-image: url('{{ asset("image/logoabu.png") }}');
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.05;
            z-index: 0;
            transform: rotate(-35deg);
        }
        
        .card-content {
            position: relative;
            z-index: 1;
            padding: 4mm;
            height: 100%;
            box-sizing: border-box;
        }
        
        /* Patient Information Section */
        .patient-info {
            margin-bottom: 2mm;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 1.2mm;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 12mm;
            margin-right: 1mm;
            font-size: 9px;
        }
        
        .info-value {
            font-size: 9px;
            margin-left: 1mm;
        }
        
        /* Prescription Table */
        .prescription-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2.5mm;
        }
        
        .prescription-table th {
            font-weight: bold;
            text-align: center;
            padding: 0.8mm 0.5mm;
            border: 1px solid #333;
            background-color: #f8f8f8;
            font-size: 8px;
        }
        
        .prescription-table td {
            text-align: center;
            padding: 0.8mm 0.5mm;
            border: 1px solid #333;
            font-size: 8px;
        }
        
        .prescription-table .eye-label {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        /* Footer with red band */
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 10mm;
            background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%);
            display: flex;
            align-items: center;
            padding: 0 3mm;
            border-radius: 0 0 0 0;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 0 0, 15% 0, 85% 0, 100% 0);
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: -2mm;
            left: 15%;
            right: 15%;
            height: 4mm;
            background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%);
            border-radius: 0 0 8mm 8mm;
            z-index: -1;
        }
        
        .footer-logo {
            color: white;
            font-weight: bold;
            font-size: 10px;
            display: flex;
            align-items: center;
        }
        
        .footer-logo img {
            height: 30mm;
            width: auto;
            margin-right: 1mm;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Cetak Resep</button>
    
    <div class="prescription-card">
        <!-- Background logo elements -->
        <div class="bg-logo-1"></div>
        <div class="bg-logo-2"></div>
        <div class="bg-logo-3"></div>
        <div class="bg-logo-4"></div>
        <div class="bg-logo-5"></div>
        <div class="bg-logo-6"></div>
        <div class="bg-logo-7"></div>
        <div class="bg-logo-8"></div>
        
        <div class="card-content">
            <!-- Patient Information -->
            <div class="patient-info">
                <div class="info-row">
                    <span class="info-label">Nama:</span>
                    <span class="info-value" style="font-weight: bold;">{{ $pasien->nama_pasien }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tgl:</span>
                    <span class="info-value">{{ $latestPrescription ? date('d/m/Y', strtotime($latestPrescription->tanggal)) : date('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Resep:</span>
                    <span class="info-value">{{ $latestPrescription->dokter_manual ?? ($latestPrescription->dokter->nama_dokter ?? '') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Frame:</span>
                    <span class="info-value">
                        @if($latestTransaction && $latestTransaction->details)
                            @php
                                $frameDetail = $latestTransaction->details->where('itemable_type', 'App\Models\Frame')->first();
                            @endphp
                            @if($frameDetail && $frameDetail->itemable)
                                {{ $frameDetail->itemable->merk_frame ?? '' }} {{ $frameDetail->itemable->jenis_frame ?? '' }}
                            @else
                                -
                            @endif
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Glass/CR-39:</span>
                    <span class="info-value">
                        @if($latestTransaction && $latestTransaction->details)
                            @php
                                $lensaDetail = $latestTransaction->details->where('itemable_type', 'App\Models\Lensa')->first();
                            @endphp
                            @if($lensaDetail && $lensaDetail->itemable)
                                {{ $lensaDetail->itemable->merk_lensa ?? '' }} {{ $lensaDetail->itemable->jenis_lensa ?? '' }}
                            @else
                                -
                            @endif
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>
            
            <!-- Prescription Table -->
            <table class="prescription-table">
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
                        <td>{{ $latestPrescription->od_sph ?? '' }}</td>
                        <td>{{ $latestPrescription->od_cyl ?? '' }}</td>
                        <td>{{ $latestPrescription->od_axis ?? '' }}</td>
                        <td rowspan="2">{{ $latestPrescription->add ?? '' }}</td>
                        <td rowspan="2">{{ $latestPrescription->pd ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="eye-label">L</td>
                        <td>{{ $latestPrescription->os_sph ?? '' }}</td>
                        <td>{{ $latestPrescription->os_cyl ?? '' }}</td>
                        <td>{{ $latestPrescription->os_axis ?? '' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">
                <img src="{{ asset('image/logoputih.png') }}" alt="Logo Optik Melati">
                
            </div>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            // Uncomment line below to auto-print
            // window.print();
        };
    </script>
</body>
</html>
