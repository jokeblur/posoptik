@extends('layouts.master')

@section('title')
    Manajemen Keuangan
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Keuangan</li>
@endsection

@section('content')

<!-- Summary Cards -->
<div class="row" id="summaryCards">
    <div class="col-md-4">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pemasukan</span>
                <span class="info-box-number" id="totalPemasukan">Rp 0</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pengeluaran</span>
                <span class="info-box-number" id="totalPengeluaran">Rp 0</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-balance-scale"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Saldo</span>
                <span class="info-box-number" id="totalSaldo">Rp 0</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-book"></i> Catatan Keuangan</h3>
                <div class="box-tools pull-right">
                    <button onclick="tambahKeuangan()" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Tambah
                    </button>
                </div>
            </div>
            <div class="box-body">
                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    @if(auth()->user()->isSuperAdmin())
                    <div class="col-md-2">
                        <select id="filterBranch" class="form-control" onchange="reloadTable()">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <select id="filterJenis" class="form-control" onchange="reloadTable()">
                            <option value="">Semua Jenis</option>
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterBulan" class="form-control" onchange="loadSummary()">
                            <option value="">Semua Bulan</option>
                            <option value="1">Januari</option><option value="2">Februari</option>
                            <option value="3">Maret</option><option value="4">April</option>
                            <option value="5">Mei</option><option value="6">Juni</option>
                            <option value="7">Juli</option><option value="8">Agustus</option>
                            <option value="9">September</option><option value="10">Oktober</option>
                            <option value="11">November</option><option value="12">Desember</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterTahun" class="form-control" onchange="loadSummary()">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="filterDari" class="form-control" placeholder="Dari" onchange="reloadTable()">
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="filterSampai" class="form-control" placeholder="Sampai" onchange="reloadTable()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tabelKeuangan">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Cabang</th>
                                <th>Keterangan</th>
                                <th>Dicatat Oleh</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Keuangan -->
<div class="modal fade" id="modalKeuangan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title text-white"><i class="fa fa-book"></i> <span id="modalKeuanganTitle">Tambah Catatan Keuangan</span></h4>
            </div>
            <div class="modal-body">
                <form id="formKeuangan">
                    <input type="hidden" id="keuanganId">
                    <div class="form-group">
                        <label>Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="kTanggal" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Jenis <span class="text-danger">*</span></label>
                        <select id="kJenis" class="form-control">
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <input type="text" id="kKategori" class="form-control" placeholder="Contoh: Gaji, Sewa, Pembelian Stok, dll." list="kategoriList">
                        <datalist id="kategoriList">
                            <option value="Gaji Karyawan">
                            <option value="Bonus Karyawan">
                            <option value="Sewa Tempat">
                            <option value="Listrik">
                            <option value="Air">
                            <option value="Pembelian Stok">
                            <option value="Pemasukan Penjualan">
                            <option value="Lain-lain">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Jumlah <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="number" id="kJumlah" class="form-control" min="0" step="1000" placeholder="0">
                        </div>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                    <div class="form-group">
                        <label>Cabang</label>
                        <select id="kBranch" class="form-control">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea id="kKeterangan" class="form-control" rows="2" placeholder="Keterangan tambahan"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanKeuangan()">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var tableKeuangan;

$(function () {
    initTableKeuangan();

    // Set bulan dan tahun default
    $('#filterBulan').val({{ date('n') }});
    loadSummary();
});

function initTableKeuangan() {
    tableKeuangan = $('#tabelKeuangan').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'desc']],
        ajax: {
            url: '{{ route("keuangan.data") }}',
            data: function (d) {
                d.branch_id = $('#filterBranch').val();
                d.jenis     = $('#filterJenis').val();
                d.dari      = $('#filterDari').val();
                d.sampai    = $('#filterSampai').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'tanggal_fmt' },
            { data: 'jenis_badge' },
            { data: 'kategori' },
            { data: 'jumlah_fmt' },
            { data: 'branch_name' },
            { data: 'keterangan', defaultContent: '-' },
            { data: 'created_by_name' },
            { data: 'aksi', orderable: false, searchable: false },
        ]
    });
}

function reloadTable() {
    if (tableKeuangan) tableKeuangan.ajax.reload();
    loadSummary();
}

function loadSummary() {
    var params = {
        branch_id: $('#filterBranch').val() || '',
        bulan:     $('#filterBulan').val() || '',
        tahun:     $('#filterTahun').val() || '',
    };
    $.get('{{ route("keuangan.summary") }}', params, function (res) {
        $('#totalPemasukan').text('Rp ' + formatAngka(res.pemasukan));
        $('#totalPengeluaran').text('Rp ' + formatAngka(res.pengeluaran));
        var saldo = res.saldo;
        var saldoEl = $('#totalSaldo');
        saldoEl.text((saldo < 0 ? '-' : '') + 'Rp ' + formatAngka(Math.abs(saldo)));
        saldoEl.closest('.info-box').removeClass('bg-aqua bg-red').addClass(saldo < 0 ? 'bg-red' : 'bg-aqua');
    });
}

function formatAngka(n) {
    return parseInt(n).toLocaleString('id-ID');
}

function tambahKeuangan() {
    $('#keuanganId').val('');
    $('#formKeuangan')[0].reset();
    $('#kTanggal').val(new Date().toISOString().substring(0, 10));
    $('#modalKeuanganTitle').text('Tambah Catatan Keuangan');
    $('#modalKeuangan').modal('show');
}

function editKeuangan(id) {
    $.get('/keuangan/' + id, function (data) {
        $('#keuanganId').val(data.id);
        $('#kTanggal').val(data.tanggal ? data.tanggal.substring(0, 10) : '');
        $('#kJenis').val(data.jenis);
        $('#kKategori').val(data.kategori);
        $('#kJumlah').val(data.jumlah);
        $('#kBranch').val(data.branch_id);
        $('#kKeterangan').val(data.keterangan);
        $('#modalKeuanganTitle').text('Edit Catatan Keuangan');
        $('#modalKeuangan').modal('show');
    });
}

function simpanKeuangan() {
    var id     = $('#keuanganId').val();
    var url    = id ? '/keuangan/' + id : '/keuangan';
    var method = id ? 'PUT' : 'POST';

    var data = {
        _token:     '{{ csrf_token() }}',
        _method:    method,
        tanggal:    $('#kTanggal').val(),
        jenis:      $('#kJenis').val(),
        kategori:   $('#kKategori').val(),
        jumlah:     $('#kJumlah').val(),
        branch_id:  $('#kBranch').val() || '',
        keterangan: $('#kKeterangan').val(),
    };

    $.post(url, data)
        .done(function (res) {
            if (res.success) {
                $('#modalKeuangan').modal('hide');
                tableKeuangan.ajax.reload();
                loadSummary();
                toastr.success(res.message);
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

function hapusKeuangan(id) {
    if (!confirm('Hapus catatan keuangan ini?')) return;
    $.ajax({
        url:  '/keuangan/' + id,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (res) {
            if (res.success) {
                tableKeuangan.ajax.reload();
                loadSummary();
                toastr.success(res.message);
            }
        }
    });
}
</script>
@endpush
