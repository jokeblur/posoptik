<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resep A4 - {{ $pasien->nama_pasien }}</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 15mm;
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
            padding: 20px;
            background: white;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .prescription-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
            display: block;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        
        .branch-name {
            font-size: 16px;
            margin: 5px 0;
            color: #666;
        }
        
        .prescription-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-decoration: underline;
        }
        
        .patient-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        
        .patient-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
            border-bottom: 1px solid #ccc;
            padding: 5px 0;
        }
        
        .prescription-data {
            border: 2px solid #000;
            padding: 20px;
            margin-bottom: 25px;
            background: #f9f9f9;
        }
        
        .prescription-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .prescription-table th,
        .prescription-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }
        
        .prescription-table th {
            background: #e9ecef;
            font-weight: bold;
            color: #333;
        }
        
        .prescription-table td {
            background: white;
        }
        
        .additional-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .info-box {
            border: 1px solid #000;
            padding: 15px;
            background: white;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        
        .doctor-section {
            margin-top: 30px;
            text-align: right;
        }
        
        .doctor-signature {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 10px;
            width: 200px;
            margin-left: auto;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        .prescription-date {
            text-align: right;
            margin-bottom: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Cetak Resep</button>
    
    <!-- Header -->
    <div class="prescription-header">
        <img src="{{ asset('image/optik-melati.png') }}" alt="Logo Optik Melati" class="logo">
        <div class="company-name">OPTIK MELATI</div>
        <div class="branch-name">{{ auth()->user()->branch->name ?? 'Cabang Utama' }}</div>
        <div class="branch-name">{{ auth()->user()->branch->address ?? '' }}</div>
        <div class="branch-name">Telp: {{ auth()->user()->branch->phone ?? '' }}</div>
    </div>
    
    <!-- Prescription Title -->
    <div class="prescription-title">RESEP KACAMATA</div>
    
    <!-- Date -->
    <div class="prescription-date">
        Tanggal: {{ date('d F Y', strtotime($latestPrescription->tanggal)) }}
    </div>
    
    <!-- Patient Information -->
    <div class="patient-section">
        <div class="section-title">DATA PASIEN</div>
        <div class="patient-info">
            <div class="info-item">
                <span class="info-label">Nama Lengkap:</span>
                <span class="info-value">{{ $pasien->nama_pasien }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Alamat:</span>
                <span class="info-value">{{ $pasien->alamat }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">No. Telepon:</span>
                <span class="info-value">{{ $pasien->nohp }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Jenis Layanan:</span>
                <span class="info-value">{{ $pasien->service_type }}</span>
            </div>
            @if($pasien->no_bpjs)
            <div class="info-item">
                <span class="info-label">No. BPJS:</span>
                <span class="info-value">{{ $pasien->no_bpjs }}</span>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Prescription Data -->
    <div class="prescription-data">
        <div class="section-title">DATA RESEP</div>
        
        <table class="prescription-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Mata</th>
                    <th style="width: 20%;">SPH</th>
                    <th style="width: 20%;">CYL</th>
                    <th style="width: 20%;">AXIS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>OD (Kanan)</strong></td>
                    <td>{{ $latestPrescription->od_sph ?? '-' }}</td>
                    <td>{{ $latestPrescription->od_cyl ?? '-' }}</td>
                    <td>{{ $latestPrescription->od_axis ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>OS (Kiri)</strong></td>
                    <td>{{ $latestPrescription->os_sph ?? '-' }}</td>
                    <td>{{ $latestPrescription->os_cyl ?? '-' }}</td>
                    <td>{{ $latestPrescription->os_axis ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
        
        <div class="additional-info">
            <div class="info-box">
                <h4>ADD (Addition)</h4>
                <p style="font-size: 18px; font-weight: bold; text-align: center; margin: 0;">
                    {{ $latestPrescription->add ?? '-' }}
                </p>
            </div>
            <div class="info-box">
                <h4>PD (Pupillary Distance)</h4>
                <p style="font-size: 18px; font-weight: bold; text-align: center; margin: 0;">
                    {{ $latestPrescription->pd ?? '-' }}
                </p>
            </div>
        </div>
        
        @if($latestPrescription->catatan)
        <div style="margin-top: 20px;">
            <h4 style="margin: 0 0 10px 0; color: #333;">Catatan Khusus:</h4>
            <div style="border: 1px solid #ccc; padding: 15px; background: white; font-style: italic;">
                {{ $latestPrescription->catatan }}
            </div>
        </div>
        @endif
    </div>
    
    <!-- Doctor Information -->
    <div class="doctor-section">
        <div class="doctor-signature">
            <p style="margin: 0; text-align: center;">
                <strong>{{ $latestPrescription->dokter ? $latestPrescription->dokter->nama_dokter : ($latestPrescription->dokter_manual ?? 'Dokter') }}</strong>
            </p>
            <p style="margin: 5px 0 0 0; text-align: center; font-size: 12px;">
                Dokter Spesialis Mata
            </p>
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
