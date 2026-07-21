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
                                    
                                    <!-- Camera Info -->
                                    <div id="cameraInfo" class="mt-2" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fa fa-camera"></i> 
                                            <span id="cameraLabel">Kamera: -</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Info -->
                                    <div id="debugInfo" class="mt-2">
                                        <div class="alert alert-warning">
                                            <i class="fa fa-info-circle"></i> 
                                            Status: <span id="debugStatus">Memuat...</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Instructions -->
                                    <div class="mt-3">
                                        <div class="alert alert-success">
                                            <h5><i class="fa fa-lightbulb-o"></i> Cara Menggunakan:</h5>
                                            <ol class="text-left">
                                                <li>Klik tombol "Mulai Scan"</li>
                                                <li>Izinkan akses kamera jika diminta</li>
                                                <li>Arahkan kamera ke QR Code</li>
                                                <li>Tunggu hingga QR Code terbaca otomatis</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Manual Input Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <i class="fa fa-keyboard-o"></i> Input Manual
                                    </h4>
                                </div>
                                <div class="box-body">
                                    <form id="searchForm">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="barcodeInput">Masukkan Kode Transaksi:</label>
                                                    <input type="text" id="barcodeInput" class="form-control" 
                                                           placeholder="Masukkan kode transaksi..." required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">
                                                        <i class="fa fa-search"></i> Cari
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results Section -->
                    <div id="resultsSection" style="display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h4 class="box-title">
                                            <i class="fa fa-info-circle"></i> Hasil Pencarian
                                        </h4>
                                    </div>
                                    <div class="box-body" id="searchResults">
                                        <!-- Results will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Optimized Styles -->
<style>
/* Mobile-first responsive design */
@media (max-width: 768px) {
    .container-fluid {
        padding: 5px !important;
    }
    
    .box {
        margin-bottom: 10px !important;
    }
    
    .box-header {
        padding: 10px 15px !important;
    }
    
    .box-body {
        padding: 15px !important;
    }
    
    #reader {
        max-width: 300px !important;
        min-height: 300px !important;
        aspect-ratio: 1 / 1 !important;
        border: 2px dashed #ddd !important;
        border-radius: 8px !important;
        background: #111 !important;
    }
    
    .btn-lg {
        padding: 12px 20px !important;
        font-size: 16px !important;
        margin: 5px !important;
        width: 100% !important;
        max-width: 200px !important;
    }
    
    .alert {
        margin: 10px 0 !important;
        padding: 10px !important;
        font-size: 14px !important;
    }
    
    .form-control {
        height: 45px !important;
        font-size: 16px !important;
    }
}

/* Camera viewport optimization */
#reader {
    max-width: 360px !important;
    min-height: 360px !important;
    aspect-ratio: 1 / 1 !important;
    margin: 0 auto !important;
    background: #111 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    position: relative !important;
}

#reader video {
    width: 100% !important;
    height: 100% !important;
    border-radius: 8px !important;
    background: transparent !important;
    object-fit: cover !important;
    display: block !important;
    filter: brightness(1.18) contrast(1.08) !important;
}

#reader canvas {
    width: 100% !important;
    height: 100% !important;
    border-radius: 8px !important;
    background: transparent !important;
    display: block !important;
}

/* Fix for white screen issue */
#reader > div {
    background: transparent !important;
    border-radius: 8px !important;
}

#reader > div > div {
    background: transparent !important;
}

#reader__scan_region {
    min-height: 360px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#reader__dashboard {
    background: #fff !important;
    padding: 12px !important;
}

/* Loading state for camera */
#reader:empty::before {
    content: 'Memuat Kamera...';
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 360px;
    background: #111;
    color: #fff;
    font-size: 18px;
    border-radius: 8px;
}

/* Loading state */
.scanner-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    background: #f9f9f9;
    border: 2px dashed #ddd;
    border-radius: 8px;
}

.scanner-loading::before {
    content: 'Memuat Scanner...';
    font-size: 18px;
    color: #666;
}

/* Success feedback */
.scan-success {
    animation: pulse-green 1s ease-in-out;
}

@keyframes pulse-green {
    0% { background-color: #d4edda; }
    50% { background-color: #c3e6cb; }
    100% { background-color: #d4edda; }
}
</style>
@endsection

@push('scripts')
<!-- Latest Html5Qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<!-- Mobile QR Scanner -->
<script src="{{ asset('js/mobile-qr-scanner.js') }}"></script>

<script>
// Global function to handle scanned QR codes
window.onQRCodeScanned = function(code) {
    console.log('QR Code scanned:', code);
    
    // Process the scanned code
    processScannedCode(code);
};

// Wait for scanner to be initialized
let initCheckCount = 0;
const initCheckInterval = setInterval(() => {
    if (window.mobileQRScanner) {
        clearInterval(initCheckInterval);
        console.log('Mobile QR Scanner initialized successfully');
        
        // Additional UI setup
        if ($(window).width() <= 768) {
            $('#barcodeInput').focus();
        }
    } else if (initCheckCount > 50) { // Timeout after 5 seconds
        clearInterval(initCheckInterval);
        console.warn('Mobile QR Scanner initialization timeout');
    }
    initCheckCount++;
}, 100);

function processScannedCode(code) {
    // Show loading
    showLoading('Memproses QR Code...');
    
    // Search for transaction
    searchTransaksi(code);
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
            hideLoading();
            
            if (response.success) {
                displaySearchResults(response.data);
            } else {
                showError('Transaksi tidak ditemukan: ' + response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            let message = 'Error: Gagal mencari transaksi';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            
            showError(message);
        }
    });
}

function displaySearchResults(data) {
    const resultsHtml = `
        <div class="alert alert-success">
            <h5><i class="fa fa-check-circle"></i> Transaksi Ditemukan!</h5>
            <p><strong>Kode:</strong> ${data.kode_penjualan}</p>
            <p><strong>Pasien:</strong> ${data.pasien_name}</p>
            <p><strong>Total:</strong> Rp ${formatNumber(data.total)}</p>
            <p><strong>Status:</strong> ${data.status_pengerjaan}</p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <button class="btn btn-info btn-block" onclick="viewDetail('${data.id}')">
                    <i class="fa fa-eye"></i> Lihat Detail
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-warning btn-block" onclick="updateStatus('${data.id}')">
                    <i class="fa fa-edit"></i> Update Status
                </button>
            </div>
        </div>
    `;
    
    $('#searchResults').html(resultsHtml);
    $('#resultsSection').show();
    
    // Scroll to results
    $('html, body').animate({
        scrollTop: $('#resultsSection').offset().top - 100
    }, 500);
}

function viewDetail(transaksiId) {
    // Open detail modal or redirect
    window.open('{{ url("/penjualan") }}/' + transaksiId, '_blank');
}

function updateStatus(transaksiId) {
    // Show status update modal
    Swal.fire({
        title: 'Update Status',
        input: 'select',
        inputOptions: {
            'Menunggu Pengerjaan': 'Menunggu Pengerjaan',
            'Sedang Dikerjakan': 'Sedang Dikerjakan',
            'Selesai': 'Selesai',
            'Diambil': 'Diambil'
        },
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            updateTransaksiStatus(transaksiId, result.value);
        }
    });
}

function updateTransaksiStatus(transaksiId, newStatus) {
    $.ajax({
        url: '{{ route("barcode.update-status") }}',
        method: 'POST',
        data: {
            transaksi_id: transaksiId,
            status: newStatus,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Status berhasil diupdate!');
                // Refresh results
                searchTransaksi($('#barcodeInput').val());
            } else {
                showError('Gagal update status: ' + response.message);
            }
        },
        error: function(xhr) {
            showError('Error: Gagal update status');
        }
    });
}

// Form submission
$('#searchForm').on('submit', function(e) {
    e.preventDefault();
    const barcode = $('#barcodeInput').val().trim();
    
    if (barcode) {
        searchTransaksi(barcode);
    } else {
        showError('Masukkan kode transaksi terlebih dahulu');
    }
});

// Utility functions
function showLoading(message) {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

function showError(message) {
    Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function showSuccess(message) {
    Swal.fire({
        title: 'Berhasil',
        text: message,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}
</script>
@endpush
