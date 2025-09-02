@extends('layouts.master')

@section('title', 'Daftar Penjualan')

@section('content')
<!-- Info Cards -->
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3 id="menunggu-count">0</h3>
                <p>Menunggu Pengerjaan</p>
            </div>
            <div class="icon">
                <i class="fa fa-clock-o"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Menunggu Pengerjaan')">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3 id="selesai-count">0</h3>
                <p>Selesai Dikerjakan</p>
            </div>
            <div class="icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Selesai Dikerjakan')">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="diambil-count">0</h3>
                <p>Sudah Diambil</p>
            </div>
            <div class="icon">
                <i class="fa fa-handshake-o"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Sudah Diambil')">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="{{ route('penjualan.create') }}" class="btn btn-sm btn-custom">Tambah Penjualan Baru</a>
                <button class="btn btn-sm btn-success" onclick="openBarcodeScanner()">
                    <i class="fa fa-qrcode"></i> Scan Barcode
                </button>
                <button class="btn btn-sm btn-warning" onclick="openStatusUpdateScanner()">
                    <i class="fa fa-edit"></i> Update Status
                </button>
                <button class="btn btn-sm btn-default" onclick="clearFilter()">
                    <i class="fa fa-refresh"></i> Reset Filter
                </button>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <div class="form-group pull-right" style="margin-bottom: 0;">
                    <select id="branch_id_filter" class="form-control input-sm">
                        <option value="">Tampilkan Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branch->id == $selectedBranchId ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="penjualan-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Kode Penjualan</th>
                            <th>Total</th>
                            <th>Kasir</th>
                            <th>Cabang</th>
                            <th>Nama Pasien</th>
                            <th>Nama Dokter</th>
                            <th>Passet Oleh</th>
                            <th>Status Transaksi</th>
                            <th>Status Pengerjaan</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data akan diisi oleh DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Scan Barcode -->
<div class="modal fade" id="scanBarcodeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-qrcode"></i> Scan Barcode Transaksi
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="barcodeInput">Masukkan Kode Barcode:</label>
                    <input type="text" 
                           id="barcodeInput" 
                           class="form-control" 
                           placeholder="Scan atau ketik kode barcode..." 
                           autofocus>
                    <small class="help-block">
                        <i class="fa fa-info-circle"></i> 
                        Arahkan scanner ke input ini atau ketik manual kode barcode
                    </small>
                </div>
                
                <div id="scanResult" class="alert" style="display: none;">
                    <!-- Hasil scan akan ditampilkan di sini -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Tutup
                </button>
                <button type="button" class="btn btn-success" onclick="processBarcodeSearch()">
                    <i class="fa fa-search"></i> Cari Transaksi
                </button>
                <button type="button" class="btn btn-primary" onclick="markAsPickedUp()" id="markPickedBtn" style="display: none;">
                    <i class="fa fa-check"></i> Tandai Sudah Diambil
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Status Pengerjaan -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-edit"></i> Update Status Pengerjaan
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="statusQrInput">Scan QR Code Transaksi:</label>
                    <input type="text" 
                           id="statusQrInput" 
                           class="form-control" 
                           placeholder="Scan QR code atau ketik kode transaksi..." 
                           autofocus>
                    <small class="help-block">
                        <i class="fa fa-info-circle"></i> 
                        Arahkan scanner QR ke input ini atau ketik manual kode transaksi
                    </small>
                </div>
                
                <div id="statusUpdateResult" class="alert" style="display: none;">
                    <!-- Hasil scan dan form update status akan ditampilkan di sini -->
                </div>

                <div id="statusUpdateForm" style="display: none;">
                    <div class="form-group">
                        <label for="newStatus">Pilih Status Baru:</label>
                        <select id="newStatus" class="form-control">
                            <option value="">-- Pilih Status --</option>
                            <option value="Menunggu Pengerjaan">Menunggu Pengerjaan</option>
                            <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                            <option value="Selesai Dikerjakan">Selesai Dikerjakan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="statusNote">Catatan (Opsional):</label>
                        <textarea id="statusNote" class="form-control" rows="3" 
                                  placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Tutup
                </button>
                <button type="button" class="btn btn-success" onclick="processStatusQrSearch()">
                    <i class="fa fa-search"></i> Cari Transaksi
                </button>
                <button type="button" class="btn btn-primary" onclick="updateTransactionStatus()" id="updateStatusBtn" style="display: none;">
                    <i class="fa fa-save"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let table; // Deklarasikan di sini agar bisa diakses secara global di dalam script
    let currentFilter = '';
    let currentBranchId = $('#branch_id_filter').val(); // Ambil nilai awal dari dropdown

    $(function () {
        table = $('#penjualan-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('penjualan.data') }}',
                data: function(d) {
                    if (currentFilter) {
                        d.status_filter = currentFilter;
                    }
                    // Tambahkan filter cabang
                    if (currentBranchId) {
                        d.branch_id = currentBranchId;
                    }
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'kode_penjualan', name: 'kode_penjualan' },
                { data: 'total_harga', name: 'total_harga' },
                { data: 'kasir', name: 'kasir' },
                { data: 'cabang', name: 'cabang' },
                { data: 'nama_pasien', name: 'nama_pasien' },
                { data: 'nama_dokter', name: 'nama_dokter' },
                { data: 'passet_by', name: 'passet_by' },
                { data: 'status_transaksi', name: 'status_transaksi' },
                { data: 'status_pengerjaan', name: 'status_pengerjaan' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ],
            drawCallback: function() {
                // Update statistik setelah data table di-render
                updateStatistics();
            }
        });
        
        // Load statistik saat halaman pertama kali dimuat
        updateStatistics();

        // Event listener untuk perubahan dropdown cabang
        $('#branch_id_filter').on('change', function() {
            currentBranchId = $(this).val();
            table.ajax.reload();
            updateStatistics(); // Juga update statistik saat cabang berubah
        });
    });
    
    function updateStatistics() {
        $.ajax({
            url: '{{ route("penjualan.statistics") }}',
            method: 'GET',
            data: {
                branch_id: currentBranchId // Kirim branch_id ke endpoint statistik
            },
            success: function(response) {
                $('#menunggu-count').text(response.menunggu || 0);
                $('#sedang-count').text(response.sedang || 0);
                $('#selesai-count').text(response.selesai || 0);
                $('#diambil-count').text(response.diambil || 0);
            },
            error: function() {
                console.log('Gagal memuat statistik');
            }
        });
    }
    
    function filterByStatus(status) {
        currentFilter = status;
        table.ajax.reload();
        
        // Update judul tabel untuk menunjukkan filter aktif
        $('.box-title').text('Daftar Penjualan - ' + status);
    }
    
    function clearFilter() {
        currentFilter = '';
        table.ajax.reload();
        $('.box-title').text('Daftar Penjualan');
    }

    function tandaiDiambil(url) {
        Swal.fire({
            title: 'Konfirmasi Pengambilan',
            text: "Anda yakin barang sudah diambil oleh pelanggan?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, sudah diambil!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, { '_token': '{{ csrf_token() }}' })
                    .done(response => {
                        Swal.fire('Berhasil!', response.message, 'success')
                            .then(() => {
                                table.ajax.reload(); // Reload setelah user menutup alert
                            });
                    })
                    .fail(errors => {
                        let message = 'Tidak dapat mengubah status.';
                        if (errors.responseJSON && errors.responseJSON.message) {
                            message = errors.responseJSON.message;
                        }
                        Swal.fire('Gagal!', message, 'error');
                    });
            }
        });
    }

    function hapusTransaksi(url) {
        Swal.fire({
            title: 'Konfirmasi Penghapusan',
            text: "Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { '_token': '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire('Berhasil!', response.message, 'success')
                            .then(() => {
                                table.ajax.reload(); // Reload setelah user menutup alert
                            });
                    },
                    error: function(xhr) {
                        let message = 'Tidak dapat menghapus transaksi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', message, 'error');
                    }
                });
            }
        });
    }

    // Variabel global untuk menyimpan data transaksi yang ditemukan
    let foundTransaction = null;

    // Fungsi untuk membuka modal scan barcode
    function openBarcodeScanner() {
        $('#scanBarcodeModal').modal('show');
        $('#barcodeInput').focus();
        
        // Reset modal state
        $('#scanResult').hide();
        $('#markPickedBtn').hide();
        $('#barcodeInput').val('');
        foundTransaction = null;
    }

    // Event listener untuk Enter key pada input barcode
    $(document).ready(function() {
        $('#barcodeInput').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                processBarcodeSearch();
            }
        });
    });

    // Fungsi untuk mencari transaksi berdasarkan barcode
    function processBarcodeSearch() {
        const barcode = $('#barcodeInput').val().trim();
        
        if (!barcode) {
            Swal.fire('Peringatan!', 'Silakan masukkan kode barcode terlebih dahulu.', 'warning');
            return;
        }

        // Tampilkan loading
        $('#scanResult').removeClass('alert-success alert-danger alert-warning')
                       .addClass('alert-info')
                       .html('<i class="fa fa-spinner fa-spin"></i> Mencari transaksi...')
                       .show();

        // AJAX request untuk mencari transaksi
        $.ajax({
            url: '{{ route("barcode.search") }}',
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'barcode': barcode
            },
            success: function(response) {
                if (response.success && response.transaction) {
                    foundTransaction = response.transaction;
                    displayTransactionInfo(response.transaction);
                } else {
                    displayNotFound();
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat mencari transaksi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#scanResult').removeClass('alert-info alert-success alert-warning')
                               .addClass('alert-danger')
                               .html('<i class="fa fa-times-circle"></i> ' + message);
            }
        });
    }

    // Fungsi untuk menampilkan informasi transaksi yang ditemukan
    function displayTransactionInfo(transaction) {
        let statusColor = 'info';
        let canMarkPickedUp = false;

        if (transaction.status_pengerjaan === 'Selesai Dikerjakan') {
            statusColor = 'success';
            canMarkPickedUp = true;
        } else if (transaction.status_pengerjaan === 'Sudah Diambil') {
            statusColor = 'primary';
        } else {
            statusColor = 'warning';
        }

        const html = `
            <i class="fa fa-check-circle"></i> <strong>Transaksi Ditemukan!</strong><br>
            <div style="margin-top: 10px;">
                <strong>Kode:</strong> ${transaction.kode_penjualan}<br>
                <strong>Tanggal:</strong> ${transaction.tanggal}<br>
                <strong>Pasien:</strong> ${transaction.nama_pasien || '-'}<br>
                <strong>Status:</strong> <span class="label label-${statusColor}">${transaction.status_pengerjaan}</span>
            </div>
        `;

        $('#scanResult').removeClass('alert-info alert-danger alert-warning')
                       .addClass('alert-success')
                       .html(html);

        if (canMarkPickedUp) {
            $('#markPickedBtn').show();
        } else {
            $('#markPickedBtn').hide();
            
            if (transaction.status_pengerjaan === 'Sudah Diambil') {
                $('#scanResult').append('<br><small class="text-muted"><i class="fa fa-info-circle"></i> Transaksi ini sudah ditandai sebagai diambil.</small>');
            } else {
                $('#scanResult').append('<br><small class="text-muted"><i class="fa fa-info-circle"></i> Transaksi belum selesai dikerjakan.</small>');
            }
        }
    }

    // Fungsi untuk menampilkan pesan tidak ditemukan
    function displayNotFound() {
        $('#scanResult').removeClass('alert-info alert-success alert-warning')
                       .addClass('alert-danger')
                       .html('<i class="fa fa-times-circle"></i> <strong>Transaksi tidak ditemukan!</strong><br>Periksa kembali kode barcode yang dimasukkan.');
        $('#markPickedBtn').hide();
    }

    // Fungsi untuk menandai transaksi sebagai sudah diambil
    function markAsPickedUp() {
        if (!foundTransaction) {
            Swal.fire('Error!', 'Tidak ada transaksi yang dipilih.', 'error');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Pengambilan',
            text: `Tandai transaksi ${foundTransaction.kode_penjualan} sebagai sudah diambil?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, sudah diambil!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`{{ url('penjualan') }}/${foundTransaction.id}/diambil`, { 
                    '_token': '{{ csrf_token() }}' 
                })
                .done(response => {
                    Swal.fire('Berhasil!', response.message, 'success')
                        .then(() => {
                            $('#scanBarcodeModal').modal('hide');
                            table.ajax.reload(); // Reload table
                            updateStatistics(); // Update statistics
                        });
                })
                .fail(errors => {
                    let message = 'Tidak dapat mengubah status.';
                    if (errors.responseJSON && errors.responseJSON.message) {
                        message = errors.responseJSON.message;
                    }
                    Swal.fire('Gagal!', message, 'error');
                });
            }
        });
    }

    // ========== STATUS UPDATE FUNCTIONS ==========
    
    // Variabel global untuk menyimpan data transaksi untuk update status
    let statusUpdateTransaction = null;

    // Fungsi untuk membuka modal update status
    function openStatusUpdateScanner() {
        $('#statusUpdateModal').modal('show');
        $('#statusQrInput').focus();
        
        // Reset modal state
        $('#statusUpdateResult').hide();
        $('#statusUpdateForm').hide();
        $('#updateStatusBtn').hide();
        $('#statusQrInput').val('');
        $('#newStatus').val('');
        $('#statusNote').val('');
        statusUpdateTransaction = null;
    }

    // Event listener untuk Enter key pada input QR status
    $(document).ready(function() {
        $('#statusQrInput').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                processStatusQrSearch();
            }
        });
    });

    // Fungsi untuk mencari transaksi berdasarkan QR code untuk update status
    function processStatusQrSearch() {
        const qrCode = $('#statusQrInput').val().trim();
        
        if (!qrCode) {
            Swal.fire('Peringatan!', 'Silakan masukkan QR code transaksi terlebih dahulu.', 'warning');
            return;
        }

        // Tampilkan loading
        $('#statusUpdateResult').removeClass('alert-success alert-danger alert-warning')
                                .addClass('alert-info')
                                .html('<i class="fa fa-spinner fa-spin"></i> Mencari transaksi...')
                                .show();

        // AJAX request untuk mencari transaksi berdasarkan kode_penjualan atau barcode
        $.ajax({
            url: '{{ route("barcode.search") }}',
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'barcode': qrCode
            },
            success: function(response) {
                if (response.success && response.transaction) {
                    statusUpdateTransaction = response.transaction;
                    displayStatusUpdateInfo(response.transaction);
                } else {
                    // Coba cari berdasarkan kode_penjualan jika tidak ditemukan berdasarkan barcode
                    searchByKodePenjualan(qrCode);
                }
            },
            error: function(xhr) {
                // Coba cari berdasarkan kode_penjualan
                searchByKodePenjualan(qrCode);
            }
        });
    }

    // Fungsi alternatif untuk mencari berdasarkan kode_penjualan
    function searchByKodePenjualan(kode) {
        $.ajax({
            url: '{{ route("penjualan.data") }}',
            method: 'GET',
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    const transaction = response.data.find(t => t.kode_penjualan && t.kode_penjualan.includes(kode));
                    if (transaction) {
                        statusUpdateTransaction = {
                            id: transaction.id || transaction.DT_RowIndex,
                            kode_penjualan: transaction.kode_penjualan,
                            tanggal: transaction.tanggal,
                            nama_pasien: transaction.nama_pasien,
                            status_pengerjaan: transaction.status_pengerjaan
                        };
                        displayStatusUpdateInfo(statusUpdateTransaction);
                    } else {
                        displayStatusNotFound();
                    }
                } else {
                    displayStatusNotFound();
                }
            },
            error: function() {
                displayStatusNotFound();
            }
        });
    }

    // Fungsi untuk menampilkan informasi transaksi untuk update status
    function displayStatusUpdateInfo(transaction) {
        let statusColor = getStatusColor(transaction.status_pengerjaan);

        const html = `
            <i class="fa fa-check-circle"></i> <strong>Transaksi Ditemukan!</strong><br>
            <div style="margin-top: 10px;">
                <strong>Kode:</strong> ${transaction.kode_penjualan}<br>
                <strong>Tanggal:</strong> ${transaction.tanggal}<br>
                <strong>Pasien:</strong> ${transaction.nama_pasien || '-'}<br>
                <strong>Status Saat Ini:</strong> <span class="label label-${statusColor}">${transaction.status_pengerjaan}</span>
            </div>
        `;

        $('#statusUpdateResult').removeClass('alert-info alert-danger alert-warning')
                               .addClass('alert-success')
                               .html(html);

        // Tampilkan form update status
        $('#statusUpdateForm').show();
        $('#updateStatusBtn').show();
        
        // Set current status sebagai default, tapi biarkan user memilih
        $('#newStatus').val(transaction.status_pengerjaan);
    }

    // Fungsi untuk menampilkan pesan transaksi tidak ditemukan untuk update status
    function displayStatusNotFound() {
        $('#statusUpdateResult').removeClass('alert-info alert-success alert-warning')
                               .addClass('alert-danger')
                               .html('<i class="fa fa-times-circle"></i> <strong>Transaksi tidak ditemukan!</strong><br>Periksa kembali QR code atau kode transaksi yang dimasukkan.');
        $('#statusUpdateForm').hide();
        $('#updateStatusBtn').hide();
    }

    // Fungsi helper untuk mendapatkan warna status
    function getStatusColor(status) {
        switch (status) {
            case 'Menunggu Pengerjaan':
                return 'warning';
            case 'Sedang Dikerjakan':
                return 'info';
            case 'Selesai Dikerjakan':
                return 'success';
            case 'Sudah Diambil':
                return 'primary';
            default:
                return 'default';
        }
    }

    // Fungsi untuk update status transaksi
    function updateTransactionStatus() {
        if (!statusUpdateTransaction) {
            Swal.fire('Error!', 'Tidak ada transaksi yang dipilih.', 'error');
            return;
        }

        const newStatus = $('#newStatus').val();
        const note = $('#statusNote').val();

        if (!newStatus) {
            Swal.fire('Peringatan!', 'Silakan pilih status baru terlebih dahulu.', 'warning');
            return;
        }

        if (newStatus === statusUpdateTransaction.status_pengerjaan) {
            Swal.fire('Peringatan!', 'Status yang dipilih sama dengan status saat ini.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Update Status',
            html: `
                <p>Update status transaksi <strong>${statusUpdateTransaction.kode_penjualan}</strong>?</p>
                <p>Dari: <span class="label label-${getStatusColor(statusUpdateTransaction.status_pengerjaan)}">${statusUpdateTransaction.status_pengerjaan}</span></p>
                <p>Ke: <span class="label label-${getStatusColor(newStatus)}">${newStatus}</span></p>
                ${note ? '<p><strong>Catatan:</strong> ' + note + '</p>' : ''}
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("barcode.update-status") }}', { 
                    '_token': '{{ csrf_token() }}',
                    'transaksi_id': statusUpdateTransaction.id,
                    'status_pengerjaan': newStatus,
                    'note': note
                })
                .done(response => {
                    Swal.fire('Berhasil!', response.message || 'Status berhasil diupdate.', 'success')
                        .then(() => {
                            $('#statusUpdateModal').modal('hide');
                            table.ajax.reload(); // Reload table
                            updateStatistics(); // Update statistics
                        });
                })
                .fail(errors => {
                    let message = 'Tidak dapat mengupdate status.';
                    if (errors.responseJSON && errors.responseJSON.message) {
                        message = errors.responseJSON.message;
                    }
                    Swal.fire('Gagal!', message, 'error');
                });
            }
        });
    }
</script>
@endpush 