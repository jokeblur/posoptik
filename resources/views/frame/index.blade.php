@extends('layouts.master')

@section('title')
    Frame
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Frame</li>
@endsection

@section('content')
@if(session('error'))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Error!</h4>
            {{ session('error') }}
        </div>
    </div>
</div>
@endif

@if(session('success'))
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-check"></i> Success!</h4>
            {{ session('success') }}
        </div>
    </div>
</div>
@endif

<div class="row mb-3">
    <div class="col-md-12 d-flex align-items-center gap-2">
        <form action="{{ route('frame.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
            @csrf
            <div class="input-group mb-3">
                <input type="file" name="file" class="form-control" required>
                <button class="btn btn-success" type="submit">Import Frame</button>
            </div>
        </form>
        <a href="{{ route('frame.export') }}" class="btn btn-info ms-2">Export Frame</a>
        <a href="{{ route('test.frame.export') }}" class="btn btn-warning ms-2" target="_blank">Test Export</a>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-aqua" style="cursor:pointer" onclick="filterJenis('BPJS I')">
            <div class="inner text-center">
                <h4>BPJS I</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-green" style="cursor:pointer" onclick="filterJenis('BPJS II')">
            <div class="inner text-center">
                <h4>BPJS II</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-yellow" style="cursor:pointer" onclick="filterJenis('BPJS III')">
            <div class="inner text-center">
                <h4>BPJS III</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-red" style="cursor:pointer" onclick="filterJenis('Umum')">
            <div class="inner text-center">
                <h4>Umum</h4>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <button onclick="addform('{{ route('frame.store') }}')" class="btn btn-primary">Tambah Frame</button>
                @endif
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stipet table-bordered" id="table">
                    <thead>
                        <th><input type="checkbox" name="select_all" id="select_all"></th>
                        <th width='5%'>No</th>
                        <th>Kode Frame</th>
                        <th>Nama Frame</th>
                        <th>Jenis Frame</th>
                        <th>Cabang</th>
                        <th>Nama Sales</th>
                        <th>Harga Beli</th>
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
@includeIf('frame.form')
@endsection

@push('scripts')
<script>
    let table;
    let jenisFilter = '';
    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('frame.data') }}',
                data: function(d) {
                    d.jenis_frame = jenisFilter;
                }
            },
            columns: [
                {data: 'select_all'},
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_frame'},
                {data: 'merk_frame'},
                {data: 'jenis_frame'},
                {data: 'branch_name'},
                {data: 'sales_name'},
                {data: 'harga_beli_frame'},
                {data: 'harga_jual_frame'},
                {data: 'stok'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });
        $('#modal-form').validator().on('submit', function (e) {
            if (!e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data berhasil disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    })
                    .fail((errors) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Tidak dapat menyimpan data.',
                        });
                    });
            }
        });
        // Inisialisasi ulang event handler setelah DataTables draw
        table.on('draw', function() {
            $('#select_all').prop('checked', false);
            $('#select_all').off('click').on('click', function() {
                var checked = this.checked;
                $('#table tbody input[type="checkbox"][name="id_frame[]"]').prop('checked', checked);
            });
        });
    });
    function addform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Frame');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_frame]').focus();
    }
    function editform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Frame');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_frame]').focus();
        $.get(url)
            .done((response) => {
                $('#modal-form [name=merk_frame]').val(response.merk_frame);
                $('#modal-form [name=id_sales]').val(response.id_sales);
                $('#modal-form [name=kode_frame]').val(response.kode_frame);
                $('#modal-form [name=jenis_frame]').val(response.jenis_frame);
                $('#modal-form [name=harga_jual_frame]').val(response.harga_jual_frame);
                $('#modal-form [name=harga_beli_frame]').val(response.harga_beli_frame);
                $('#modal-form [name=stok]').val(response.stok);
                $('#modal-form [name=branch_id]').val(response.branch_id);
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
    function filterJenis(jenis) {
        jenisFilter = jenis;
        table.ajax.reload();
    }
</script>
@endpush
