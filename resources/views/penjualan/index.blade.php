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
        <div class="small-box bg-blue">
            <div class="inner">
                <h3 id="sedang-count">0</h3>
                <p>Sedang Dikerjakan</p>
            </div>
            <div class="icon">
                <i class="fa fa-cogs"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Sedang Dikerjakan')">
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
                <button class="btn btn-sm btn-default" onclick="clearFilter()">
                    <i class="fa fa-refresh"></i> Reset Filter
                </button>
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
@endsection

@push('scripts')
<script>
    let table; // Deklarasikan di sini agar bisa diakses secara global di dalam script
    let currentFilter = '';

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
    });
    
    function updateStatistics() {
        $.ajax({
            url: '{{ route("penjualan.statistics") }}',
            method: 'GET',
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
</script>
@endpush 