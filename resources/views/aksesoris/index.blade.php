@extends('layouts.master')

@section('title')
    Aksesoris
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Aksesoris</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addform('{{ route('aksesoris.store') }}')" class="btn btn-sm btn-custom">Tambah Aksesoris</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stipet table-bordered" id="table">
                    <thead>
                        <th width='5%'>No</th>
                        <th>Nama Produk</th>
                        @if(auth()->user()->isSuperAdmin())
                        <th>Harga Beli</th>
                        @endif
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th width='10%'><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('aksesoris.form')
@endsection

@push('scripts')
<script>
    let table;
    let columns = [
        {data: 'DT_RowIndex', searchable: false, orderable: false},
        {data: 'nama_produk'},
        @if(auth()->user()->isSuperAdmin())
        {data: 'harga_beli'},
        @endif
        {data: 'harga_jual'},
        {data: 'stok'},
        {data: 'aksi', searchable: false, orderable: false},
    ];
    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('aksesoris.index') }}',
            },
            columns: columns
        });
        $('#modal-form').validator().on('submit', function (e) {
            if (!e.preventDefault()) {
                var form = $('#modal-form form');
                var url = form.attr('action');
                var method = form.find('[name=_method]').val() === 'put' ? 'PUT' : 'POST';
                $.ajax({
                    url: url,
                    type: method,
                    data: form.serialize(),
                    success: function(response) {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(errors) {
                        alert('Tidak dapat menyimpan data');
                    }
                });
            }
        });

        // Edit aksesoris
        $(document).on('click', '.btn-edit-aksesoris', function () {
            var id = $(this).data('id');
            var urlShow = '{{ url('aksesoris') }}/' + id;
            var urlUpdate = '{{ url('aksesoris') }}/' + id;
            $('#modal-form').modal('show');
            $('#modal-form .modal-title').text('Edit Aksesoris');
            $('#modal-form form')[0].reset();
            $('#modal-form form').attr('action', urlUpdate);
            $('#modal-form [name=_method]').val('put');
            $.get(urlShow, function (data) {
                $('#modal-form [name=nama_produk]').val(data.nama_produk);
                $('#modal-form [name=harga_beli]').val(data.harga_beli);
                $('#modal-form [name=harga_jual]').val(data.harga_jual);
                $('#modal-form [name=stok]').val(data.stok);
            });
        });

        // Delete aksesoris
        $(document).on('submit', 'form[action*="aksesoris"]', function(e) {
            e.preventDefault();
            if (confirm('Yakin hapus data ini?')) {
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        table.ajax.reload();
                    },
                    error: function() {
                        alert('Tidak dapat menghapus data');
                    }
                });
            }
        });
    });
    function addform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Aksesoris');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_produk]').focus();
    }
</script>
@endpush 