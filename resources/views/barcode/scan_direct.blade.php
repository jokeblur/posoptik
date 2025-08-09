<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Transaksi - {{ $transaksi->kode_penjualan ?? 'Tidak Ditemukan' }}</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .container-fluid {
            padding: 20px;
        }
        .box {
            background: #fff;
            border-radius: 3px;
            border-top: 3px solid #d2d6de;
            box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
            margin-bottom: 20px;
        }
        .box-success {
            border-top-color: #00a65a;
        }
        .box-warning {
            border-top-color: #f39c12;
        }
        .box-danger {
            border-top-color: #dd4b39;
        }
        .box-header {
            padding: 10px;
            border-bottom: 1px solid #f4f4f4;
        }
        .box-title {
            margin: 0;
            font-size: 18px;
        }
        .box-body {
            padding: 15px;
        }
        .label {
            display: inline;
            padding: .2em .6em .3em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25em;
        }
        .label-success { background-color: #5cb85c; }
        .label-warning { background-color: #f0ad4e; }
        .label-info { background-color: #5bc0de; }
        .label-primary { background-color: #337ab7; }
        .label-default { background-color: #777; }
        
        /* Navigation Button Styling */
        .mb-3 { margin-bottom: 15px; }
        .btn { margin-right: 10px; margin-bottom: 5px; }
        .btn i { margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Navigation Button -->
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                    <i class="fa fa-home"></i> Kembali ke Dashboard
                </a>
                <a href="{{ url('/barcode/scan') }}" class="btn btn-info">
                    <i class="fa fa-qrcode"></i> Scan QR Code
                </a>
            </div>
        </div>
        
        @if(isset($error))
        <div class="row">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-ban"></i> Error!</h3>
                    </div>
                    <div class="box-body">
                        {{ $error }}
                        <br><br>
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                            <i class="fa fa-home"></i> Kembali ke Dashboard
                        </a>
                        <a href="{{ url('/barcode/scan') }}" class="btn btn-info">
                            <i class="fa fa-qrcode"></i> Scan QR Code Lain
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($transaksi))
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Data Transaksi</h3>
                    </div>
                    <div class="box-body">
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
                                        <td>{{ $transaksi->pasien->nama_pasien ?? 'N/A' }}</td>
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
                        
                        <!-- Status Update Section -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="box box-warning">
                                    <div class="box-header">
                                        <h4 class="box-title"><i class="fa fa-edit"></i> Update Status Pengerjaan</h4>
                                    </div>
                                    <div class="box-body">
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
                                        <button id="updateStatusBtn" class="btn btn-warning" data-transaksi-id="{{ $transaksi->id }}">
                                            <i class="fa fa-save"></i> Update Status
                                        </button>
                                        
                                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                                            <i class="fa fa-home"></i> Kembali ke Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#updateStatusBtn').on('click', function() {
                const transaksiId = $(this).data('transaksi-id');
                const newStatus = $('#statusSelect').val();
                
                if (!newStatus) {
                    Swal.fire('Error', 'Pilih status terlebih dahulu', 'error');
                    return;
                }
                
                updateStatus(transaksiId, newStatus);
            });
        });

        function updateStatus(transaksiId, newStatus) {
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
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
    </script>
</body>
</html> 