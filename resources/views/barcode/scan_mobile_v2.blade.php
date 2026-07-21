@extends('layouts.master')

@section('title', 'Scan QR Code - Mobile')

@section('content')
<style>
    #reader {
        width: 100% !important;
        max-width: 360px !important;
        height: 360px !important;
        margin: 20px auto !important;
        background: #000 !important;
        border: 3px solid #333 !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        position: relative !important;
    }
    
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }
    
    #reader canvas {
        width: 100% !important;
        height: 100% !important;
    }
    
    .scanner-info {
        padding: 15px;
        margin: 15px 0;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .btn-group-scanner {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
        margin: 15px 0;
    }
    
    .btn-group-scanner button {
        flex: 1;
        min-width: 120px;
    }
</style>

<div class="container-fluid" style="padding: 10px;">
    <div class="row">
        <div class="col-md-12">
            <!-- Scanner Box -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-qrcode"></i> Scan QR Code
                    </h3>
                </div>
                <div class="box-body">
                    <!-- Debug Info -->
                    <div class="scanner-info alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Status:</strong> <span id="debugStatus">Memuat library scanner...</span>
                    </div>
                    
                    <!-- Camera Container -->
                    <div style="text-align: center;">
                        <div id="reader"></div>
                    </div>
                    
                    <!-- Control Buttons -->
                    <div class="btn-group-scanner">
                        <button id="startScan" class="btn btn-success btn-lg">
                            <i class="fa fa-play"></i> Mulai Scan
                        </button>
                        <button id="stopScan" class="btn btn-danger btn-lg" style="display: none;">
                            <i class="fa fa-stop"></i> Hentikan
                        </button>
                        <button id="switchCamera" class="btn btn-info btn-lg" style="display: none;">
                            <i class="fa fa-refresh"></i> Ganti Kamera
                        </button>
                    </div>
                    
                    <!-- Camera Info -->
                    <div id="cameraInfo" class="scanner-info alert alert-info" style="display: none;">
                        <i class="fa fa-camera"></i>
                        <strong>Kamera:</strong> <span id="cameraLabel">-</span>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="scanner-info alert alert-success">
                        <h5><i class="fa fa-lightbulb-o"></i> Petunjuk:</h5>
                        <ol style="margin: 10px 0; padding-left: 20px;">
                            <li>Klik tombol "Mulai Scan"</li>
                            <li>Izinkan akses kamera ketika diminta</li>
                            <li>Arahkan kamera ke QR Code</li>
                            <li>Tunggu hingga otomatis terbaca</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <!-- Manual Input Box -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h4 class="box-title">
                        <i class="fa fa-keyboard-o"></i> Input Manual (Jika Kamera Error)
                    </h4>
                </div>
                <div class="box-body">
                    <form id="searchForm">
                        <div class="form-group">
                            <label for="barcodeInput">Masukkan Kode Transaksi:</label>
                            <input type="text" id="barcodeInput" class="form-control" 
                                   placeholder="Contoh: TRX-20260721-001" autofocus required>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-search"></i> Cari Transaksi
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Results Box -->
            <div id="resultsSection" class="box box-info" style="display: none;">
                <div class="box-header with-border">
                    <h4 class="box-title">
                        <i class="fa fa-info-circle"></i> Hasil Pencarian
                    </h4>
                </div>
                <div class="box-body" id="searchResults"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Html5Qrcode Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
// Wait for Html5Qrcode to load
function waitForLibrary(callback, maxWait = 10000) {
    let waited = 0;
    const interval = setInterval(() => {
        if (typeof Html5Qrcode !== 'undefined') {
            clearInterval(interval);
            console.log('Html5Qrcode loaded');
            callback();
        } else if (waited >= maxWait) {
            clearInterval(interval);
            console.error('Html5Qrcode failed to load');
            updateStatus('Error: Library scanner gagal dimuat');
        }
        waited += 100;
    }, 100);
}

// Global scanner instance
let scanner = null;
let isScanning = false;
let availableCameras = [];
let currentCameraIndex = 0;

function updateStatus(msg) {
    console.log('Status:', msg);
    document.getElementById('debugStatus').textContent = msg;
}

function showError(msg) {
    console.error('Error:', msg);
    updateStatus('Error: ' + msg);
    Swal.fire('Error', msg, 'error');
}

function showSuccess(msg) {
    Swal.fire('Berhasil', msg, 'success');
}

async function getAvailableCameras() {
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            console.log('enumerateDevices not supported');
            return [];
        }
        
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cameras = devices.filter(device => device.kind === 'videoinput');
        console.log('Available cameras:', cameras.length, cameras);
        
        availableCameras = cameras;
        
        // Try to find back camera
        const backIndex = cameras.findIndex(cam => 
            cam.label.toLowerCase().includes('back') ||
            cam.label.toLowerCase().includes('rear') ||
            cam.label.toLowerCase().includes('environment')
        );
        
        if (backIndex !== -1) {
            currentCameraIndex = backIndex;
        } else {
            currentCameraIndex = cameras.length > 0 ? cameras.length - 1 : 0;
        }
        
        console.log('Using camera index:', currentCameraIndex);
        return cameras;
    } catch (error) {
        console.error('Error getting cameras:', error);
        return [];
    }
}

function updateCameraInfo() {
    if (availableCameras.length > 0) {
        const cam = availableCameras[currentCameraIndex];
        const label = cam.label || 'Kamera ' + (currentCameraIndex + 1);
        document.getElementById('cameraLabel').textContent = label;
        document.getElementById('cameraInfo').style.display = 'block';
    }
}

async function startScanning() {
    if (isScanning) return;
    
    try {
        updateStatus('Mempersiapkan kamera...');
        
        if (!scanner) {
            scanner = new Html5Qrcode('reader');
        }
        
        const cameras = await getAvailableCameras();
        console.log('Starting scan with', cameras.length, 'cameras');
        
        if (cameras.length === 0) {
            // Try with facingMode only
            updateStatus('Mencoba dengan facingMode...');
            await scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                onScanSuccess,
                onScanFailure
            );
        } else {
            // Try with specific camera
            updateStatus('Memulai scanner...');
            await scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    videoConstraints: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                },
                onScanSuccess,
                onScanFailure
            );
        }
        
        isScanning = true;
        updateUI();
        updateStatus('Scanner aktif - Arahkan ke QR Code');
        updateCameraInfo();
        
    } catch (error) {
        console.error('Error starting scanner:', error);
        showError('Gagal memulai scanner: ' + error.message);
    }
}

async function stopScanning() {
    if (!scanner || !isScanning) return;
    
    try {
        await scanner.stop();
        scanner.clear();
        isScanning = false;
        updateUI();
        updateStatus('Scanner dihentikan');
    } catch (error) {
        console.error('Error stopping scanner:', error);
    }
}

async function switchCamera() {
    if (!isScanning || availableCameras.length <= 1) return;
    
    try {
        await stopScanning();
        currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
        setTimeout(() => startScanning(), 500);
    } catch (error) {
        console.error('Error switching camera:', error);
        showError('Gagal mengganti kamera');
    }
}

function onScanSuccess(decodedText) {
    console.log('QR Code scanned:', decodedText);
    showSuccess('QR Code berhasil dibaca!');
    processScannedCode(decodedText);
}

function onScanFailure(error) {
    // Silent fail
}

function processScannedCode(code) {
    updateStatus('Memproses QR Code: ' + code);
    searchTransaksi(code);
}

function updateUI() {
    const startBtn = document.getElementById('startScan');
    const stopBtn = document.getElementById('stopScan');
    const switchBtn = document.getElementById('switchCamera');
    
    if (isScanning) {
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-block';
        if (availableCameras.length > 1) {
            switchBtn.style.display = 'inline-block';
        }
    } else {
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
        switchBtn.style.display = 'none';
    }
}

function searchTransaksi(barcode) {
    $.ajax({
        url: '{{ route("barcode.search") }}',
        method: 'POST',
        data: {
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                displayResults(response.data);
            } else {
                showError('Transaksi tidak ditemukan');
            }
        },
        error: function(xhr) {
            showError('Error searching transaction');
        }
    });
}

function displayResults(data) {
    const html = `
        <div class="alert alert-success">
            <h5><i class="fa fa-check-circle"></i> Transaksi Ditemukan!</h5>
            <p><strong>Kode:</strong> ${data.kode_penjualan}</p>
            <p><strong>Pasien:</strong> ${data.pasien?.nama_pasien || '-'}</p>
            <p><strong>Status:</strong> ${data.status_pengerjaan}</p>
        </div>
        <button class="btn btn-primary btn-block" onclick="location.href='{{ url('/penjualan') }}/${data.id}'">
            <i class="fa fa-eye"></i> Lihat Detail
        </button>
    `;
    document.getElementById('searchResults').innerHTML = html;
    document.getElementById('resultsSection').style.display = 'block';
}

// Initialize
waitForLibrary(function() {
    updateStatus('Scanner siap. Klik "Mulai Scan"');
    
    // Event listeners
    document.getElementById('startScan').addEventListener('click', startScanning);
    document.getElementById('stopScan').addEventListener('click', stopScanning);
    document.getElementById('switchCamera').addEventListener('click', switchCamera);
    
    // Form submission
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const barcode = document.getElementById('barcodeInput').value.trim();
        if (barcode) searchTransaksi(barcode);
    });
});
</script>
@endpush
