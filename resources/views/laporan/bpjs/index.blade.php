@extends('layouts.master')

@section('title', 'Laporan Transaksi BPJS')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Laporan Transaksi BPJS</h3>
            </div>
            <div class="box-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Cabang:</label>
                        <select id="branch_id" class="form-control">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal Mulai:</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal Akhir:</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Jenis Transaksi:</label>
                        <select id="transaction_type" class="form-control">
                            <option value="">Semua Transaksi</option>
                            <option value="bpjs_normal">BPJS Normal</option>
                            <option value="bpjs_naik_kelas">BPJS Naik Kelas</option>
                            <option value="all_bpjs">Semua BPJS</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label><br>
                        <button onclick="applyFilter()" class="btn btn-primary">Filter</button>
                        <button onclick="exportData()" class="btn btn-success">Export</button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="small-box bg-blue">
                            <div class="inner">
                                <h3 id="total_transaksi">0</h3>
                                <p>Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3 id="bpjs_normal_count">0</h3>
                                <p>BPJS Normal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3 id="bpjs_naik_kelas_count">0</h3>
                                <p>BPJS Naik Kelas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3 id="total_default_bpjs_card">Rp 0</h3>
                                <p>Total Default BPJS</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Table -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Jenis Transaksi</th>
                                        <th>Jumlah</th>
                                        <th>Total Nilai Default BPJS</th>
                                        <th>Total Default BPJS</th>
                                        <th>Total Biaya Tambahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>BPJS Normal</strong></td>
                                        <td id="bpjs_normal_count_table">0</td>
                                        <td id="bpjs_normal_total">Rp 0</td>
                                        <td id="bpjs_normal_default_total">Rp 0</td>
                                        <td>-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>BPJS Naik Kelas</strong></td>
                                        <td id="bpjs_naik_kelas_count_table">0</td>
                                        <td id="bpjs_naik_kelas_total">Rp 0</td>
                                        <td id="bpjs_naik_kelas_default_total">Rp 0</td>
                                        <td id="bpjs_naik_kelas_additional_total">Rp 0</td>
                                    </tr>
                                    <tr class="bg-info">
                                        <td><strong>TOTAL</strong></td>
                                        <td id="total_transaksi_table">0</td>
                                        <td id="total_pendapatan">Rp 0</td>
                                        <td id="total_default_bpjs">Rp 0</td>
                                        <td id="total_additional_cost">Rp 0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="bpjs-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal</th>
                                <th>Kode Penjualan</th>
                                <th>Nama Pasien</th>
                                <th>Jenis Layanan</th>
                                <th>Status Transaksi</th>
                                <th>Nilai Default BPJS</th>
                                <th>Default BPJS</th>
                                <th>Biaya Tambahan</th>
                                <th>Kasir</th>
                                <th>Cabang</th>
                                <th width="10%">Aksi</th>
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
</div>
@endsection

@push('scripts')
<script>
    let table;

    $(function () {
        table = $('#bpjs-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'desc']], // Order by tanggal column descending
            ajax: {
                url: '{{ route("laporan.bpjs.data") }}',
                data: function(d) {
                    d.branch_id = $('#branch_id').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.transaction_type = $('#transaction_type').val();
                },
                error: function(xhr, error, thrown) {
                    console.log('DataTables Error:', error);
                    console.log('Response:', xhr.responseText);
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'kode_penjualan', name: 'kode_penjualan' },
                { data: 'nama_pasien', name: 'nama_pasien' },
                { data: 'jenis_layanan', name: 'pasien_service_type' },
                { data: 'status_transaksi', name: 'transaction_status' },
                { data: 'total_harga', name: 'total' },
                { data: 'harga_default_bpjs', name: 'bpjs_default_price' },
                { data: 'biaya_tambahan', name: 'total_additional_cost' },
                { data: 'kasir', name: 'kasir' },
                { data: 'cabang', name: 'cabang' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ]
        });

        // Load summary on page load
        loadSummary();
    });

    function applyFilter() {
        table.ajax.reload();
        loadSummary();
    }

    function loadSummary() {
        $.get('{{ route("laporan.bpjs.summary") }}', {
            branch_id: $('#branch_id').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            transaction_type: $('#transaction_type').val()
        })
        .done(function(data) {
            $('#total_transaksi').text(data.total_transaksi);
            $('#bpjs_normal_count').text(data.bpjs_normal_count);
            $('#bpjs_naik_kelas_count').text(data.bpjs_naik_kelas_count);

            $('#total_transaksi_table').text(data.total_transaksi);
            $('#bpjs_normal_count_table').text(data.bpjs_normal_count);
            $('#bpjs_naik_kelas_count_table').text(data.bpjs_naik_kelas_count);

            $('#total_pendapatan').text('Rp ' + formatNumber(data.total_pendapatan));
            $('#bpjs_normal_total').text('Rp ' + formatNumber(data.bpjs_normal_total));
            $('#bpjs_naik_kelas_total').text('Rp ' + formatNumber(data.bpjs_naik_kelas_total));

            $('#bpjs_normal_default_total').text('Rp ' + formatNumber(data.bpjs_normal_default_total));
            $('#bpjs_naik_kelas_default_total').text('Rp ' + formatNumber(data.bpjs_naik_kelas_default_total));
            $('#bpjs_naik_kelas_additional_total').text('Rp ' + formatNumber(data.bpjs_naik_kelas_additional_total));

            const totalDefaultBpjs = (data.bpjs_normal_default_total || 0) + (data.bpjs_naik_kelas_default_total || 0);
            $('#total_default_bpjs').text('Rp ' + formatNumber(totalDefaultBpjs));
            $('#total_default_bpjs_card').text('Rp ' + formatNumber(totalDefaultBpjs));
            $('#total_additional_cost').text('Rp ' + formatNumber(data.bpjs_naik_kelas_additional_total));
        })
        .fail(function(xhr, status, error) {
            console.log('Summary Error:', error);
            console.log('Response:', xhr.responseText);
        });
    }

    function exportData() {
        let params = new URLSearchParams({
            branch_id: $('#branch_id').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            transaction_type: $('#transaction_type').val()
        });

        window.open('{{ route("laporan.bpjs.export") }}?' + params.toString(), '_blank');
    }

    function hapusTransaksi(url) {
        Swal.fire({
            title: 'Konfirmasi Penghapusan',
            text: 'Anda yakin ingin menghapus transaksi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: url,
                type: 'DELETE',
                data: { '_token': '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.fire('Berhasil!', response.message, 'success');
                    table.ajax.reload();
                    loadSummary();
                },
                error: function(xhr) {
                    let message = 'Tidak dapat menghapus transaksi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', message, 'error');
                }
            });
        });
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>
@endpush 