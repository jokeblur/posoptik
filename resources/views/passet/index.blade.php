@extends('layouts.master')

@section('title', 'Daftar Pengerjaan Passet')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Pekerjaan yang Perlu Diselesaikan</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="passet-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal Masuk</th>
                            <th>Kode Penjualan</th>
                            <th>Nama Pasien</th>
                            <th>Cabang</th>
                            <th>Status Pengerjaan</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table;
    $(function() {
        table = $('#passet-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('passet.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'kode_penjualan', name: 'kode_penjualan' },
                { data: 'pasien_name', name: 'pasien_name' },
                { data: 'cabang_name', name: 'cabang_name' },
                { data: 'status_pengerjaan', name: 'status_pengerjaan' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ]
        });
    });

    function markAsSelesai(url) {
        if (confirm('Anda yakin pengerjaan untuk item ini sudah selesai?')) {
            $.post(url, { '_token': '{{ csrf_token() }}' })
                .done(response => {
                    table.ajax.reload();
                    Swal.fire('Berhasil!', response.message, 'success');
                })
                .fail(errors => {
                    Swal.fire('Gagal!', 'Tidak dapat mengubah status.', 'error');
                });
        }
    }
</script>
@endpush 