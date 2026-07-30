@extends('layouts.master')

@section('title', 'Audit Hapus Penjualan')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Riwayat Audit Hapus Penjualan</h3>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">Tanggal Akhir</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-3" style="padding-top: 25px;">
                        <button type="button" class="btn btn-primary" onclick="reloadAuditTable()">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="audit-delete-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Waktu Log</th>
                                <th>Kode Penjualan</th>
                                <th>ID Penjualan</th>
                                <th>Dihapus Oleh</th>
                                <th>Role</th>
                                <th>Cabang</th>
                                <th>Stok Dikembalikan</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let auditDeleteTable;

    $(function () {
        auditDeleteTable = $('#audit-delete-table').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            ajax: {
                url: '{{ route('laporan.penjualan-delete.data') }}',
                data: function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', searchable: false, orderable: false },
                { data: 'deleted_at', name: 'deleted_at' },
                { data: 'kode_penjualan', name: 'kode_penjualan' },
                { data: 'penjualan_id', name: 'penjualan_id' },
                { data: 'deleted_by', name: 'deleted_by' },
                { data: 'deleted_by_user_role', name: 'deleted_by_user_role' },
                { data: 'branch_id', name: 'branch_id' },
                { data: 'restored_summary', name: 'restored_summary' },
                { data: 'aksi', searchable: false, orderable: false }
            ]
        });
    });

    function reloadAuditTable() {
        auditDeleteTable.ajax.reload();
    }

    function showRestoredItems(itemsJson) {
        let items = [];

        try {
            items = JSON.parse(itemsJson);
        } catch (error) {
            items = [];
        }

        if (!items.length) {
            Swal.fire('Informasi', 'Tidak ada data pengembalian stok pada log ini.', 'info');
            return;
        }

        const htmlRows = items.map(function (item, index) {
            return `<tr>
                <td>${index + 1}</td>
                <td>${item.itemable_type || '-'}</td>
                <td>${item.itemable_id || '-'}</td>
                <td>${item.qty_restored || 0}</td>
            </tr>`;
        }).join('');

        Swal.fire({
            title: 'Detail Pengembalian Stok',
            width: 700,
            html: `<div class="table-responsive">
                <table class="table table-bordered table-striped" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tipe Item</th>
                            <th>ID Item</th>
                            <th>Qty Dikembalikan</th>
                        </tr>
                    </thead>
                    <tbody>${htmlRows}</tbody>
                </table>
            </div>`,
            confirmButtonText: 'Tutup'
        });
    }
</script>
@endpush
