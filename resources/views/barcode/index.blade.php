@extends('layouts.master')

@section('title')
    Manajemen Barcode
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Barcode</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Manajemen Barcode Transaksi</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-warning btn-sm" id="bulk-generate-btn">
                        <i class="fa fa-qrcode"></i> Generate Barcode Massal
                    </button>
                    <button type="button" class="btn btn-info btn-sm" onclick="window.location.href='{{ route('barcode.scan') }}'">
                        <i class="fa fa-search"></i> Scan Barcode
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h4><i class="icon fa fa-info"></i> Informasi!</h4>
                            <p>Halaman ini digunakan untuk mengelola barcode transaksi:</p>
                            <ul>
                                <li><strong>Generate Barcode Massal:</strong> Membuat barcode untuk semua transaksi yang belum memiliki barcode</li>
                                <li><strong>Scan Barcode:</strong> Halaman untuk melakukan scan barcode dan melihat detail transaksi</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3 id="total-transaksi">0</h3>
                                <p>Total Transaksi</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3 id="total-barcode">0</h3>
                                <p>Transaksi dengan Barcode</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-qrcode"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Daftar Transaksi</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table class="table table-bordered table-striped" id="transaksi-table">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Kode Transaksi</th>
                                            <th>Tanggal</th>
                                            <th>Pasien</th>
                                            <th>Status</th>
                                            <th>Barcode</th>
                                            <th width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data akan diload via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load statistics
    loadStatistics();
    
    // Initialize DataTable
    $('#transaksi-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("penjualan.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kode_penjualan', name: 'kode_penjualan' },
            { data: 'tanggal', name: 'tanggal' },
            { data: 'nama_pasien', name: 'nama_pasien' },
            { data: 'status_pengerjaan', name: 'status_pengerjaan' },
            { 
                data: 'barcode', 
                name: 'barcode',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (data) {
                        return '<span class="label label-success"><i class="fa fa-check"></i> ' + data + '</span>';
                    } else {
                        return '<span class="label label-warning"><i class="fa fa-times"></i> Belum ada</span>';
                    }
                }
            },
            {
                data: 'id',
                name: 'aksi',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let buttons = '';
                    
                    if (row.barcode) {
                        buttons += '<button class="btn btn-info btn-xs" onclick="printBarcode(' + data + ')"><i class="fa fa-print"></i></button> ';
                        buttons += '<button class="btn btn-primary btn-xs" onclick="scanBarcode(\'' + row.barcode + '\')"><i class="fa fa-search"></i></button>';
                    } else {
                        buttons += '<button class="btn btn-warning btn-xs" onclick="generateBarcode(' + data + ')"><i class="fa fa-qrcode"></i></button>';
                    }
                    
                    return buttons;
                }
            }
        ],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
        }
    });
    
    // Bulk generate barcode
    $('#bulk-generate-btn').on('click', function() {
        if (confirm('Apakah Anda yakin ingin generate barcode untuk semua transaksi yang belum memiliki barcode?')) {
            $.ajax({
                url: '{{ route("barcode.bulk-generate") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#transaksi-table').DataTable().ajax.reload();
                        loadStatistics();
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
                }
            });
        }
    });
});

function loadStatistics() {
    // Load total transaksi dan barcode statistics
    // Ini bisa ditambahkan route khusus untuk statistik jika diperlukan
    $('#total-transaksi').text('{{ \App\Models\Penjualan::count() }}');
    $('#total-barcode').text('{{ \App\Models\Penjualan::whereNotNull("barcode")->count() }}');
}

function generateBarcode(transaksiId) {
    $.ajax({
        url: '{{ route("barcode.generate") }}',
        type: 'POST',
        data: {
            transaksi_id: transaksiId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert('Barcode berhasil dibuat: ' + response.barcode);
                $('#transaksi-table').DataTable().ajax.reload();
                loadStatistics();
            }
        },
        error: function(xhr) {
            alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
        }
    });
}

function printBarcode(transaksiId) {
    window.open('{{ route("barcode.print", ":id") }}'.replace(':id', transaksiId), '_blank');
}

function scanBarcode(barcode) {
    window.open('{{ route("barcode.scan.direct", ":barcode") }}'.replace(':barcode', barcode), '_blank');
}
</script>
@endpush
