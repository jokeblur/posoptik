@extends('layouts.master')

@section('title', 'Laporan Tanda Tangan BPJS')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Laporan Tanda Tangan Pasien BPJS</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="start_date">Tanggal Mulai:</label>
                        <input type="date" id="start_date" class="form-control" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">Tanggal Akhir:</label>
                        <input type="date" id="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="branch_filter">Cabang:</label>
                        <select id="branch_filter" class="form-control">
                            <option value="">Semua Cabang</option>
                            @foreach(\App\Models\Branch::all() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="service_type_filter">Jenis Layanan:</label>
                        <select id="service_type_filter" class="form-control">
                            <option value="">Semua Layanan</option>
                            <option value="BPJS I">BPJS I</option>
                            <option value="BPJS II">BPJS II</option>
                            <option value="BPJS III">BPJS III</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button type="button" id="btn-filter" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" id="btn-reset" class="btn btn-default">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <button type="button" id="btn-export" class="btn btn-success">
                            <i class="fa fa-download"></i> Export Excel
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="fa fa-signature"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Tanda Tangan</span>
                                <span class="info-box-number" id="total-signatures">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Hari Ini</span>
                                <span class="info-box-number" id="today-signatures">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-hospital-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">BPJS I</span>
                                <span class="info-box-number" id="bpjs1-count">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-user-md"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">BPJS II & III</span>
                                <span class="info-box-number" id="bpjs23-count">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table id="signature-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Nama Pasien</th>
                                <th>Jenis Layanan</th>
                                <th>Kasir</th>
                                <th>Cabang</th>
                                <th>Waktu Tanda Tangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tanda Tangan -->
<div class="modal fade" id="signature-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Tanda Tangan Pasien: <span id="modal-pasien-name"></span></h4>
            </div>
            <div class="modal-body text-center">
                <img id="modal-signature-image" src="" alt="Tanda Tangan" style="max-width: 100%; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="downloadSignature()">Download</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    let signatureTable = $('#signature-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("laporan.signature.bpjs.data") }}',
            data: function(d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.branch_id = $('#branch_filter').val();
                d.service_type = $('#service_type_filter').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'tanggal', name: 'tanggal'},
            {data: 'kode_penjualan', name: 'kode_penjualan'},
            {data: 'nama_pasien', name: 'nama_pasien'},
            {data: 'service_type', name: 'service_type'},
            {data: 'kasir', name: 'kasir'},
            {data: 'cabang', name: 'cabang'},
            {data: 'signature_date', name: 'signature_date'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });
    
    // Filter button
    $('#btn-filter').click(function() {
        signatureTable.ajax.reload();
        updateSummary();
    });
    
    // Reset button
    $('#btn-reset').click(function() {
        $('#start_date').val('{{ date("Y-m-d", strtotime("-30 days")) }}');
        $('#end_date').val('{{ date("Y-m-d") }}');
        $('#branch_filter').val('');
        $('#service_type_filter').val('');
        signatureTable.ajax.reload();
        updateSummary();
    });
    
    // Export button
    $('#btn-export').click(function() {
        let params = new URLSearchParams({
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            branch_id: $('#branch_filter').val(),
            service_type: $('#service_type_filter').val()
        });
        
        window.open('{{ route("laporan.signature.bpjs.data") }}?' + params.toString() + '&export=1', '_blank');
    });
    
    // Update summary on page load
    updateSummary();
    
    function updateSummary() {
        $.ajax({
            url: '{{ route("laporan.signature.bpjs.data") }}',
            data: {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                branch_id: $('#branch_filter').val(),
                service_type: $('#service_type_filter').val()
            },
            success: function(response) {
                let data = response.data;
                let total = data.length;
                let today = data.filter(item => {
                    return item.tanggal === '{{ date("d/m/Y") }}';
                }).length;
                let bpjs1 = data.filter(item => item.service_type === 'BPJS I').length;
                let bpjs23 = data.filter(item => ['BPJS II', 'BPJS III'].includes(item.service_type)).length;
                
                $('#total-signatures').text(total);
                $('#today-signatures').text(today);
                $('#bpjs1-count').text(bpjs1);
                $('#bpjs23-count').text(bpjs23);
            }
        });
    }
});

// View signature function
function viewSignature(signatureData, pasienName) {
    $('#modal-pasien-name').text(pasienName);
    $('#modal-signature-image').attr('src', signatureData);
    $('#signature-modal').modal('show');
}

// Download signature function
function downloadSignature() {
    let link = document.createElement('a');
    link.download = 'tanda-tangan-' + $('#modal-pasien-name').text() + '.png';
    link.href = $('#modal-signature-image').attr('src');
    link.click();
}
</script>
@endpush

@push('styles')
<style>
.info-box {
    min-height: 80px;
    margin-bottom: 20px;
}

.info-box-icon {
    height: 80px;
    line-height: 80px;
    font-size: 30px;
}

.info-box-content {
    padding: 15px;
}

.info-box-number {
    font-size: 24px;
    font-weight: bold;
}

.info-box-text {
    font-size: 14px;
    margin-bottom: 5px;
}
</style>
@endpush
