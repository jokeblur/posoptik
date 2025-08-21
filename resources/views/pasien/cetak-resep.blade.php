<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resep - {{ $pasien->nama_pasien }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: 130mm 90mm;
                margin: 3mm;
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
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 5px;
            background: white;
            font-size: 9px;
            line-height: 1.2;
        }
        
        .prescription-card {
            width: 124mm;
            height: 84mm;
            border: 1px solid #ddd;
            padding: 8px;
            box-sizing: border-box;
            background: white;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        
        .logo {
            width: 18px;
            height: 18px;
            margin: 0 auto 3px;
            display: block;
        }
        
        .company-name {
            font-size: 11px;
            font-weight: 700;
            margin: 0;
            color: #2c3e50;
            letter-spacing: 0.5px;
        }
        
        .form-fields {
            margin-bottom: 8px;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 4px;
            align-items: center;
        }
        
        .form-label {
            font-weight: 600;
            width: 35px;
            flex-shrink: 0;
            color: #2c3e50;
            font-size: 8px;
        }
        
        .form-line {
            flex: 1;
            height: 1px;
            background: #bdc3c7;
            margin-left: 8px;
        }
        
        .prescription-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-bottom: 8px;
        }
        
        .prescription-table th,
        .prescription-table td {
            border: 1px solid #bdc3c7;
            padding: 3px;
            text-align: center;
        }
        
        .prescription-table th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            font-size: 6px;
            text-transform: uppercase;
        }
        
        .prescription-table td {
            background: white;
            font-weight: 500;
        }
        
        .prescription-table td:first-child {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            text-align: left;
            padding-left: 6px;
        }
        
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #e74c3c, #f39c12);
            border-radius: 0 0 4px 4px;
        }
        
        .footer-logo {
            position: absolute;
            bottom: 2px;
            left: 4px;
            width: 12px;
            height: 12px;
        }
        
        .footer-text {
            position: absolute;
            bottom: 1px;
            left: 20px;
            font-size: 6px;
            font-weight: 600;
            color: white;
            letter-spacing: 0.3px;
        }
        
        .print-button {
            position: fixed;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
        }
        
        .print-button:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Cetak</button>
    
    <div class="prescription-card">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" class="logo">
            <div class="company-name">OPTIK MELATI</div>
            <!-- <div class="branch-name">{{ auth()->user()->branch->name ?? 'Cabang Utama' }}</div> -->
        </div>
        
        <!-- Form Fields -->
        <div class="form-fields">
            <div class="form-row">
                <span class="form-label">Nama</span>
                <div class="form-line"></div>
            </div>
            <div class="form-row">
                <span class="form-label">Tgl</span>
                <div class="form-line"></div>
            </div>
            <div class="form-row">
                <span class="form-label">Resep</span>
                <div class="form-line"></div>
            </div>
            <div class="form-row">
                <span class="form-label">Frame</span>
                <div class="form-line"></div>
            </div>
            <div class="form-row">
                <span class="form-label">Glass/CR-39</span>
                <div class="form-line"></div>
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
                    <td><strong>R</strong></td>
                    <td>{{ $latestPrescription->od_sph ?? '' }}</td>
                    <td>{{ $latestPrescription->od_cyl ?? '' }}</td>
                    <td>{{ $latestPrescription->od_axis ?? '' }}</td>
                    <td rowspan="2">{{ $latestPrescription->add ?? '' }}</td>
                    <td rowspan="2">{{ $latestPrescription->pd ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>L</strong></td>
                    <td>{{ $latestPrescription->os_sph ?? '' }}</td>
                    <td>{{ $latestPrescription->os_cyl ?? '' }}</td>
                    <td>{{ $latestPrescription->os_axis ?? '' }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Footer -->
        <div class="footer">
            <img src="{{ asset('image/optik-melati.png') }}" alt="Logo" class="footer-logo">
            <span class="footer-text">OPTIK MELATI</span>
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
