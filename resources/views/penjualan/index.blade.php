@extends('layouts.master')

@section('title', 'Daftar Penjualan')

@section('content')
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h4><i class="icon fa fa-ban"></i> Toko Belum Dibuka!</h4>
    {{ session('error') }}
</div>
@endif
<!-- Info Cards -->
<div class="row">
    <div class="col-lg-2 col-xs-6">
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

    <div class="col-lg-2 col-xs-6">
        <div class="small-box bg-orange">
            <div class="inner">
                <h3 id="lensa-dipesan-count">0</h3>
                <p>Lensa Di Pesan</p>
            </div>
            <div class="icon">
                <i class="fa fa-shopping-basket"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Lensa Di Pesan')">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-2 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3 id="lensa-datang-count">0</h3>
                <p>Lensa Datang</p>
            </div>
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Lensa Datang')">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-2 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3 id="selesai-count">0</h3>
                <p>Sudah Di Kerjakan</p>
            </div>
            <div class="icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Sudah Di Kerjakan')">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-2 col-xs-6">
        <div class="small-box bg-navy">
            <div class="inner">
                <h3 id="kirim-wa-count">0</h3>
                <p>Kirim WA</p>
            </div>
            <div class="icon">
                <i class="fa fa-whatsapp"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Kirim WA')">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-2 col-xs-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3 id="diambil-count">0</h3>
                <p>Sudah Di Ambil</p>
            </div>
            <div class="icon">
                <i class="fa fa-handshake-o"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('Sudah Di Ambil')">
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
                <div class="form-group pull-right" style="margin-bottom: 0; margin-left: 10px;">
                    <select id="jenis_transaksi_filter" class="form-control input-sm">
                        <option value="">Semua Jenis Transaksi</option>
                        <option value="Stock">Stock</option>
                        <option value="Gosok">Gosok</option>
                    </select>
                </div>
                <div class="form-group pull-right" style="margin-bottom: 0; margin-left: 10px;">
                    <select id="tahun_filter" class="form-control input-sm">
                        @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}" {{ (int) date('Y') === $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group pull-right" style="margin-bottom: 0; margin-left: 10px;">
                    <select id="bulan_filter" class="form-control input-sm">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ sprintf('%02d', $i) }}" {{ (int) date('m') === $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
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
                            <th>Nama Pasien</th>
                            <th>Kode Penjualan</th>
                            <th>Nama Dokter</th>
                            <th>Total</th>
                            <th>Passet Oleh</th>
                            <th>Jenis Layanan</th>
                            <th>Jenis Transaksi</th>
                            <th>Status Transaksi</th>
                            <th>Metode Pembayaran</th>
                            <th>Status Pembayaran</th>
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
    let currentBranchId = $('#branch_id_filter').val(); // Ambil nilai awal dari dropdown
    let currentJenisTransaksi = $('#jenis_transaksi_filter').val();
    let currentBulan = $('#bulan_filter').val();
    let currentTahun = $('#tahun_filter').val();

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
                    if (currentJenisTransaksi) {
                        d.jenis_transaksi = currentJenisTransaksi;
                    }
                    d.bulan = currentBulan;
                    d.tahun = currentTahun;
                    // Tambahkan filter cabang
                    if (currentBranchId) {
                        d.branch_id = currentBranchId;
                    }
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'nama_pasien', name: 'nama_pasien' },
                { data: 'kode_penjualan', name: 'kode_penjualan' },
                { data: 'nama_dokter', name: 'nama_dokter' },
                { data: 'total_harga', name: 'total_harga' },
                { data: 'passet_by', name: 'passet_by' },
                { data: 'jenis_layanan', name: 'jenis_layanan' },
                { data: 'jenis_transaksi', name: 'jenis_transaksi' },
                { data: 'status_transaksi', name: 'status_transaksi' },
                { data: 'metode_pembayaran', name: 'metode_pembayaran' },
                { data: 'status_pembayaran', name: 'status_pembayaran' },
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

        $('#jenis_transaksi_filter').on('change', function() {
            currentJenisTransaksi = $(this).val();
            table.ajax.reload();
                updateStatistics();
        });

        $('#bulan_filter, #tahun_filter').on('change', function() {
            currentBulan = $('#bulan_filter').val();
            currentTahun = $('#tahun_filter').val();
            currentFilter = '';
            table.ajax.reload();
            updateStatistics();
            $('.box-title').text('Daftar Penjualan');
        });
    });
    
    function updateStatistics() {
        $.ajax({
            url: '{{ route("penjualan.statistics") }}',
            method: 'GET',
            data: {
                    branch_id: currentBranchId,
                    jenis_transaksi: currentJenisTransaksi,
                    bulan: currentBulan,
                    tahun: currentTahun
            },
            success: function(response) {
                $('#menunggu-count').text(response.menunggu || 0);
                $('#lensa-dipesan-count').text(response.lensa_dipesan || 0);
                $('#lensa-datang-count').text(response.lensa_datang || 0);
                $('#selesai-count').text(response.selesai || 0);
                $('#kirim-wa-count').text(response.kirim_wa || 0);
                $('#diambil-count').text(response.diambil || 0);
            },
            error: function() {
                console.log('Gagal memuat statistik');
            }
        });
    }

    function getStatusLabel(status) {
        if (status === 'Menunggu Pengerjaan') return 'Menunggu Pengerjaan';
        if (status === 'Lensa Di Pesan') return 'Lensa Di Pesan';
        if (status === 'Lensa Datang') return 'Lensa Datang';
        if (status === 'Sedang Mengerjakan') return 'Sedang Mengerjakan';
        if (status === 'Sudah Di Kerjakan') return 'Sudah Di Kerjakan';
        if (status === 'Kirim WA') return 'Kirim WA';
        if (status === 'Sudah Di Ambil') return 'Sudah Di Ambil';
        return status || 'Semua';
    }
    
    function filterByStatus(status) {
        currentFilter = status;
        table.ajax.reload();
        
        // Update judul tabel untuk menunjukkan filter aktif
        $('.box-title').text('Daftar Penjualan - ' + getStatusLabel(status));
    }
    

    function tandaiDiambil(url) {
        Swal.fire({
            title: 'Konfirmasi Pengambilan',
            text: "Anda yakin barang sudah diambil oleh pelanggan?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, sudah di ambil!',
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

    function updateStatusPengerjaan(id) {
        Swal.fire({
            title: 'Update Status Pengerjaan',
            html: `
                <div class="form-group text-left">
                    <label for="status_select">Pilih Status Baru:</label>
                    <select id="status_select" class="form-control">
                        <option value="">-- Pilih Status --</option>
                        <option value="Menunggu Pengerjaan">Menunggu Pengerjaan</option>
                        <option value="Lensa Di Pesan">Lensa Di Pesan</option>
                        <option value="Lensa Datang">Lensa Datang</option>
                        <option value="Sedang Mengerjakan">Sedang Mengerjakan</option>
                        <option value="Sudah Di Kerjakan">Sudah Di Kerjakan</option>
                        <option value="Kirim WA">Kirim WA</option>
                        <option value="Sudah Di Ambil">Sudah Di Ambil</option>
                    </select>
                </div>
                <div class="form-group text-left" id="nohp_group" style="display:none; margin-top:10px;">
                    <label for="nohp_input">No HP Pasien (isi jika belum ada):</label>
                    <input type="text" id="nohp_input" class="form-control" placeholder="Contoh: 081234567890">
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            didOpen: () => {
                // Focus ke select
                const statusSelect = document.getElementById('status_select');
                const nohpGroup = document.getElementById('nohp_group');

                statusSelect.focus();
                statusSelect.addEventListener('change', function () {
                    if (this.value === 'Kirim WA') {
                        nohpGroup.style.display = 'block';
                    } else {
                        nohpGroup.style.display = 'none';
                    }
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const status = document.getElementById('status_select').value;
                const nohp = (document.getElementById('nohp_input')?.value || '').trim();
                
                if (!status) {
                    Swal.fire('Peringatan!', 'Pilih status terlebih dahulu', 'warning');
                    return;
                }

                // Update status dengan AJAX
                $.ajax({
                    url: '{{ route("penjualan.update_status_pengerjaan", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'status_pengerjaan': status,
                        'nohp': nohp
                    },
                    success: function(response) {
                        const wa = response.whatsapp || null;
                        let successMessage = response.message;

                        if (wa && wa.message) {
                            successMessage += '\n\n' + wa.message;
                        }

                        if (wa && wa.open_link && wa.link) {
                            window.open(wa.link, '_blank');
                        }

                        Swal.fire('Berhasil!', successMessage, 'success')
                            .then(() => {
                                table.ajax.reload(); // Reload tabel
                            });
                    },
                    error: function(xhr) {
                        let message = 'Tidak dapat mengubah status.';
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