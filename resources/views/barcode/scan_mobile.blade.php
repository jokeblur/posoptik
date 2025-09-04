@extends('layouts.master')

@section('title', 'Scan QR Code - Mobile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-qrcode"></i> Scan QR Code
                    </h3>
                </div>
                <div class="box-body">
                    <!-- Mobile Optimized Scanner -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h4 class="box-title">
                                        <i class="fa fa-camera"></i> Scanner QR Code
                                    </h4>
                                </div>
                                <div class="box-body text-center">
                                    <!-- Scanner Container -->
                                    <div id="reader" style="width: 100%; max-width: 100%; margin: 0 auto; min-height: 300px;"></div>
                                    
                                    <!-- Control Buttons -->
                                    <div class="mt-3">
                                        <button id="startScan" class="btn btn-primary btn-lg">
                                            <i class="fa fa-play"></i> Mulai Scan
                                        </button>
                                        <button id="stopScan" class="btn btn-danger btn-lg" style="display: none;">
                                            <i class="fa fa-stop"></i> Stop Scan
                                        </button>
                                        <button id="switchCamera" class="btn btn-info btn-lg" style="display: none;">
                                            <i class="fa fa-refresh"></i> Ganti Kamera
                                        </button>
                                    </div>
                                    
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
        min-height: 250px !important;
        border: 2px dashed #ddd !important;
        border-radius: 8px !important;
        background: #f9f9f9 !important;
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
    background: #000 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    position: relative !important;
}

#reader video {
    width: 100% !important;
    height: auto !important;
    border-radius: 8px !important;
    background: #000 !important;
    object-fit: cover !important;
    display: block !important;
}

#reader canvas {
    width: 100% !important;
    height: auto !important;
    border-radius: 8px !important;
    background: #000 !important;
    display: block !important;
}

/* Fix for white screen issue */
#reader > div {
    background: #000 !important;
    border-radius: 8px !important;
}

#reader > div > div {
    background: #000 !important;
}

/* Loading state for camera */
#reader:empty::before {
    content: 'Memuat Kamera...';
    display: flex;
    align-items: center;
    justify-content: center;
    height: 300px;
    background: #000;
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

// Auto-focus input on mobile
$(document).ready(function() {
    if ($(window).width() <= 768) {
        $('#barcodeInput').focus();
    }
});
</script>
@endpush
