@extends('layouts.master')

@section('title')
    Data Pasien Optik Melati
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Pasien</li>
@endsection

@section('content')
<div class="row"></div>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addform('{{ route('pasien.store') }}')" class="btn btn-custom">Tambah pasien</button>
                <button onclick="bulkDelete()" class="btn btn-danger" id="bulk-delete-btn" style="display:none;">
                    <i class="fa fa-trash"></i> Hapus Terpilih
                </button>
                <a href="{{ route('pasien.export') }}" class="btn btn-success">Export</a>
                <form action="{{ route('pasien.import') }}" method="POST" enctype="multipart/form-data" style="display:inline-block;">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required style="display:inline-block;width:auto;">
                    <button type="submit" class="btn btn-primary">Import</button>
                </form>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stipet table-bordered" id="table">
                    <thead>
                        <th width='3%'>
                            <input type="checkbox" name="select_all" id="select_all">
                        </th>
                        <th width='5%'>No</th>
                        <th>Nama pasien</th>
                        <th>Alamat</th>
                        <th>Kontak</th>
                        <th>Jenis Layanan</th>
                        <th width='10%'><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@includeIf('pasien.form')
@includeIf('pasien.detail')
@endsection

@push('scripts')
<script>
    let table;
    let allSelectedIds = new Set(); // Untuk menyimpan semua ID yang dipilih dari semua halaman
    let isSelectAllActive = false; // Status select all

    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('pasien.data') }}',
            },
            columns: [
                {data: 'checkbox', searchable: false, sortable: false},
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'nama_pasien'},
                {data: 'alamat'},
                {data: 'nohp'},
                {data: 'service_type'},
                {data: 'aksi', searchable: false, sortable: false},
            ],
            drawCallback: function() {
                // Update checkbox status setelah tabel di-redraw
                if (allSelectedIds.size > 0 || isSelectAllActive) {
                    setTimeout(function() {
                        updateCheckboxesOnCurrentPage();
                    }, 100);
                }
            }
        });

        $('#modal-form').validator().on('submit', function (e) {
            if (!e.preventDefault()) {
                $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
                    .done((response) => {
                        $('#modal-form').modal('hide');
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        Swal.fire(
                            'Error!',
                            'Tidak dapat menyimpan data',
                            'error'
                        );
                        return;
                    });
            }
        });

        // Handle tombol "Simpan & Lanjut ke Transaksi"
        $(document).on('click', '#btn-simpan-transaksi', function() {
            let form = $('#modal-form form');
            
            // Validasi form
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            $.post('{{ route("pasien.store-and-redirect") }}', form.serialize())
                .done((response) => {
                    $('#modal-form').modal('hide');
                    table.ajax.reload();
                    
                    // Tampilkan pesan sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect ke halaman transaksi dengan data pasien
                        window.location.href = response.redirect_url;
                    });
                })
                .fail((errors) => {
                    let message = 'Tidak dapat menyimpan data';
                    if (errors.responseJSON && errors.responseJSON.message) {
                        message = errors.responseJSON.message;
                    }
                    Swal.fire(
                        'Error!',
                        message,
                        'error'
                    );
                });
        });

        // Event handler untuk select all
        $(document).on('change', '#select_all', function(){
            const isChecked = $(this).is(':checked');
            
            if (isChecked) {
                // Select all - pilih semua data di halaman saat ini
                $('input[name="selected_pasien[]"]').prop('checked', true);
                $('input[name="selected_pasien[]"]').each(function() {
                    allSelectedIds.add($(this).val());
                });
                isSelectAllActive = true;
            } else {
                // Unselect all - hapus semua selection
                $('input[name="selected_pasien[]"]').prop('checked', false);
                allSelectedIds.clear();
                isSelectAllActive = false;
            }
            
            updateBulkDeleteButton();
        });

        // Reset modal content when modal is hidden
        $('#modal-detail').on('hidden.bs.modal', function () {
            $('#detail-prescriptions-container').empty();
            $('#detail-nama').text('');
            $('#detail-alamat').text('');
            $('#detail-nohp').text('');
            $('#detail-service_type').text('');
            $('#detail-no-bpjs').text('');
            $('#detail-dokter').text('');
        });
    });

    function showDetail(url) {
        // Clear modal content first to prevent duplication
        $('#detail-prescriptions-container').empty();
        $('#detail-nama').text('');
        $('#detail-alamat').text('');
        $('#detail-nohp').text('');
        $('#detail-service_type').text('');
        $('#detail-no-bpjs').text('');
        $('#detail-dokter').text('');
        
        $('#modal-detail').modal('show');

        $.get(url)
            .done((response) => {
                $('#detail-nama').text(response.nama_pasien);
                $('#detail-alamat').text(response.alamat);
                $('#detail-nohp').text(response.nohp);
                $('#detail-service_type').text(response.service_type);
                $('#detail-no-bpjs').text(response.no_bpjs || '-');
                let dokterNama = '-';
                if (response.prescriptions && response.prescriptions.length > 0) {
                    const latestPrescription = response.prescriptions[response.prescriptions.length - 1];
                    if (latestPrescription.dokter_manual && latestPrescription.dokter_manual !== '') {
                        dokterNama = latestPrescription.dokter_manual;
                    } else {
                        dokterNama = latestPrescription.dokter_nama || '-';
                    }
                }
                $('#detail-dokter').text(dokterNama);

                // Tampilkan/sembunyikan baris No. BPJS sesuai jenis layanan
                if(response.service_type && response.service_type.toLowerCase() === 'umum') {
                    $('#row-no-bpjs').hide();
                } else {
                    $('#row-no-bpjs').show();
                }

                let prescriptionsContainer = $('#detail-prescriptions-container');
                prescriptionsContainer.empty(); // Clear previous data

                if (response.prescriptions && response.prescriptions.length > 0) {
                    // Sort prescriptions by date to ensure proper order
                    const sortedPrescriptions = response.prescriptions.sort((a, b) => 
                        new Date(a.tanggal) - new Date(b.tanggal)
                    );
                    
                    // Debug: log the prescriptions to check for duplicates
                    console.log('Prescriptions data:', sortedPrescriptions);
                    
                    sortedPrescriptions.forEach(function(rx) {
                        const prescriptionHtml = `
                            <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                                <div class="row">
                                    <div class="col-sm-6"><strong>Tanggal:</strong> ${rx.tanggal}</div>
                                    <div class="col-sm-3"><strong>ADD:</strong> ${rx.add || '-'}</div>
                                    <div class="col-sm-3"><strong>PD:</strong> ${rx.pd || '-'}</div>
                                </div>
                                <table class="table table-bordered table-condensed" style="margin-top: 10px;">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center">SPH</th>
                                            <th class="text-center">CYL</th>
                                            <th class="text-center">AXIS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>OD</strong></td>
                                            <td class="text-center">${rx.od_sph || '-'}</td>
                                            <td class="text-center">${rx.od_cyl || '-'}</td>
                                            <td class="text-center">${rx.od_axis || '-'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>OS</strong></td>
                                            <td class="text-center">${rx.os_sph || '-'}</td>
                                            <td class="text-center">${rx.os_cyl || '-'}</td>
                                            <td class="text-center">${rx.os_axis || '-'}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div><strong>Catatan:</strong> ${rx.catatan || 'Tidak ada catatan.'}</div>
                            </div>
                        `;
                        prescriptionsContainer.append(prescriptionHtml);
                    });
                } else {
                    prescriptionsContainer.append('<p class="text-center">Tidak ada riwayat resep.</p>');
                }
            })
            .fail((errors) => {
                Swal.fire(
                    'Error!',
                    'Tidak dapat menampilkan data detail.',
                    'error'
                );
            });
    }

    function addform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah pasien');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=nama_pasien]').focus();
    }

    function editform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit pasien');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=nama_pasien]').focus();
        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama_pasien]').val(response.nama_pasien);
                $('#modal-form [name=alamat]').val(response.alamat);
                $('#modal-form [name=nohp]').val(response.nohp);
                $('#modal-form [name=service_type]').val(response.service_type);
                $('#modal-form [name=no_bpjs]').val(response.no_bpjs || '');
                if(response.service_type === 'BPJS I' || response.service_type === 'BPJS II' || response.service_type === 'BPJS III') {
                    $('#form-no-bpjs').show();
                } else {
                    $('#form-no-bpjs').hide();
                }
                if (response.prescriptions && response.prescriptions.length > 0) {
                    const latestPrescription = response.prescriptions[response.prescriptions.length - 1];
                    $('#modal-form [name=od_sph]').val(latestPrescription.od_sph);
                    $('#modal-form [name=od_cyl]').val(latestPrescription.od_cyl);
                    $('#modal-form [name=od_axis]').val(latestPrescription.od_axis);
                    $('#modal-form [name=os_sph]').val(latestPrescription.os_sph);
                    $('#modal-form [name=os_cyl]').val(latestPrescription.os_cyl);
                    $('#modal-form [name=os_axis]').val(latestPrescription.os_axis);
                    $('#modal-form [name=add]').val(latestPrescription.add);
                    $('#modal-form [name=pd]').val(latestPrescription.pd);
                    $('#modal-form [name=catatan]').val(latestPrescription.catatan);
                    $('#modal-form [name=dokter_id]').val(latestPrescription.dokter_id || '');
                } else {
                    $('#modal-form [name=dokter_id]').val('');
                }
            })
            .fail((errors) => {
                Swal.fire(
                    'Error!',
                    'Tidak dapat menampilkan data',
                    'error'
                );
                return;
            });
    }

    function deleteData(url) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Yakin ingin menghapus data pasien ini? Data riwayat resep juga akan ikut terhapus.',
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
                    if (response.message) {
                        Swal.fire(
                            'Berhasil!',
                            response.message,
                            'success'
                        );
                    }
                })
                .fail((errors) => {
                    let errorMessage = 'Tidak dapat menghapus data';
                    if (errors.responseJSON && errors.responseJSON.message) {
                        errorMessage = 'Gagal menghapus data: ' + errors.responseJSON.message;
                    }
                    Swal.fire(
                        'Error!',
                        errorMessage,
                        'error'
                    );
                });
            }
        });
    }

    function bulkDelete() {
        const selectedIds = Array.from(allSelectedIds);

        if (selectedIds.length === 0) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Pilih data pasien yang akan dihapus terlebih dahulu.',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Hapus Massal',
            html: `Yakin ingin menghapus <strong>${selectedIds.length}</strong> data pasien yang dipilih?<br><br><small class="text-muted">Data riwayat resep juga akan ikut terhapus.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '{{ route('pasien.bulk-delete') }}',
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

    // Event handler untuk checkbox individual
    $(document).on('change', 'input[name="selected_pasien[]"]', function() {
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

    // Fungsi untuk update checkbox di halaman saat ini
    function updateCheckboxesOnCurrentPage() {
        $('input[name="selected_pasien[]"]').each(function() {
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
        const currentPageCheckboxes = $('input[name="selected_pasien[]"]');
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
</script>
@endpush

