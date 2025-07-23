@extends('layouts.master')

@section('title')
    Sales
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Sales</li>
@endsection

@section('content')
<div class="row"></div>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addform('{{ route('sales.store') }}')" class="btn btn-primary">Tambah Sales</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stipet table-bordered" id="table">
                    <thead>
                        <th width='5%'>No</th>
                        <th>Nama Sales</th>
                        <th>Alamat</th>
                        <th>Kontak</th>
                        <th>Keterangan</th>
                        <th width='10%'><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('sales.form')
@endsection

@push('scripts')
<script>
    let table;
    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('sales.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'nama_sales'},
                {data: 'alamat'},
                {data: 'nohp'},
                {data: 'keterangan'},
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
        $('#modal-form .modal-title').text('Tambah sales');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_sales]').focus();
    }
    function editform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit sales');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_sales]').focus();
        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama_sales]').val(response.nama_sales);
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
