@extends('layouts.master')

@section('title', 'Scan Barcode Transaksi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Scan Barcode Transaksi</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <!-- Camera Section -->
                        <div class="col-md-6">
                                                    <div class="box box-info">
                            <div class="box-header with-border">
                                <h4 class="box-title">Scanner QR Code</h4>
                            </div>
                            <div class="box-body text-center">
                                <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                                <div class="mt-3">
                                    <button id="startScan" class="btn btn-primary">Mulai Scan QR Code</button>
                                    <button id="stopScan" class="btn btn-danger" style="display: none;">Stop Scan</button>
                                    <button id="switchCamera" class="btn btn-info" style="display: none;">
                                        <i class="fa fa-refresh"></i> Ganti Kamera
                                    </button>
                                </div>
                                <div id="cameraInfo" class="mt-2" style="display: none;">
                                    <small class="text-muted">
                                        <i class="fa fa-camera"></i> <span id="cameraLabel">Kamera: -</span>
                                    </small>
                                </div>
                                <div id="debugInfo" class="mt-2">
                                    <small class="text-info">
                                        <i class="fa fa-info-circle"></i> 
                                        Status: <span id="debugStatus">Memuat...</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Manual Input Section -->
                        <!-- <div class="col-md-6">
                                                    <div class="box box-success">
                            <div class="box-header with-border">
                                <h4 class="box-title">Input Manual</h4>
                            </div>
                            <div class="box-body">
                                <form id="searchForm">
                                    <div class="form-group">
                                        <label for="barcodeInput">Kode QR Code:</label>
                                        <input type="text" id="barcodeInput" class="form-control" placeholder="Masukkan kode QR code atau scan dengan kamera">
                                    </div>
                                    <button type="submit" class="btn btn-success">Cari Transaksi</button>
                                </form>
                            </div>
                        </div>
                        </div> -->
                    </div>

                    <!-- Result Section -->
                    <div id="resultSection" style="display: none;">
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header with-border">
                                        <h4 class="box-title">Hasil Pencarian</h4>
                                    </div>
                                    <div class="box-body">
                                        <div id="transaksiInfo"></div>
                                        
                                        <!-- Status Update Section -->
                                        <div id="statusUpdateSection" class="mt-3" style="display: none;">
                                            <hr>
                                            <h5>Update Status Pengerjaan</h5>
                                            <div class="form-group">
                                                <label for="statusSelect">Status Baru:</label>
                                                <select id="statusSelect" class="form-control">
                                                    <option value="">Pilih Status</option>
                                                    <option value="Menunggu Pengerjaan">Menunggu Pengerjaan</option>
                                                    <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                                                    <option value="Selesai Dikerjakan">Selesai Dikerjakan</option>
                                                    <option value="Sudah Diambil">Sudah Diambil</option>
                                                </select>
                                            </div>
                                            <button id="updateStatusBtn" class="btn btn-warning">Update Status</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Result Section -->
                    @if(isset($transaksi))
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="box box-success">
                                <div class="box-header with-border">
                                    <h4 class="box-title">Data Transaksi</h4>
                                </div>
                                <div class="box-body">
                                    <div id="directTransaksiInfo">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>Kode Transaksi</th>
                                                        <td>{{ $transaksi->kode_penjualan }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Barcode</th>
                                                        <td><strong>{{ $transaksi->barcode }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <td>{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Pasien</th>
                                                        <td>{{ $transaksi->nama_pasien ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Kasir</th>
                                                        <td>{{ $transaksi->user ? $transaksi->user->name : 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cabang</th>
                                                        <td>{{ $transaksi->branch ? $transaksi->branch->name : 'N/A' }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>Total</th>
                                                        <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status Pembayaran</th>
                                                        <td>
                                                            <span class="label label-{{ $transaksi->status == 'Lunas' ? 'success' : 'warning' }}">
                                                                {{ $transaksi->status }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status Pengerjaan</th>
                                                        <td>
                                                            @php
                                                                $statusClass = [
                                                                    'Menunggu Pengerjaan' => 'label-warning',
                                                                    'Sedang Dikerjakan' => 'label-info',
                                                                    'Selesai Dikerjakan' => 'label-success',
                                                                    'Sudah Diambil' => 'label-primary'
                                                                ];
                                                            @endphp
                                                            <span class="label {{ $statusClass[$transaksi->status_pengerjaan] ?? 'label-default' }}">
                                                                {{ $transaksi->status_pengerjaan }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tanggal Siap</th>
                                                        <td>{{ $transaksi->tanggal_siap ? \Carbon\Carbon::parse($transaksi->tanggal_siap)->format('d/m/Y') : 'Belum ditentukan' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Waktu Selesai</th>
                                                        <td>{{ $transaksi->waktu_selesai_dikerjakan ? \Carbon\Carbon::parse($transaksi->waktu_selesai_dikerjakan)->format('d/m/Y H:i') : '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Waktu Diambil</th>
                                                        <td>{{ $transaksi->waktu_sudah_diambil ? \Carbon\Carbon::parse($transaksi->waktu_sudah_diambil)->format('d/m/Y H:i') : '-' }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Update Section -->
                                    <div class="mt-3">
                                        <hr>
                                        <h5>Update Status Pengerjaan</h5>
                                        <div class="form-group">
                                            <label for="directStatusSelect">Status Baru:</label>
                                            <select id="directStatusSelect" class="form-control">
                                                <option value="">Pilih Status</option>
                                                <option value="Menunggu Pengerjaan">Menunggu Pengerjaan</option>
                                                <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                                                <option value="Selesai Dikerjakan">Selesai Dikerjakan</option>
                                                <option value="Sudah Diambil">Sudah Diambil</option>
                                            </select>
                                        </div>
                                        <button id="directUpdateStatusBtn" class="btn btn-warning" data-transaksi-id="{{ $transaksi->id }}">Update Status</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($error))
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-danger">
                                <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                {{ $error }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="transaksiDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-info-circle"></i> Detail Transaksi
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="transaksiDetailContent">
                    <!-- Content akan diisi oleh JavaScript -->
                </div>
                
                <!-- Status Update Section -->
                <div id="modalStatusUpdateSection" class="mt-3" style="display: none;">
                    <hr>
                    <h5><i class="fa fa-edit"></i> Update Status Pengerjaan</h5>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="modalStatusSelect">Status Baru:</label>
                                <select id="modalStatusSelect" class="form-control">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="Menunggu Pengerjaan">Menunggu Pengerjaan</option>
                                    <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                                    <option value="Selesai Dikerjakan">Selesai Dikerjakan</option>
                                    <option value="Sudah Diambil">Sudah Diambil</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label><br>
                            <button id="modalUpdateStatusBtn" class="btn btn-warning btn-block">
                                <i class="fa fa-save"></i> Update Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="printTransaksi()" id="printBtn" style="display: none;">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-success" onclick="continueScanning()">
                    <i class="fa fa-qrcode"></i> Scan Lagi
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrcodeScanner = null;
let currentTransaksi = null;
let availableCameras = [];
let currentCameraIndex = 0;
let isScanning = false;

$(document).ready(function() {
    console.log('Document ready, starting scanner initialization');
    
    // Check if SweetAlert is available
    if (typeof Swal === 'undefined') {
        alert('SweetAlert tidak tersedia, beberapa notifikasi mungkin tidak muncul');
    }
    
    // Initialize scanner
    $('#debugStatus').text('Inisialisasi scanner...');
    initializeScanner();
    
    // Form submission
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        searchTransaksi($('#barcodeInput').val());
    });
    
    // Update status
    $('#updateStatusBtn').on('click', function() {
        updateStatus();
    });

    // Direct update status
    $('#directUpdateStatusBtn').on('click', function() {
        const transaksiId = $(this).data('transaksi-id');
        const newStatus = $('#directStatusSelect').val();
        
        if (!newStatus) {
            Swal.fire('Error', 'Pilih status terlebih dahulu', 'error');
            return;
        }
        
        updateStatusDirect(transaksiId, newStatus);
    });
    
    // Modal update status button
    $('#modalUpdateStatusBtn').on('click', function() {
        updateModalStatus();
    });
});

function initializeScanner() {
    console.log('Initializing scanner...');
    $('#debugStatus').text('Memeriksa library scanner...');
    
    // Check if Html5Qrcode is available
    if (typeof Html5Qrcode === 'undefined') {
        console.error('Html5Qrcode library not loaded');
        $('#debugStatus').text('Error: Library scanner tidak dimuat');
        Swal.fire('Error', 'Scanner library tidak dapat dimuat. Silakan refresh halaman.', 'error');
        return;
    }
    
    // Get available cameras and prioritize back camera
    $('#debugStatus').text('Mencari kamera yang tersedia...');
    Html5Qrcode.getCameras().then(cameras => {
        console.log('Camera detection successful:', cameras);
        
        if (cameras && cameras.length) {
            availableCameras = cameras;
            console.log('Available cameras:', cameras);
            $('#debugStatus').text(`Ditemukan ${cameras.length} kamera`);
            
            // Try to find back camera (environment facing)
            let backCameraIndex = cameras.findIndex(camera => 
                camera.label && (
                    camera.label.toLowerCase().includes('back') ||
                    camera.label.toLowerCase().includes('rear') ||
                    camera.label.toLowerCase().includes('environment') ||
                    camera.label.toLowerCase().includes('0')
                )
            );
            
            // If back camera found, use it as default
            if (backCameraIndex !== -1) {
                currentCameraIndex = backCameraIndex;
                console.log('Back camera found at index:', backCameraIndex);
                $('#debugStatus').text(`Siap - Kamera belakang terdeteksi`);
            } else {
                // Fallback to last camera (usually back camera on mobile)
                currentCameraIndex = cameras.length - 1;
                console.log('Using last camera as fallback:', currentCameraIndex);
                $('#debugStatus').text(`Siap - Menggunakan kamera default`);
            }
            
            // Show camera info if multiple cameras available
            if (cameras.length > 1) {
                updateCameraInfo();
                $('#switchCamera').show();
            }
        } else {
            console.log('No cameras found, will use simple scanner');
            $('#debugStatus').text('Siap - Mode auto-detect');
        }
    }).catch(err => {
        console.error('Error getting cameras:', err);
        console.log('Will fallback to simple scanner');
        $('#debugStatus').text('Siap - Mode fallback');
        // Don't show error immediately, let it fallback to simple scanner
    });
    
    $('#startScan').on('click', function() {
        console.log('Start scan clicked');
        startScanning();
    });
    
    $('#stopScan').on('click', function() {
        console.log('Stop scan clicked');
        stopScanning();
    });
    
    $('#switchCamera').on('click', function() {
        console.log('Switch camera clicked');
        switchCamera();
    });
}

function startScanning() {
    // Fallback to simple Html5QrcodeScanner if no cameras detected
    if (availableCameras.length === 0) {
        console.log('No cameras detected, using simple scanner');
        startSimpleScanner();
        return;
    }
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    html5QrcodeScanner = new Html5Qrcode("reader");
    
    // Try to start with specific camera
    const cameraId = availableCameras[currentCameraIndex].id;
    console.log('Starting scanner with camera ID:', cameraId);
    
    html5QrcodeScanner.start(
        // Pass explicit videoConstraints here instead of just cameraId
        { deviceId: { exact: cameraId }, facingMode: { ideal: "environment" } },
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        isScanning = true;
        $('#startScan').hide();
        $('#stopScan').show();
        $('#switchCamera').show();
        $('#cameraInfo').show();
        updateCameraInfo();
        console.log('Scanner started with camera:', availableCameras[currentCameraIndex].label);
    }).catch(err => {
        console.error('Error starting scanner with specific camera:', err);
        // Fallback to simple scanner
        startSimpleScanner();
    });
}

function startSimpleScanner() {
    console.log('Starting simple scanner fallback');
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
        // Try to prioritize back camera
        videoConstraints: {
            facingMode: { ideal: "environment" }
        }
    };
    
    html5QrcodeScanner = new Html5QrcodeScanner("reader", config);
    
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    
    isScanning = true;
    $('#startScan').hide();
    $('#stopScan').show();
    $('#cameraInfo').show();
    $('#cameraLabel').text('Kamera: Auto-detected');
    console.log('Simple scanner started');
}

function stopScanning() {
    if (html5QrcodeScanner && isScanning) {
        // Check if it's Html5Qrcode or Html5QrcodeScanner
        if (typeof html5QrcodeScanner.stop === 'function') {
            // Html5Qrcode method
            html5QrcodeScanner.stop().then(() => {
                cleanup();
            }).catch(err => {
                console.error('Error stopping Html5Qrcode scanner:', err);
                cleanup();
            });
        } else if (typeof html5QrcodeScanner.clear === 'function') {
            // Html5QrcodeScanner method
            try {
                html5QrcodeScanner.clear();
                cleanup();
            } catch (err) {
                console.error('Error clearing Html5QrcodeScanner:', err);
                cleanup();
            }
        } else {
            cleanup();
        }
    }
}

function cleanup() {
    isScanning = false;
    $('#startScan').show();
    $('#stopScan').hide();
    $('#switchCamera').hide();
    $('#cameraInfo').hide();
    html5QrcodeScanner = null;
    console.log('Scanner stopped and cleaned up');
}

function switchCamera() {
    if (!isScanning || availableCameras.length <= 1) {
        return;
    }
    
    // Stop current scanner
    html5QrcodeScanner.stop().then(() => {
        // Switch to next camera
        currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
        console.log('Switching to camera index:', currentCameraIndex);
        
        // Start with new camera
        setTimeout(() => {
            startScanning();
        }, 500); // Small delay to ensure cleanup
    }).catch(err => {
        console.error('Error switching camera:', err);
        Swal.fire('Error', 'Gagal mengganti kamera', 'error');
    });
}

function updateCameraInfo() {
    if (availableCameras.length > 0 && currentCameraIndex < availableCameras.length) {
        const camera = availableCameras[currentCameraIndex];
        let cameraName = camera.label || `Kamera ${currentCameraIndex + 1}`;
        
        // Detect camera type
        if (cameraName.toLowerCase().includes('back') || 
            cameraName.toLowerCase().includes('rear') || 
            cameraName.toLowerCase().includes('environment')) {
            cameraName += ' (Belakang)';
        } else if (cameraName.toLowerCase().includes('front') || 
                   cameraName.toLowerCase().includes('user')) {
            cameraName += ' (Depan)';
        }
        
        $('#cameraLabel').text(`Kamera: ${cameraName}`);
    }
}

function onScanSuccess(decodedText, decodedResult) {
    // Play beep sound
    playBeep();
    
    // Stop scanner
    stopScanning();
    
    console.log('QR Code scanned:', decodedText);
    $('#debugStatus').text('QR Code berhasil discan');
    
    // Check if decodedText is a URL (QR Code contains URL)
    if (decodedText.startsWith('http')) {
        // Extract barcode from URL if it's a scan direct URL
        const urlParts = decodedText.split('/');
        const barcode = urlParts[urlParts.length - 1];
        console.log('Extracted barcode from URL:', barcode);
        
        // Search using the extracted barcode and show in modal
        searchTransaksiForModal(barcode);
    } else {
        // If it's just barcode text, search using AJAX and show in modal
        searchTransaksiForModal(decodedText);
    }
}

function onScanFailure(error) {
    // Handle scan failure silently
}

function playBeep() {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
    audio.play();
}

function searchTransaksi(barcode) {
    if (!barcode) {
        Swal.fire('Error', 'Kode QR code tidak boleh kosong', 'error');
        return;
    }
    
    $.ajax({
        url: '{{ route("barcode.search") }}',
        method: 'POST',
        data: {
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success && response.transaction) {
                displayTransaksi(response.transaction);
            } else {
                Swal.fire('Error', response.message || 'Transaksi tidak ditemukan', 'error');
            }
        },
        error: function(xhr) {
            let message = 'Terjadi kesalahan saat mencari transaksi';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            Swal.fire('Error', message, 'error');
        }
    });
}

function displayTransaksi(transaksi) {
    currentTransaksi = transaksi;
    
    const statusClass = {
        'Menunggu Pengerjaan': 'label-warning',
        'Sedang Dikerjakan': 'label-info',
        'Selesai Dikerjakan': 'label-success',
        'Sudah Diambil': 'label-primary'
    };
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Kode Transaksi</th>
                        <td>${transaksi.kode_penjualan}</td>
                    </tr>
                    <tr>
                        <th>Barcode</th>
                        <td><strong>${transaksi.barcode}</strong></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>${formatDate(transaksi.created_at)}</td>
                    </tr>
                    <tr>
                        <th>Pasien</th>
                        <td>${transaksi.nama_pasien || 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Kasir</th>
                        <td>${transaksi.user ? transaksi.user.name : 'N/A'}</td>
                    </tr>
                    <tr>
                        <th>Cabang</th>
                        <td>${transaksi.branch ? transaksi.branch.name : 'N/A'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Total</th>
                        <td>Rp ${formatNumber(transaksi.total)}</td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td><span class="label label-${transaksi.status == 'Lunas' ? 'success' : 'warning'}">${transaksi.status}</span></td>
                    </tr>
                    <tr>
                        <th>Status Pengerjaan</th>
                        <td><span class="label ${statusClass[transaksi.status_pengerjaan] || 'label-default'}">${transaksi.status_pengerjaan}</span></td>
                    </tr>
                    <tr>
                        <th>Tanggal Siap</th>
                        <td>${transaksi.tanggal_siap ? formatDate(transaksi.tanggal_siap) : 'Belum ditentukan'}</td>
                    </tr>
                    <tr>
                        <th>Waktu Selesai</th>
                        <td>${transaksi.waktu_selesai_dikerjakan ? formatDate(transaksi.waktu_selesai_dikerjakan) : '-'}</td>
                    </tr>
                    <tr>
                        <th>Waktu Diambil</th>
                        <td>${transaksi.waktu_sudah_diambil ? formatDate(transaksi.waktu_sudah_diambil) : '-'}</td>
                    </tr>
                </table>
            </div>
        </div>
    `;
    
    $('#transaksiInfo').html(html);
    $('#resultSection').show();
    $('#statusUpdateSection').show();
    
    // Scroll to result
    $('html, body').animate({
        scrollTop: $('#resultSection').offset().top
    }, 500);
}

function updateStatus() {
    const newStatus = $('#statusSelect').val();
    if (!newStatus) {
        Swal.fire('Error', 'Pilih status terlebih dahulu', 'error');
        return;
    }
    
    if (!currentTransaksi) {
        Swal.fire('Error', 'Tidak ada transaksi yang dipilih', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Update Status',
        text: `Apakah Anda yakin ingin mengubah status menjadi "${newStatus}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Update!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("barcode.update-status") }}',
                method: 'POST',
                data: {
                    transaksi_id: currentTransaksi.id,
                    status_pengerjaan: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        // Refresh transaksi data
                        searchTransaksi(currentTransaksi.barcode);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat update status';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

function updateStatusDirect(transaksiId, newStatus) {
    Swal.fire({
        title: 'Konfirmasi Update Status',
        text: `Apakah Anda yakin ingin mengubah status menjadi "${newStatus}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Update!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("barcode.update-status") }}',
                method: 'POST',
                data: {
                    transaksi_id: transaksiId,
                    status_pengerjaan: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat update status';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ========== MODAL FUNCTIONS ==========

let modalCurrentTransaksi = null;

// Fungsi untuk mencari transaksi dan menampilkan di modal
function searchTransaksiForModal(barcode) {
    if (!barcode) {
        Swal.fire('Error', 'Kode QR code tidak boleh kosong', 'error');
        return;
    }
    
    console.log('Searching transaksi for modal:', barcode);
    $('#debugStatus').text('Mencari data transaksi...');
    
    $.ajax({
        url: '{{ route("barcode.search") }}',
        method: 'POST',
        data: {
            barcode: barcode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            console.log('Search response:', response);
            if (response.success && response.transaction) {
                modalCurrentTransaksi = response.transaction;
                displayTransaksiModal(response.transaction);
                $('#debugStatus').text('Data transaksi ditemukan');
            } else {
                $('#debugStatus').text('Transaksi tidak ditemukan');
                Swal.fire('Error', response.message || 'Transaksi tidak ditemukan', 'error');
            }
        },
        error: function(xhr) {
            console.error('Search error:', xhr);
            $('#debugStatus').text('Error saat mencari transaksi');
            let message = 'Terjadi kesalahan saat mencari transaksi';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            Swal.fire('Error', message, 'error');
        }
    });
}

// Fungsi untuk menampilkan detail transaksi di modal
function displayTransaksiModal(transaksi) {
    console.log('Displaying transaksi in modal:', transaksi);
    
    const statusClass = {
        'Menunggu Pengerjaan': 'label-warning',
        'Sedang Dikerjakan': 'label-info',
        'Selesai Dikerjakan': 'label-success',
        'Sudah Diambil': 'label-primary'
    };
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Kode Transaksi</th>
                        <td><strong>${transaksi.kode_penjualan}</strong></td>
                    </tr>
                    <tr>
                        <th>Barcode</th>
                        <td><code>${transaksi.barcode || '-'}</code></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>${transaksi.tanggal}</td>
                    </tr>
                    <tr>
                        <th>Pasien</th>
                        <td>${transaksi.nama_pasien || 'N/A'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Status Pembayaran</th>
                        <td>
                            <span class="label label-success">
                                Lunas
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status Pengerjaan</th>
                        <td>
                            <span class="label ${statusClass[transaksi.status_pengerjaan] || 'label-default'}">
                                ${transaksi.status_pengerjaan}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Barcode</th>
                        <td>${transaksi.barcode ? '<i class="fa fa-check text-success"></i> Tersedia' : '<i class="fa fa-times text-danger"></i> Belum ada'}</td>
                    </tr>
                    <tr>
                        <th>ID Transaksi</th>
                        <td><small class="text-muted">#${transaksi.id}</small></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> 
            <strong>Transaksi berhasil ditemukan!</strong> 
            Anda dapat mengupdate status pengerjaan jika diperlukan.
        </div>
    `;
    
    $('#transaksiDetailContent').html(html);
    
    // Show modal
    $('#transaksiDetailModal').modal('show');
    
    // Show status update section
    $('#modalStatusUpdateSection').show();
    
    // Set current status in select
    $('#modalStatusSelect').val(transaksi.status_pengerjaan);
    
    // Show print button if transaction exists
    $('#printBtn').show();
}

// Event handlers untuk modal sudah ada di $(document).ready() utama

// Fungsi untuk update status dari modal
function updateModalStatus() {
    const newStatus = $('#modalStatusSelect').val();
    if (!newStatus) {
        Swal.fire('Error', 'Pilih status terlebih dahulu', 'error');
        return;
    }
    
    if (!modalCurrentTransaksi) {
        Swal.fire('Error', 'Tidak ada transaksi yang dipilih', 'error');
        return;
    }
    
    if (newStatus === modalCurrentTransaksi.status_pengerjaan) {
        Swal.fire('Warning', 'Status yang dipilih sama dengan status saat ini', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Update Status',
        html: `
            <p>Update status transaksi <strong>${modalCurrentTransaksi.kode_penjualan}</strong>?</p>
            <p>Dari: <span class="label label-info">${modalCurrentTransaksi.status_pengerjaan}</span></p>
            <p>Ke: <span class="label label-warning">${newStatus}</span></p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Update!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("barcode.update-status") }}',
                method: 'POST',
                data: {
                    transaksi_id: modalCurrentTransaksi.id,
                    status_pengerjaan: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message || 'Status berhasil diupdate', 'success');
                        // Update current transaction status
                        modalCurrentTransaksi.status_pengerjaan = newStatus;
                        // Refresh modal content
                        displayTransaksiModal(modalCurrentTransaksi);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat update status';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// Fungsi untuk print transaksi
function printTransaksi() {
    if (modalCurrentTransaksi) {
        const printUrl = `{{ url('penjualan') }}/${modalCurrentTransaksi.id}/cetak`;
        window.open(printUrl, '_blank');
    }
}

// Fungsi untuk continue scanning
function continueScanning() {
    $('#transaksiDetailModal').modal('hide');
    modalCurrentTransaksi = null;
    $('#debugStatus').text('Siap untuk scan berikutnya');
    
    // Reset form
    $('#modalStatusSelect').val('');
    $('#printBtn').hide();
}
</script>
@endpush 