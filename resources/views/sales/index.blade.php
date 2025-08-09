@extends('layouts.master')

@section('title')
    Data Sales
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
                <button onclick="addform('{{ route('sales.store') }}')" class="btn btn-sm btn-custom">Tambah Sales</button>
                <button onclick="showImportModal()" class="btn btn-sm btn-success"><i class="fa fa-upload"></i> Import Excel</button>
                <a href="{{ route('sales.export') }}" class="btn btn-sm btn-info"><i class="fa fa-download"></i> Export Excel</a>
                <a href="{{ route('sales.export-full') }}" class="btn btn-sm btn-warning"><i class="fa fa-download"></i> Export Lengkap</a>
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

<!-- Modal Import -->
<div class="modal fade" id="modal-import" tabindex="-1" role="dialog" aria-labelledby="modal-import-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-import-label">Import Data Sales</h4>
            </div>
            <form id="form-import" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">File Excel (.xlsx, .xls)</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format: nama_sales, alamat, nohp, keterangan</small>
                        <br>
                        <a href="#" onclick="downloadTemplate()" class="btn btn-sm btn-info">
                            <i class="fa fa-download"></i> Download Template Excel
                        </a>
                    </div>
                    <div class="alert alert-info">
                        <strong>Petunjuk:</strong>
                        <ul>
                            <li>File harus berformat Excel (.xlsx atau .xls)</li>
                            <li>Baris pertama harus berisi header: nama_sales, alamat, nohp, keterangan</li>
                            <li>Kolom nama_sales wajib diisi</li>
                            <li>Kolom alamat, nohp, dan keterangan bersifat opsional</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Tidak dapat menampilkan data.',
                });
            });
    }
    function deleteData(url) {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Yakin ingin menghapus data terpilih?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                    Swal.fire(
                        'Berhasil!',
                        'Data berhasil dihapus.',
                        'success'
                    );
                })
                .fail((errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Tidak dapat menghapus data.',
                    });
                });
            }
        });
    }

    function showImportModal() {
        $('#modal-import').modal('show');
    }

    function downloadTemplate() {
        // Buat template Excel sederhana menggunakan JavaScript
        const headers = ['nama_sales', 'alamat', 'nohp', 'keterangan'];
        const sampleData = [
            ['John Doe', 'Jl. Contoh No. 123, Jakarta', '081234567890', 'Sales Senior'],
            ['Jane Smith', 'Jl. Sample No. 456, Bandung', '081234567891', 'Sales Junior'],
            ['Bob Johnson', 'Jl. Template No. 789, Surabaya', '081234567892', 'Sales Manager']
        ];
        
        // Buat CSV content
        let csvContent = headers.join(',') + '\n';
        sampleData.forEach(row => {
            csvContent += row.join(',') + '\n';
        });
        
        // Download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'template_sales.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Event handler untuk form import
    $('#form-import').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route('sales.import') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#modal-import').modal('hide');
                $('#form-import')[0].reset();
                table.ajax.reload();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: response.message || 'Terjadi kesalahan saat import data.',
                });
            }
        });
    });
</script>
@endpush
