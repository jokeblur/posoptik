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
            <div class="box-body">
                @include('partials.mobile-table-wrapper')
                <table class="table table-striped table-bordered datatable" id="table">
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
            if (e.isDefaultPrevented()) {
                return;
            }

            e.preventDefault();
            submitPatientWithDuplicateCheck($('#modal-form form'), false);
        });

        $('#modal-form').on('blur', '[name="nama_pasien"]', function() {
            const form = $('#modal-form form');
            const name = $.trim($(this).val());

            if (form.find('[name="_method"]').val() === 'put' || !name) {
                return;
            }

            checkDuplicateName(form, name, true);
        });

        // Handle tombol "Simpan & Lanjut ke Transaksi"
        $(document).on('click', '#btn-simpan-transaksi', function() {
            let form = $('#modal-form form');
            
            // Validasi form
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            submitPatientWithDuplicateCheck(form, true);
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

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html();
    }

    function duplicatePatientTable(pasien) {
        let rows = pasien.map(function(row) {
            const resep = row.resep || {};
            const resepHtml = resep.od_sph || resep.od_cyl || resep.od_axis || resep.os_sph || resep.os_cyl || resep.os_axis
                ? 'OD: ' + escapeHtml([resep.od_sph || '-', resep.od_cyl || '-', resep.od_axis || '-'].join(' / ')) + '<br>OS: ' + escapeHtml([resep.os_sph || '-', resep.os_cyl || '-', resep.os_axis || '-'].join(' / ')) + '<br>ADD: ' + escapeHtml(resep.add || resep.add_kanan || resep.add_kiri || '-') + ' | PD: ' + escapeHtml(resep.pd || resep.pd_kanan || resep.pd_kiri || '-')
                : '-';

            return '<tr>' +
                '<td>' + escapeHtml(row.nama_pasien) + '</td>' +
                '<td>' + escapeHtml(row.umur) + '</td>' +
                '<td>' + escapeHtml(row.alamat) + '</td>' +
                '<td>' + escapeHtml(row.nohp) + '</td>' +
                '<td>' + escapeHtml(row.service_type) + '</td>' +
                '<td>' + escapeHtml(row.no_bpjs) + '</td>' +
                '<td>' + escapeHtml(row.tanggal_periksa || (row.created_at ? row.created_at.substring(0, 10) : '-')) + '</td>' +
                '<td style="white-space:nowrap;">' + resepHtml + '</td>' +
                '<td><button type="button" class="btn btn-xs btn-primary btn-pilih-pasien" data-pasien-id="' + row.id_pasien + '">Pilih</button></td>' +
                '</tr>';
        }).join('');

        return '<div style="max-height:260px; overflow:auto; text-align:left; font-size:12px;">' +
            '<table class="table table-bordered table-condensed" style="margin-bottom:0;">' +
            '<thead><tr><th>Nama</th><th>Umur</th><th>Alamat</th><th>Telepon</th><th>Layanan</th><th>No. BPJS</th><th>Tanggal</th><th>Ukuran Resep (SPH/CYL/AXIS)</th><th>Aksi</th></tr></thead>' +
            '<tbody>' + rows + '</tbody></table></div>';
    }

    function submitPatientWithDuplicateCheck(form, redirectToTransaction) {
        const action = redirectToTransaction ? '{{ route("pasien.store-and-redirect") }}' : form.attr('action');
        const name = $.trim(form.find('[name="nama_pasien"]').val());

        if (form.data('selected-patient-id')) {
            savePatient(form, action, redirectToTransaction);
            return;
        }

        if (form.data('duplicate-confirmed-name') === name) {
            savePatient(form, action, redirectToTransaction);
            return;
        }

        checkDuplicateName(form, name, false, function() {
            savePatient(form, action, redirectToTransaction);
        });
    }

    function savePatient(form, action, redirectToTransaction) {
        const selectedPatientId = form.data('selected-patient-id');

        if (selectedPatientId) {
            $('#modal-form').modal('hide');
            table.ajax.reload();

            if (redirectToTransaction) {
                window.location.href = '{{ route("penjualan.create", ["pasien_id" => "__PASIEN_ID__"]) }}'.replace('__PASIEN_ID__', selectedPatientId);
            } else {
                showDetail('{{ url('/pasien') }}/' + selectedPatientId);
            }
            return;
        }

        $.post(action, form.serialize())
            .done(function(result) {
                $('#modal-form').modal('hide');
                table.ajax.reload();

                if (redirectToTransaction) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = result.redirect_url;
                    });
                }
            })
            .fail(function(errors) {
                const message = errors.responseJSON && errors.responseJSON.message
                    ? errors.responseJSON.message
                    : 'Tidak dapat menyimpan data';
                Swal.fire('Error!', message, 'error');
            });
    }

    function checkDuplicateName(form, name, notifyOnly, onConfirmed) {
        if (form.data('selected-patient-name') && form.data('selected-patient-name') !== name) {
            form.removeData('selected-patient-id').removeData('selected-patient-name');
        }

        $.get('{{ route("pasien.check-duplicate-name") }}', { nama_pasien: name })
            .done(function(response) {
                if (!response.exists) {
                    if (!notifyOnly && onConfirmed) {
                        onConfirmed();
                    }
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Pasien ini sudah pernah masuk ke data pasien',
                    html: '<p style="text-align:left;">Berikut semua data pasien dengan nama yang sama:</p>' + duplicatePatientTable(response.pasien) + '<p style="margin-top:12px;">Apakah Anda ingin menambahkan data pasien lagi?</p>',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tambahkan Lagi',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    width: '95%'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.data('duplicate-confirmed-name', name);
                        if (!notifyOnly && onConfirmed) {
                            onConfirmed();
                        }
                    } else {
                        form.removeData('duplicate-confirmed-name');
                    }
                });

                const alertContainer = Swal.getHtmlContainer();
                if (alertContainer) {
                    $(alertContainer).off('click.pilihPasien').on('click.pilihPasien', '.btn-pilih-pasien', function() {
                        const selectedId = $(this).data('pasien-id');
                        const selectedPatient = response.pasien.find(function(row) {
                            return String(row.id_pasien) === String(selectedId);
                        });

                        form.data('selected-patient-id', selectedId);
                        form.data('selected-patient-name', name);
                        form.removeData('duplicate-confirmed-name');
                        if (selectedPatient) {
                            form.find('[name="nama_pasien"]').val(selectedPatient.nama_pasien || '');
                            form.find('[name="umur"]').val(selectedPatient.umur || '');
                            form.find('[name="alamat"]').val(selectedPatient.alamat || '');
                            form.find('[name="nohp"]').val(selectedPatient.nohp || '');
                            form.find('[name="service_type"]').val(selectedPatient.service_type || '').trigger('change');
                            form.find('[name="no_bpjs"]').val(selectedPatient.no_bpjs || '');
                            form.find('[name="tanggal_periksa"]').val((selectedPatient.tanggal_periksa || '').substring(0, 10));

                            const resep = selectedPatient.resep || {};
                            form.find('[name="od_sph"]').val(resep.od_sph || '');
                            form.find('[name="od_cyl"]').val(resep.od_cyl || '');
                            form.find('[name="od_axis"]').val(resep.od_axis || '');
                            form.find('[name="os_sph"]').val(resep.os_sph || '');
                            form.find('[name="os_cyl"]').val(resep.os_cyl || '');
                            form.find('[name="os_axis"]').val(resep.os_axis || '');
                            form.find('[name="add_kanan"]').val(resep.add_kanan || resep.add || '');
                            form.find('[name="add_kiri"]').val(resep.add_kiri || resep.add || '');
                            form.find('[name="pd_kanan"]').val(resep.pd_kanan || resep.pd || '');
                            form.find('[name="pd_kiri"]').val(resep.pd_kiri || resep.pd || '');
                            form.find('[name="catatan"]').val(resep.catatan || '');
                        }
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: 'Pasien dipilih',
                            text: 'Pasien yang sudah ada akan digunakan. Data baru tidak dibuat.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    });
                }
            })
            .fail(function() {
                Swal.fire('Error!', 'Tidak dapat memeriksa nama pasien.', 'error');
            });
    }

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
                const normalizeDate = (value) => {
                    const date = new Date(value);
                    return Number.isNaN(date.getTime()) ? 0 : date.getTime();
                };

                const uniquePrescriptions = Array.isArray(response.prescriptions)
                    ? response.prescriptions.filter((rx, idx, arr) => {
                        const key = [
                            rx.tanggal || '', rx.od_sph || '', rx.od_cyl || '', rx.od_axis || '',
                            rx.os_sph || '', rx.os_cyl || '', rx.os_axis || '',
                            rx.add || '', rx.add_kanan || '', rx.add_kiri || '',
                            rx.pd || '', rx.pd_kanan || '', rx.pd_kiri || '',
                            rx.catatan || ''
                        ].join('|');
                        return idx === arr.findIndex((row) => [
                            row.tanggal || '', row.od_sph || '', row.od_cyl || '', row.od_axis || '',
                            row.os_sph || '', row.os_cyl || '', row.os_axis || '',
                            row.add || '', row.add_kanan || '', row.add_kiri || '',
                            row.pd || '', row.pd_kanan || '', row.pd_kiri || '',
                            row.catatan || ''
                        ].join('|') === key);
                    })
                    : [];

                $('#detail-nama').text(response.nama_pasien);
                $('#detail-alamat').text(response.alamat);
                $('#detail-nohp').text(response.nohp);
                $('#detail-service_type').text(response.service_type);
                $('#detail-no-bpjs').text(response.no_bpjs || '-');
                let dokterNama = '-';
                if (uniquePrescriptions.length > 0) {
                    const latestPrescription = [...uniquePrescriptions].sort((a, b) => normalizeDate(a.tanggal) - normalizeDate(b.tanggal)).pop();
                    if (latestPrescription.dokter_manual && latestPrescription.dokter_manual !== '') {
                        dokterNama = latestPrescription.dokter_manual;
                    } else {
                        dokterNama = latestPrescription.dokter_nama || '-';
                    }
                }
                $('#detail-dokter').text(dokterNama);

                // Set URL untuk tombol cetak resep
                $('#btn-cetak-resep').attr('href', '{{ route("pasien.cetak-resep-kartu", ":id") }}'.replace(':id', response.id_pasien));
                $('#btn-cetak-resep-a4').attr('href', '{{ route("pasien.cetak-resep-a4", ":id") }}'.replace(':id', response.id_pasien));

                // Tampilkan/sembunyikan baris No. BPJS sesuai jenis layanan
                if(response.service_type && response.service_type.toLowerCase() === 'umum') {
                    $('#row-no-bpjs').hide();
                } else {
                    $('#row-no-bpjs').show();
                }

                let prescriptionsContainer = $('#detail-prescriptions-container');
                prescriptionsContainer.empty(); // Clear previous data

                if (uniquePrescriptions.length > 0) {
                    const sortedPrescriptions = [...uniquePrescriptions].sort((a, b) => normalizeDate(a.tanggal) - normalizeDate(b.tanggal));

                    sortedPrescriptions.forEach(function(rx) {
                        const addKanan = rx.add_kanan || rx.add || '-';
                        const addKiri = rx.add_kiri || rx.add || '-';
                        const pdKanan = rx.pd_kanan || rx.pd || '-';
                        const pdKiri = rx.pd_kiri || rx.pd || '-';

                        const prescriptionHtml = `
                            <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                                <div class="row">
                                    <div class="col-sm-4"><strong>Tanggal:</strong> ${rx.tanggal}</div>
                                    <div class="col-sm-4"><strong>ADD (R/L):</strong> ${addKanan} / ${addKiri}</div>
                                    <div class="col-sm-4"><strong>PD (R/L):</strong> ${pdKanan} / ${pdKiri}</div>
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
        $('#modal-form form').removeData('duplicate-confirmed-name');
        $('#modal-form form').removeData('selected-patient-id').removeData('selected-patient-name');
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#group-mode-resep-baru').show();
        $('#mode_resep_baru').prop('checked', false).trigger('change');
        $('#modal-form [name=nama_pasien]').focus();
    }

    function editform(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit pasien');
        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#group-mode-resep-baru').hide();
        $('#mode_resep_baru').prop('checked', false).trigger('change');
        $('#modal-form [name=nama_pasien]').focus();
        $.get(url)
            .done((response) => {
                $('#modal-form [name=nama_pasien]').val(response.nama_pasien);
                $('#modal-form [name=umur]').val(response.umur);
                $('#modal-form [name=alamat]').val(response.alamat);
                $('#modal-form [name=nohp]').val(response.nohp);
                $('#modal-form [name=anamnesa]').val(response.anamnesa);
                $('#modal-form [name=tanggal_periksa]').val(response.tanggal_periksa);
                $('#modal-form [name=service_type]').val(response.service_type);
                $('#modal-form [name=no_bpjs]').val(response.no_bpjs || '');
                if(response.service_type === 'BPJS I' || response.service_type === 'BPJS II' || response.service_type === 'BPJS III') {
                    $('#form-no-bpjs').show();
                } else {
                    $('#form-no-bpjs').hide();
                }
                if (response.prescriptions && response.prescriptions.length > 0) {
                    const latestPrescription = [...response.prescriptions]
                        .sort((a, b) => {
                            const dateA = new Date(a.tanggal);
                            const dateB = new Date(b.tanggal);
                            return (Number.isNaN(dateA.getTime()) ? 0 : dateA.getTime()) - (Number.isNaN(dateB.getTime()) ? 0 : dateB.getTime());
                        })
                        .pop();
                    $('#modal-form [name=od_sph]').val(latestPrescription.od_sph);
                    $('#modal-form [name=od_cyl]').val(latestPrescription.od_cyl);
                    $('#modal-form [name=od_axis]').val(latestPrescription.od_axis);
                    $('#modal-form [name=os_sph]').val(latestPrescription.os_sph);
                    $('#modal-form [name=os_cyl]').val(latestPrescription.os_cyl);
                    $('#modal-form [name=os_axis]').val(latestPrescription.os_axis);
                    $('#modal-form [name=add_kanan]').val(latestPrescription.add_kanan || latestPrescription.add || '');
                    $('#modal-form [name=add_kiri]').val(latestPrescription.add_kiri || latestPrescription.add || '');
                    $('#modal-form [name=pd_kanan]').val(latestPrescription.pd_kanan || latestPrescription.pd || '');
                    $('#modal-form [name=pd_kiri]').val(latestPrescription.pd_kiri || latestPrescription.pd || '');
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

