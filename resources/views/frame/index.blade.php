@extends('layouts.master')

@section('title')
    Data Stok Frame
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Frame</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-12 d-flex align-items-center gap-2">
        <button onclick="showImportModal()" class="btn btn-success">
            <i class="fa fa-upload"></i> Import Excel
        </button>
        <button onclick="exportFrameData()" class="btn btn-info">
            <i class="fa fa-download"></i> Export Excel
        </button>
        <button onclick="exportFrameDataFull()" class="btn btn-warning">
            <i class="fa fa-download"></i> Export Lengkap
        </button>
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
                <button onclick="addform('{{ route('frame.store') }}')" class="btn btn-sm btn-custom">Tambah Frame</button>
                <button onclick="bulkDelete()" class="btn btn-danger" id="bulk-delete-btn" style="display:none;">
                    <i class="fa fa-trash"></i> Hapus Terpilih
                </button>
                @endif
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered" id="table-frame">
                    <thead>
                        <tr>
                            <th width="3%">
                                <input type="checkbox" name="select_all" id="select_all">
                            </th>
                            <th width="5%">No</th>
                            <th>Kode Frame</th>
                            <th>Merk Frame</th>
                            @if(auth()->user()->isSuperAdmin())
                            <th>Harga Beli</th>
                            @endif
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Jenis Frame</th>
                            <th>Cabang</th>
                            <th width="10%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('frame.form')

<!-- Modal Import -->
<div class="modal fade" id="modal-import" tabindex="-1" role="dialog" aria-labelledby="modal-import-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-import-label">Import Data Frame</h4>
            </div>
            <form id="form-import" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">File Excel (.xlsx, .xls)</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                        <small class="text-muted">Format: kode_frame, merk_frame, jenis_frame, harga_beli_frame, harga_jual_frame, stok, cabang, sales</small>
                        <br>
                        <a href="#" onclick="downloadTemplate()" class="btn btn-sm btn-info">
                            <i class="fa fa-download"></i> Download Template Excel
                        </a>
                    </div>
                    <div class="alert alert-info">
                        <strong>Petunjuk:</strong>
                        <ul>
                            <li>File harus berformat Excel (.xlsx atau .xls)</li>
                            <li>Baris pertama harus berisi header sesuai format di atas</li>
                            <li>Kolom kode_frame bisa dikosongkan (akan di-generate otomatis)</li>
                            <li>Kolom cabang dan sales harus sesuai dengan data yang ada di sistem</li>
                            <li>Harga dan stok harus berupa angka</li>
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
    let jenisFilter = '';
    let allSelectedIds = new Set(); // Untuk menyimpan semua ID yang dipilih dari semua halaman
    let isSelectAllActive = false; // Status select all
    $(function () {
        let columns = [
            {data: 'checkbox', searchable: false, sortable: false},
            {data: 'DT_RowIndex', searchable: false, orderable: false},
            {data: 'kode_frame'},
            {data: 'merk_frame'},
            @if(auth()->user()->isSuperAdmin())
            {data: 'harga_beli_frame'},
            @endif
            {data: 'harga_jual_frame'},
            {data: 'stok'},
            {data: 'jenis_frame'},
            {data: 'branch_name'},
            {data: 'aksi', searchable: false, orderable: false},
        ];
        table = $('#table-frame').DataTable({
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
            columns: columns,
            drawCallback: function() {
                // Update checkbox status setelah tabel di-redraw, tapi hanya jika ada selection
                if (allSelectedIds.size > 0 || isSelectAllActive) {
                    setTimeout(function() {
                        updateCheckboxesOnCurrentPage();
                        updateSelectAllStatus();
                    }, 100);
                } else {
                    // Reset select all checkbox jika tidak ada selection
                    $('#select_all').prop('checked', false);
                    $('#select_all').prop('indeterminate', false);
                }
            }
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
        // Event handler untuk select all
        $(document).on('change', '#select_all', function(){
            const isChecked = $(this).is(':checked');
            
            if (isChecked) {
                // Select all - pilih semua data di halaman saat ini
                $('input[name="selected_frame[]"]').prop('checked', true);
                $('input[name="selected_frame[]"]').each(function() {
                    allSelectedIds.add($(this).val());
                });
                isSelectAllActive = true;
            } else {
                // Unselect all - hapus semua selection
                $('input[name="selected_frame[]"]').prop('checked', false);
                allSelectedIds.clear();
                isSelectAllActive = false;
            }
            
            updateBulkDeleteButton();
        });

        // Event handler untuk checkbox individual
        $(document).on('change', 'input[name="selected_frame[]"]', function() {
            const id = $(this).val();
            const isChecked = $(this).is(':checked');
            
            if (isChecked) {
                allSelectedIds.add(id);
            } else {
                allSelectedIds.delete(id);
                isSelectAllActive = false;
            }
            
            updateBulkDeleteButton();
            updateSelectAllStatus();
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

    function bulkDelete() {
        const selectedIds = Array.from(allSelectedIds);

        if (selectedIds.length === 0) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Pilih data frame yang akan dihapus terlebih dahulu.',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Hapus Massal',
            html: `Yakin ingin menghapus <strong>${selectedIds.length}</strong> data frame yang dipilih?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '{{ route('frame.bulk-delete') }}',
                    type: 'POST',
                    data: {
                        '_token': $('[name=csrf-token]').attr('content'),
                        'ids': selectedIds
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Request failed: ${error.responseJSON?.message || 'Tidak dapat menghapus data'}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                table.ajax.reload();
                // Reset semua state
                allSelectedIds.clear();
                isSelectAllActive = false;
                $('#select_all').prop('checked', false);
                $('#bulk-delete-btn').hide();
                
                Swal.fire(
                    'Berhasil!',
                    result.value.message || 'Data berhasil dihapus',
                    'success'
                );
            }
        });
    }

    // Fungsi untuk update checkbox di halaman saat ini
    function updateCheckboxesOnCurrentPage() {
        $('input[name="selected_frame[]"]').each(function() {
            const id = $(this).val();
            const shouldBeChecked = allSelectedIds.has(id);
            if ($(this).is(':checked') !== shouldBeChecked) {
                $(this).prop('checked', shouldBeChecked);
            }
        });
    }

    function updateBulkDeleteButton() {
        const checkedCount = allSelectedIds.size;
        if (checkedCount > 0) {
            $('#bulk-delete-btn').show().text(`Hapus Terpilih (${checkedCount})`);
        } else {
            $('#bulk-delete-btn').hide();
        }
    }

    // Fungsi untuk mengupdate status select all checkbox
    function updateSelectAllStatus() {
        const currentPageCheckboxes = $('input[name="selected_frame[]"]');
        const currentPageChecked = currentPageCheckboxes.filter(':checked').length;
        const totalCurrentPage = currentPageCheckboxes.length;
        
        if (currentPageChecked === 0) {
            $('#select_all').prop('checked', false);
            $('#select_all').prop('indeterminate', false);
        } else if (currentPageChecked === totalCurrentPage) {
            $('#select_all').prop('checked', true);
            $('#select_all').prop('indeterminate', false);
        } else {
            $('#select_all').prop('checked', false);
            $('#select_all').prop('indeterminate', true);
        }
    }

    function showImportModal() {
        $('#modal-import').modal('show');
    }

    function downloadTemplate() {
        // Buat template Excel sederhana menggunakan JavaScript
        const headers = ['kode_frame', 'merk_frame', 'jenis_frame', 'harga_beli_frame', 'harga_jual_frame', 'stok', 'cabang', 'sales'];
        const sampleData = [
            ['FR000001', 'Ray-Ban', 'Sunglasses', '500000', '750000', '10', 'Cabang Jakarta', 'John Doe'],
            ['FR000002', 'Oakley', 'Sport', '300000', '450000', '15', 'Cabang Bandung', 'Jane Smith'],
            ['FR000003', 'Gucci', 'Fashion', '800000', '1200000', '5', 'Cabang Surabaya', 'Bob Johnson']
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
        link.setAttribute('download', 'template_frame.csv');
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
            url: '{{ route('frame.import') }}',
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

    // Fungsi untuk export Frame data
    function exportFrameData() {
        // Tampilkan loading
        Swal.fire({
            title: 'Mengexport Data Frame...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Buat link untuk download
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = '{{ route('frame.export') }}';
        a.download = 'frame_data.xlsx';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        // Tutup loading setelah beberapa detik
        setTimeout(() => {
            Swal.close();
            
            // Tampilkan pesan sukses
            Swal.fire({
                icon: 'success',
                title: 'Export Berhasil!',
                text: 'File Excel data frame telah berhasil diunduh',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }

    function exportFrameDataFull() {
        // Tampilkan loading
        Swal.fire({
            title: 'Mengexport Data Frame Lengkap...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Buat link untuk download
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = '{{ route('frame.export-full') }}';
        a.download = 'frame_data_lengkap.xlsx';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        // Tutup loading setelah beberapa detik
        setTimeout(() => {
            Swal.close();
            
            // Tampilkan pesan sukses
            Swal.fire({
                icon: 'success',
                title: 'Export Berhasil!',
                text: 'File Excel data frame lengkap telah berhasil diunduh',
                timer: 2000,
                showConfirmButton: false
            });
        }, 2000);
    }
</script>
@endpush
