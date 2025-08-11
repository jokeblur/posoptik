<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resep - {{ $pasien->nama_pasien }}</title>
    <style>
        @media print {
            @page {
                size: 90mm 55mm;
                margin: 5mm;
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
            padding: 10px;
            background: white;
            font-size: 10px;
            line-height: 1.2;
        }
        
        .prescription-card {
            width: 80mm;
            height: 45mm;
            border: 2px solid #000;
            padding: 8px;
            box-sizing: border-box;
            background: white;
            position: relative;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        
        .logo {
            width: 25px;
            height: 25px;
            margin: 0 auto 3px;
            display: block;
        }
        
        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }
        
        .branch-name {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .patient-info {
            margin-bottom: 8px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 2px;
        }
        
        .info-label {
            font-weight: bold;
            width: 25px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
        }
        
        .prescription-data {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 8px;
        }
        
        .prescription-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        .prescription-table th,
        .prescription-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
        }
        
        .prescription-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .doctor-info {
            text-align: center;
            font-size: 9px;
            margin-top: 5px;
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
        }
        
        .print-button:hover {
            background: #0056b3;
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
        
        <!-- Patient Information -->
        <div class="patient-info">
            <div class="info-row">
                <span class="info-label">Nama:</span>
                <span class="info-value">{{ $pasien->nama_pasien }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Alamat:</span>
                <span class="info-value">{{ $pasien->alamat }}</span>
            </div>
            <!-- <div class="info-row">
                <span class="info-label">T:</span>
                <span class="info-value">{{ $pasien->nohp }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">L:</span>
                <span class="info-value">{{ $pasien->service_type }}</span>
            </div>
            @if($pasien->no_bpjs)
            <div class="info-row">
                <span class="info-label">B:</span>
                <span class="info-value">{{ $pasien->no_bpjs }}</span>
            </div>
            @endif -->
        </div>
        
        <!-- Prescription Data -->
        <div class="prescription-data">
            <table class="prescription-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>SPH</th>
                        <th>CYL</th>
                        <th>AXIS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>OD</strong></td>
                        <td>{{ $latestPrescription->od_sph ?? '-' }}</td>
                        <td>{{ $latestPrescription->od_cyl ?? '-' }}</td>
                        <td>{{ $latestPrescription->od_axis ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>OS</strong></td>
                        <td>{{ $latestPrescription->os_sph ?? '-' }}</td>
                        <td>{{ $latestPrescription->os_cyl ?? '-' }}</td>
                        <td>{{ $latestPrescription->os_axis ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 5px; font-size: 8px;">
                <strong>ADD:</strong> {{ $latestPrescription->add ?? '-' }} | 
                <strong>PD:</strong> {{ $latestPrescription->pd ?? '-' }}
            </div>
            
            @if($latestPrescription->catatan)
            <div style="margin-top: 3px; font-size: 8px;">
                <strong>Catatan:</strong> {{ $latestPrescription->catatan }}
            </div>
            @endif
        </div>
        
        <!-- Doctor Information -->
        <!-- <div class="doctor-info">
            <strong>Dokter:</strong> 
            {{ $latestPrescription->dokter ? $latestPrescription->dokter->nama_dokter : ($latestPrescription->dokter_manual ?? '-') }}
            <br>
            <strong>Tanggal:</strong> {{ date('d/m/Y', strtotime($latestPrescription->tanggal)) }}
        </div> -->
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
