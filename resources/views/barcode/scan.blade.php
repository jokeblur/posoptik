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
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Manual Input Section -->
                        <div class="col-md-6">
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
                        </div>
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
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrcodeScanner = null;
let currentTransaksi = null;

$(document).ready(function() {
    // Initialize scanner
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
});

function initializeScanner() {
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    html5QrcodeScanner = new Html5QrcodeScanner("reader", config);
    
    $('#startScan').on('click', function() {
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        $('#startScan').hide();
        $('#stopScan').show();
    });
    
    $('#stopScan').on('click', function() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
        $('#startScan').show();
        $('#stopScan').hide();
    });
}

function onScanSuccess(decodedText, decodedResult) {
    // Play beep sound
    playBeep();
    
    // Stop scanner
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }
    $('#startScan').show();
    $('#stopScan').hide();
    
    // Check if decodedText is a URL (QR Code contains URL)
    if (decodedText.startsWith('http')) {
        // If it's a URL, redirect to that URL
        window.location.href = decodedText;
    } else {
        // If it's just barcode text, search using AJAX
        searchTransaksi(decodedText);
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
            if (response.success) {
                displayTransaksi(response.data);
            } else {
                Swal.fire('Error', response.message, 'error');
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
</script>
@endpush 