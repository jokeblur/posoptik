@extends('layouts.master')

@section('title')
    Manajemen Karyawan
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Karyawan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> Data Karyawan</h3>
                <div class="box-tools pull-right">
                    <button onclick="tambahKaryawan()" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Tambah Karyawan
                    </button>
                </div>
            </div>
            <div class="box-body">
                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select id="filterBranch" class="form-control" onchange="reloadTable()">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterStatus" class="form-control" onchange="reloadTable()">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabelKaryawan">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Cabang</th>
                                <th>No HP</th>
                                <th>Tgl Masuk</th>
                                <th>Gaji Pokok</th>
                                <th>Status</th>
                                <th width="160">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Karyawan -->
<div class="modal fade" id="modalKaryawan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title text-white"><i class="fa fa-user"></i> <span id="modalKaryawanTitle">Tambah Karyawan</span></h4>
            </div>
            <div class="modal-body">
                <form id="formKaryawan">
                    <input type="hidden" id="karyawanId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama <span class="text-danger">*</span></label>
                                <input type="text" id="fNama" class="form-control" placeholder="Nama lengkap karyawan">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jabatan <span class="text-danger">*</span></label>
                                <input type="text" id="fJabatan" class="form-control" placeholder="Jabatan / posisi">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cabang</label>
                                <select id="fBranch" class="form-control">
                                    <option value="">-- Pilih Cabang --</option>
                                    @foreach($branches as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" id="fNoHp" class="form-control" placeholder="No. Handphone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" id="fEmail" class="form-control" placeholder="Email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" id="fTglMasuk" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gaji Pokok <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="number" id="fGajiPokok" class="form-control" min="0" step="1000" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select id="fStatus" class="form-control">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Catatan</label>
                                <textarea id="fCatatan" class="form-control" rows="2" placeholder="Catatan tambahan"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanKaryawan()">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gaji Karyawan -->
<div class="modal fade" id="modalGaji" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title text-white"><i class="fa fa-money"></i> Gaji & Bonus – <span id="namaKaryawanGaji"></span></h4>
            </div>
            <div class="modal-body">
                <!-- Form Input Gaji -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Input Gaji / Bonus</h3>
                    </div>
                    <div class="box-body">
                        <input type="hidden" id="gajiKaryawanId">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Bulan <span class="text-danger">*</span></label>
                                    <select id="gBulan" class="form-control">
                                        <option value="1">Januari</option><option value="2">Februari</option>
                                        <option value="3">Maret</option><option value="4">April</option>
                                        <option value="5">Mei</option><option value="6">Juni</option>
                                        <option value="7">Juli</option><option value="8">Agustus</option>
                                        <option value="9">September</option><option value="10">Oktober</option>
                                        <option value="11">November</option><option value="12">Desember</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tahun <span class="text-danger">*</span></label>
                                    <input type="number" id="gTahun" class="form-control" value="{{ date('Y') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Gaji Pokok <span class="text-danger">*</span></label>
                                    <input type="number" id="gGajiPokok" class="form-control" min="0" step="1000" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Bonus</label>
                                    <input type="number" id="gBonus" class="form-control" min="0" step="1000" placeholder="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Tunjangan</label>
                                    <input type="number" id="gTunjangan" class="form-control" min="0" step="1000" placeholder="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Potongan</label>
                                    <input type="number" id="gPotongan" class="form-control" min="0" step="1000" placeholder="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Keterangan</label>
                                    <input type="text" id="gKeterangan" class="form-control" placeholder="Keterangan">
                                </div>
                            </div>
                            <div class="col-md-12" style="margin-top: 5px;">
                                <button type="button" class="btn btn-success" onclick="simpanGaji()">
                                    <i class="fa fa-save"></i> Simpan Gaji
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Gaji -->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Riwayat Gaji</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed" id="tabelGaji">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Periode</th>
                                        <th>Gaji Pokok</th>
                                        <th>Bonus</th>
                                        <th>Tunjangan</th>
                                        <th>Potongan</th>
                                        <th>Total</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var tableKaryawan, tableGaji;
var currentKaryawanId = null;

$(function () {
    initTableKaryawan();

    // Set bulan sekarang
    $('#gBulan').val({{ date('n') }});
});

function initTableKaryawan() {
    tableKaryawan = $('#tabelKaryawan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("karyawan.data") }}',
            data: function (d) {
                d.branch_id = $('#filterBranch').val();
                d.status    = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama' },
            { data: 'jabatan' },
            { data: 'branch_name' },
            { data: 'no_hp', defaultContent: '-' },
            { data: 'tanggal_masuk' },
            { data: 'gaji_pokok_fmt' },
            { data: 'status_badge' },
            { data: 'aksi', orderable: false, searchable: false },
        ]
    });
}

function reloadTable() {
    if (tableKaryawan) tableKaryawan.ajax.reload();
}

function tambahKaryawan() {
    $('#karyawanId').val('');
    $('#formKaryawan')[0].reset();
    $('#modalKaryawanTitle').text('Tambah Karyawan');
    $('#modalKaryawan').modal('show');
}

function editKaryawan(id) {
    $.get('/karyawan/' + id, function (data) {
        $('#karyawanId').val(data.id);
        $('#fNama').val(data.nama);
        $('#fJabatan').val(data.jabatan);
        $('#fBranch').val(data.branch_id);
        $('#fNoHp').val(data.no_hp);
        $('#fEmail').val(data.email);
        $('#fTglMasuk').val(data.tanggal_masuk ? data.tanggal_masuk.substring(0, 10) : '');
        $('#fGajiPokok').val(data.gaji_pokok);
        $('#fStatus').val(data.status);
        $('#fCatatan').val(data.catatan);
        $('#modalKaryawanTitle').text('Edit Karyawan');
        $('#modalKaryawan').modal('show');
    });
}

function simpanKaryawan() {
    var id   = $('#karyawanId').val();
    var url  = id ? '/karyawan/' + id : '/karyawan';
    var method = id ? 'PUT' : 'POST';

    var data = {
        _token:        '{{ csrf_token() }}',
        _method:       method,
        nama:          $('#fNama').val(),
        jabatan:       $('#fJabatan').val(),
        branch_id:     $('#fBranch').val(),
        no_hp:         $('#fNoHp').val(),
        email:         $('#fEmail').val(),
        tanggal_masuk: $('#fTglMasuk').val(),
        gaji_pokok:    $('#fGajiPokok').val(),
        status:        $('#fStatus').val(),
        catatan:       $('#fCatatan').val(),
    };

    $.post(url, data)
        .done(function (res) {
            if (res.success) {
                $('#modalKaryawan').modal('hide');
                tableKaryawan.ajax.reload();
                toastr.success(res.message);
            }
        })
        .fail(function (xhr) {
            var errors = xhr.responseJSON && xhr.responseJSON.errors;
            if (errors) {
                var msg = Object.values(errors).map(e => e[0]).join('\n');
                toastr.error(msg);
            } else {
                toastr.error('Terjadi kesalahan');
            }
        });
}

function hapusKaryawan(id) {
    if (!confirm('Hapus karyawan ini?')) return;
    $.ajax({
        url:  '/karyawan/' + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (res) {
            if (res.success) {
                tableKaryawan.ajax.reload();
                toastr.success(res.message);
            }
        }
    });
}

// ---- GAJI ----

function showGaji(karyawanId, nama) {
    currentKaryawanId = karyawanId;
    $('#namaKaryawanGaji').text(nama);
    $('#gajiKaryawanId').val(karyawanId);

    // Isi gaji pokok dari data karyawan
    $.get('/karyawan/' + karyawanId, function (data) {
        $('#gGajiPokok').val(data.gaji_pokok);
    });

    // Init/reload tabel riwayat gaji
    if (tableGaji) {
        tableGaji.ajax.url('/karyawan/' + karyawanId + '/gaji/data').load();
    } else {
        tableGaji = $('#tabelGaji').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/karyawan/' + karyawanId + '/gaji/data',
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'periode' },
                { data: 'gaji_pokok_fmt' },
                { data: 'bonus_fmt' },
                { data: 'tunjangan_fmt' },
                { data: 'potongan_fmt' },
                { data: 'total_fmt' },
                { data: 'keterangan', defaultContent: '-' },
                { data: 'aksi', orderable: false, searchable: false },
            ]
        });
    }

    $('#modalGaji').modal('show');
}

function simpanGaji() {
    var karyawanId = $('#gajiKaryawanId').val();
    $.post('/karyawan/' + karyawanId + '/gaji', {
        _token:      '{{ csrf_token() }}',
        bulan:       $('#gBulan').val(),
        tahun:       $('#gTahun').val(),
        gaji_pokok:  $('#gGajiPokok').val(),
        bonus:       $('#gBonus').val() || 0,
        tunjangan:   $('#gTunjangan').val() || 0,
        potongan:    $('#gPotongan').val() || 0,
        keterangan:  $('#gKeterangan').val(),
    })
    .done(function (res) {
        if (res.success) {
            tableGaji.ajax.reload();
            toastr.success(res.message);
            $('#gBonus').val(0);
            $('#gTunjangan').val(0);
            $('#gPotongan').val(0);
            $('#gKeterangan').val('');
        }
    })
    .fail(function (xhr) {
        var errors = xhr.responseJSON && xhr.responseJSON.errors;
        if (errors) {
            toastr.error(Object.values(errors).map(e => e[0]).join('\n'));
        } else {
            toastr.error('Terjadi kesalahan');
        }
    });
}

function hapusGaji(id) {
    if (!confirm('Hapus data gaji ini?')) return;
    $.ajax({
        url:  '/karyawan/gaji/' + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (res) {
            if (res.success) {
                tableGaji.ajax.reload();
                toastr.success(res.message);
            }
        }
    });
}
</script>
@endpush
