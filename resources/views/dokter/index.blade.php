@extends('layouts.master')

@section('title')
    Dokter
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dokter</li>
@endsection

@section('content')
<div class="row"></div>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addform('{{ route('dokter.store') }}')" class="btn btn-sm btn-custom">Tambah dokter</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stipet table-bordered" id="table">
                    <thead>
                        <th width='5%'>No</th>
                        <th>Nama dokter</th>
                        <th>Alamat</th>
                        <th>Kontak</th>
                        <th width='10%'><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('dokter.form')
@endsection

@push('scripts')
<script>
    let table;
    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('dokter.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'nama_dokter'},
                {data: 'alamat'},
                {data: 'nohp'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });
        $('#modal-form').validator().on('submit', function (e) {
            if (!e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menyimpan data');
                        return;
                    });
            }
        });
        $('[name=select_all]').on('click', function(){
            $(':checkbox').prop('checked', this.checked);
        });
    });
    function addform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah dokter');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_dokter]').focus();
    }
    function editform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit dokter');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_dokter]').focus();
        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama_dokter]').val(response.nama_dokter);
                $('#modal-form [name=alamat]').val(response.alamat);
                $('#modal-form [name=nohp]').val(response.nohp);
                $('#modal-form [name=keterangan]').val(response.keterangan);
            })
            .fail((errors) => {
                alert('Tidak dapat menampilkan data');
                return;
            });
    }
    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                '_token': $('[name=csrf-token]').attr('content'),
                '_method': 'delete'
            })
            .done((response) => {
                table.ajax.reload();
            })
            .fail((errors) => {
                alert('Tidak dapat menghapus data');
                return;
            });
        }
    }
</script>
@endpush
