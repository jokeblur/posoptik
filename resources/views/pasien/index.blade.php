@extends('layouts.master')

@section('title')
    pasien
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
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stipet table-bordered" id="table">
                    <thead>
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
    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('pasien.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'nama_pasien'},
                {data: 'alamat'},
                {data: 'nohp'},
                {data: 'service_type'},
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

    function showDetail(url) {
        $('#modal-detail').modal('show');

        $.get(url)
            .done((response) => {
                $('#detail-nama').text(response.nama_pasien);
                $('#detail-alamat').text(response.alamat);
                $('#detail-nohp').text(response.nohp);
                $('#detail-service_type').text(response.service_type);

                let prescriptionsContainer = $('#detail-prescriptions-container');
                prescriptionsContainer.empty(); // Clear previous data

                if (response.prescriptions && response.prescriptions.length > 0) {
                    response.prescriptions.forEach(function(rx) {
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
                alert('Tidak dapat menampilkan data detail.');
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

                if (response.prescriptions && response.prescriptions.length > 0) {
                    // Assuming we edit the latest prescription
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
                }
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

