@extends('layouts.master')

@section('title')
    lensa
@endsection

@section('breadcrumb')
    @parent
    <li class="active">lensa</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addform('{{ route('lensa.store') }}')" class="btn btn-primary">Tambah lensa</button>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="small-box bg-aqua" style="cursor:pointer" onclick="filterJenis(1)">
            <div class="inner text-center">
                <h1>Optik Melati </h1>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="small-box bg-green" style="cursor:pointer" onclick="filterJenis(2)">
            <div class="inner text-center">
                <h1>Optik Melati 2</h1>
            </div>
        </div>
    </div>
    
</div>

<div class="row mb-3">
    <div class="col-md-12 d-flex align-items-center gap-2">
        <form action="{{ route('lensa.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
            @csrf
            <div class="input-group mb-3">
                <input type="file" name="file" class="form-control" required>
                <button class="btn btn-success" type="submit">Import Lensa</button>
            </div>
        </form>
        <a href="{{ route('lensa.export') }}" class="btn btn-info ms-2">Export Lensa</a>
    </div>
</div>

<!-- Tabel utama tetap ada untuk semua data -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            
            <div class="box-body table-responsive">
                <table class="table table-stipet table-bordered" id="table-lensa">
                    <thead>
                        <th><input type="checkbox" name="select_all" id="select_all"></th>
                        <th width='5%'>No</th>
                        <th>Kode lensa</th>
                        <th>Nama lensa</th>
                        <th>Cabang</th>
                        <th>Type</th>
                        <th>Ukuran</th>
                        <th>Coating</th>
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
@includeIf('lensa.form')
@endsection

@push('scripts')
<script>
    let table;
    let jenisFilter = '';
  
 $(function () {
        table = $('#table-lensa').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('lensa.data') }}',
                data: function(d) {
                    if(jenisFilter) {
                        d.branch_id = jenisFilter;
                    }
                }
            },
            columns: [
                {data: 'select_all'},
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_lensa'},
                {data: 'merk_lensa'},
                {data: 'branch_name'},
                {data: 'type'},
                {data: 'index'},
                {data: 'coating'},
                {data: 'harga_beli_lensa'},
                {data: 'harga_jual_lensa'},
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
                $('#table-lensa tbody input[type="checkbox"][name="id[]"]').prop('checked', checked);
            });
        });
    });

    function addform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah lensa');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_lensa]').focus();
    }
    function editform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit lensa');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_lensa]').focus();
        $.get(url)
            .done((response) => {
                $('#modal-form [name=merk_lensa]').val(response.merk_lensa);
                $('#modal-form [name=kode_lensa]').val(response.kode_lensa);
                $('#modal-form [name=type]').val(response.type);
                $('#modal-form [name=index]').val(response.index);
                $('#modal-form [name=coating]').val(response.coating);
                $('#modal-form [name=harga_jual_lensa]').val(response.harga_jual_lensa);
                $('#modal-form [name=harga_beli_lensa]').val(response.harga_beli_lensa);
                $('#modal-form [name=stok]').val(response.stok);
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
