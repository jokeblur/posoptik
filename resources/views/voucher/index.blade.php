@extends('layouts.master')

@section('title', 'Voucher')

@section('breadcrumb')
    @parent
    <li class="active">Voucher</li>
@endsection

@section('content')
@if ($canManage)
<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-money"></i> Dana Voucher</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-success btn-sm btn-dana" data-jenis="isi">
                        <i class="fa fa-plus"></i> Isi Dana
                    </button>
                    <button type="button" class="btn btn-default btn-sm btn-dana" data-jenis="tarik">
                        <i class="fa fa-minus"></i> Tarik Dana
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="info-box" style="margin-bottom:10px;">
                            <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Dana Tersedia</span>
                                <span class="info-box-number" style="font-size:22px;">{{ $dana->saldoLabel() }}</span>
                                <small class="text-muted">Siap dipakai untuk voucher uang baru / tambah saldo</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-box" style="margin-bottom:10px;">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-ticket"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Saldo di Voucher</span>
                                <span class="info-box-number" style="font-size:22px;">Rp {{ number_format($saldoBeredar, 0, ',', '.') }}</span>
                                <small class="text-muted">Total saldo voucher uang yang belum dipakai</small>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="#danaRiwayat" data-toggle="collapse"><i class="fa fa-history"></i> Riwayat dana (10 terakhir)</a>
                <div id="danaRiwayat" class="collapse" style="margin-top:8px;">
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered" style="margin-bottom:0;">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th class="text-right">Jumlah</th>
                                    <th class="text-right">Sisa Dana</th>
                                    <th>Voucher</th>
                                    <th>Keterangan</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($danaLogs as $log)
                                    <tr>
                                        <td>{{ $log->created_at ? $log->created_at->format('d-m-Y H:i') : '-' }}</td>
                                        <td>{{ \App\Models\VoucherDanaLog::JENIS_LABEL[$log->jenis] ?? $log->jenis }}</td>
                                        <td class="text-right {{ $log->isMasuk() ? 'text-success' : 'text-danger' }}">
                                            {{ $log->isMasuk() ? '+' : '-' }}Rp {{ number_format($log->jumlah, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right">Rp {{ number_format($log->saldo_sesudah, 0, ',', '.') }}</td>
                                        <td>{{ $log->voucher_kode ?: '-' }}</td>
                                        <td>{{ $log->keterangan ?: '-' }}</td>
                                        <td>{{ optional($log->user)->name ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">Belum ada riwayat dana.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-ticket"></i> Data Voucher</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#cekVoucherModal">
                        <i class="fa fa-qrcode"></i> Cek Voucher
                    </button>
                    @if ($canManage)
                        <a href="{{ route('voucher.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Buat Voucher
                        </a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Voucher</th>
                                <th>Nominal</th>
                                <th>Saldo</th>
                                <th>Berlaku</th>
                                <th>Syarat dan Ketentuan</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Dibuat Pada</th>
                                @if ($canManage)
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vouchers as $index => $voucher)
                                @php
                                    $status = $voucher->statusInfo();
                                    $statusClass = [
                                        'aktif' => 'success',
                                        'terpakai' => 'primary',
                                        'saldo_habis' => 'primary',
                                        'nonaktif' => 'default',
                                        'kadaluarsa' => 'danger',
                                        'belum_berlaku' => 'warning',
                                    ][$status['key']] ?? 'default';
                                @endphp
                                <tr>
                                    <td>{{ $vouchers->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $voucher->kode }}</strong>
                                        @if ($voucher->batch_kode)
                                            <br><small class="text-muted">Batch {{ $voucher->batch_kode }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $voucher->nominalLabel() }}</td>
                                    <td style="white-space: nowrap;">
                                        @if ($voucher->isDiskon())
                                            <span class="text-muted">-</span>
                                        @elseif ($voucher->penjualans_count > 0)
                                            <span class="text-muted">Rp 0</span>
                                            @if ((float) $voucher->saldo > 0)
                                                <br><small class="text-muted">Sisa {{ $voucher->saldoLabel() }} hangus</small>
                                            @endif
                                        @else
                                            <strong class="{{ (float) $voucher->saldo > 0 ? 'text-success' : 'text-danger' }}">{{ $voucher->saldoLabel() }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $voucher->berlaku_mulai ? $voucher->berlaku_mulai->format('d-m-Y') : '-' }}
                                        s/d
                                        {{ $voucher->berlaku_sampai ? $voucher->berlaku_sampai->format('d-m-Y') : '-' }}
                                    </td>
                                    <td style="white-space: pre-line; max-width: 260px;">{{ $voucher->syarat_ketentuan ?: '-' }}</td>
                                    <td><span class="label label-{{ $statusClass }}">{{ $status['key'] === 'aktif' ? 'Aktif' : $status['label'] }}</span></td>
                                    <td>{{ optional($voucher->creator)->name ?: '-' }}</td>
                                    <td>{{ $voucher->created_at ? $voucher->created_at->format('d-m-Y H:i') : '-' }}</td>
                                    @if ($canManage)
                                        <td style="white-space: nowrap;">
                                            @unless ($voucher->isDiskon() || $voucher->penjualans_count > 0)
                                                <button type="button" class="btn btn-xs btn-warning btn-tambah-saldo" title="Tambah saldo"
                                                    data-url="{{ route('voucher.saldo', $voucher) }}"
                                                    data-kode="{{ $voucher->kode }}"
                                                    data-saldo="{{ $voucher->saldoLabel() }}">
                                                    <i class="fa fa-plus-circle"></i> Saldo
                                                </button>
                                            @endunless
                                            <a href="{{ route('voucher.print', $voucher) }}" target="_blank" class="btn btn-xs btn-success" title="Cetak voucher ini"><i class="fa fa-print"></i></a>
                                            @if ($voucher->batch_kode)
                                                <a href="{{ route('voucher.print', ['voucher' => $voucher, 'semua' => 1]) }}" target="_blank" class="btn btn-xs btn-success" title="Cetak semua voucher batch {{ $voucher->batch_kode }}"><i class="fa fa-print"></i> Semua</a>
                                            @endif
                                            <a href="{{ route('voucher.edit', $voucher) }}" class="btn btn-xs btn-info" title="Edit"><i class="fa fa-pencil"></i></a>
                                            <form action="{{ route('voucher.destroy', $voucher) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus voucher ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $canManage ? 10 : 9 }}" class="text-center">Belum ada voucher.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center">{{ $vouchers->links() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cekVoucherModal" tabindex="-1" role="dialog" aria-labelledby="cekVoucherTitle">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="cekVoucherTitle"><i class="fa fa-search"></i> Cek Voucher</h4>
            </div>
            <div class="modal-body">
                <form id="cekVoucherForm" autocomplete="off">
                    <label for="cekVoucherKode">Kode Voucher</label>
                    <div class="input-group">
                        <input type="text" id="cekVoucherKode" class="form-control" placeholder="Ketik kode atau scan QR" style="text-transform:uppercase;">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary" id="cekVoucherSubmit"><i class="fa fa-search"></i> Cek</button>
                            <button type="button" class="btn btn-success" id="cekVoucherScanBtn"><i class="fa fa-camera"></i> Scan QR</button>
                        </span>
                    </div>
                    <small class="text-muted">Scanner barcode USB juga bisa langsung dipakai di kolom ini.</small>
                </form>

                <div id="cekVoucherScanner" style="display:none; margin-top:12px;">
                    <div id="cekVoucherReader" style="width:100%; max-width:360px; margin:0 auto;"></div>
                    <div class="text-center" style="margin-top:8px;">
                        <button type="button" class="btn btn-default btn-sm" id="cekVoucherStopScan"><i class="fa fa-stop"></i> Stop Kamera</button>
                    </div>
                </div>

                <div id="cekVoucherResult" style="margin-top:15px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@if ($canManage)
<div class="modal fade" id="tambahSaldoModal" tabindex="-1" role="dialog" aria-labelledby="tambahSaldoTitle">
    <div class="modal-dialog modal-sm" role="document">
        <form method="POST" id="tambahSaldoForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="tambahSaldoTitle"><i class="fa fa-plus-circle"></i> Tambah Saldo</h4>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:10px;">
                    Voucher <strong id="tambahSaldoKode"></strong><br>
                    Saldo sekarang: <strong id="tambahSaldoSekarang"></strong><br>
                    <small class="text-muted">Diambil dari dana voucher (tersedia {{ $dana->saldoLabel() }})</small>
                </p>
                <div class="form-group">
                    <label for="tambahSaldoJumlah">Jumlah Tambahan (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah" id="tambahSaldoJumlah" class="form-control" min="1" step="1" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="tambahSaldoKeterangan">Keterangan</label>
                    <input type="text" name="keterangan" id="tambahSaldoKeterangan" class="form-control" maxlength="255" placeholder="Opsional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Tambah</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="danaModal" tabindex="-1" role="dialog" aria-labelledby="danaTitle">
    <div class="modal-dialog modal-sm" role="document">
        <form method="POST" action="{{ route('voucher.dana') }}" class="modal-content">
            @csrf
            <input type="hidden" name="jenis" id="danaJenis" value="isi">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="danaTitle">Isi Dana Voucher</h4>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:10px;">Dana tersedia: <strong>{{ $dana->saldoLabel() }}</strong></p>
                <div class="form-group">
                    <label for="danaJumlah">Jumlah (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah" id="danaJumlah" class="form-control" min="1" step="1" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="danaKeterangan">Keterangan</label>
                    <input type="text" name="keterangan" id="danaKeterangan" class="form-control" maxlength="255" placeholder="Opsional, mis. anggaran promo Oktober">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="danaSubmit"><i class="fa fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        $(function () {
            const checkUrl = @json(route('voucher.check'));
            const $modal = $('#cekVoucherModal');
            const $form = $('#cekVoucherForm');
            const $input = $('#cekVoucherKode');
            const $submit = $('#cekVoucherSubmit');
            const $scanBtn = $('#cekVoucherScanBtn');
            const $scanner = $('#cekVoucherScanner');
            const $result = $('#cekVoucherResult');
            let html5QrCode = null;
            let isScanning = false;

            function escapeHtml(value) {
                return $('<div>').text(value == null ? '' : String(value)).html();
            }

            function showAlert(type, message) {
                $result.html('<div class="alert alert-' + type + '" style="margin-bottom:0;">' + escapeHtml(message) + '</div>');
            }

            function renderVoucher(data) {
                const v = data.voucher;
                const s = data.status;
                const alertType = s.valid ? 'success' : (['belum_berlaku', 'terpakai', 'saldo_habis'].includes(s.key) ? 'warning' : 'danger');
                const pakai = data.dipakai_di;
                const pakaiRow = pakai
                    ? '<tr><th>' + (v.jenis_nominal === 'diskon' ? 'Dipakai di Transaksi' : 'Terakhir Dipakai') + '</th><td><a href="' + escapeHtml(pakai.url) + '">' + escapeHtml(pakai.kode_penjualan) + '</a>'
                        + ' (' + escapeHtml(pakai.tanggal) + ') &mdash; potongan ' + escapeHtml(pakai.potongan_label) + '</td></tr>'
                    : '';
                const saldoRow = v.jenis_nominal === 'diskon'
                    ? ''
                    : '<tr><th>Sisa Saldo</th><td><strong style="font-size:16px;" class="' + (v.saldo > 0 ? 'text-success' : 'text-danger') + '">'
                        + escapeHtml(v.saldo_label) + '</strong> <small class="text-muted">(dipakai ' + escapeHtml(v.jumlah_pemakaian) + 'x)</small></td></tr>';
                const icon = s.valid ? 'fa-check-circle' : 'fa-times-circle';

                let riwayatHtml = '';
                if (data.riwayat && data.riwayat.length) {
                    riwayatHtml = '<h5 style="margin-top:15px;"><strong>Riwayat Saldo</strong> <small>(10 terakhir)</small></h5>'
                        + '<div class="table-responsive"><table class="table table-condensed table-striped" style="margin-bottom:0; font-size:12px;">'
                        + '<tr><th>Tanggal</th><th>Jenis</th><th class="text-right">Jumlah</th><th class="text-right">Saldo</th><th>Ket.</th></tr>'
                        + data.riwayat.map(function (r) {
                            const cls = r.jenis === 'pakai' ? 'text-danger' : 'text-success';
                            return '<tr><td>' + escapeHtml(r.tanggal) + '</td><td>' + escapeHtml(r.jenis_label) + '</td>'
                                + '<td class="text-right ' + cls + '">' + escapeHtml(r.jumlah_label) + '</td>'
                                + '<td class="text-right">' + escapeHtml(r.saldo_label) + '</td>'
                                + '<td>' + escapeHtml(r.keterangan) + '<br><small class="text-muted">' + escapeHtml(r.oleh) + '</small></td></tr>';
                        }).join('')
                        + '</table></div>';
                }

                $result.html(
                    '<div class="alert alert-' + alertType + '" style="font-size:16px;">'
                    + '<i class="fa ' + icon + '"></i> <strong>' + escapeHtml(v.kode) + '</strong> &mdash; ' + escapeHtml(s.label)
                    + '</div>'
                    + '<table class="table table-condensed table-bordered" style="margin-bottom:0;">'
                    + '<tr><th style="width:40%;">Nominal</th><td><strong>' + escapeHtml(v.nominal_label) + '</strong>'
                    + (v.jenis_nominal === 'diskon' ? ' (Diskon)' : ' (Uang Tunai)') + '</td></tr>'
                    + saldoRow
                    + '<tr><th>Berlaku</th><td>' + escapeHtml(v.berlaku_mulai) + ' s/d ' + escapeHtml(v.berlaku_sampai) + '</td></tr>'
                    + '<tr><th>Syarat dan Ketentuan</th><td style="white-space:pre-line;">' + escapeHtml(v.syarat_ketentuan) + '</td></tr>'
                    + '<tr><th>Dibuat Oleh</th><td>' + escapeHtml(v.dibuat_oleh) + '</td></tr>'
                    + pakaiRow
                    + '</table>'
                    + riwayatHtml
                    + (v.edit_url ? '<div class="text-right" style="margin-top:8px;"><a href="' + escapeHtml(v.edit_url) + '" class="btn btn-xs btn-info"><i class="fa fa-pencil"></i> Edit Voucher</a></div>' : '')
                );
            }

            function checkVoucher(kode) {
                kode = String(kode || '').trim().toUpperCase();
                if (!kode) {
                    showAlert('warning', 'Masukkan kode voucher terlebih dahulu.');
                    $input.focus();
                    return;
                }

                $input.val(kode);
                $submit.prop('disabled', true);
                $result.html('<div class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Mengecek voucher...</div>');

                $.ajax({
                    url: checkUrl,
                    method: 'GET',
                    data: { kode: kode },
                    dataType: 'json'
                }).done(function (data) {
                    renderVoucher(data);
                }).fail(function (xhr) {
                    const message = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengecek voucher. Coba lagi.';
                    showAlert(xhr.status === 404 ? 'danger' : 'warning', message);
                }).always(function () {
                    $submit.prop('disabled', false);
                    $input.select();
                });
            }

            async function stopScan() {
                if (html5QrCode && isScanning) {
                    try {
                        await html5QrCode.stop();
                        html5QrCode.clear();
                    } catch (e) {
                        console.warn('Gagal menghentikan kamera', e);
                    }
                }
                isScanning = false;
                $scanner.hide();
                $scanBtn.prop('disabled', false);
            }

            async function startScan() {
                if (typeof Html5Qrcode === 'undefined') {
                    showAlert('danger', 'Library scanner QR gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.');
                    return;
                }
                if (isScanning) {
                    return;
                }

                $result.empty();
                $scanner.show();
                $scanBtn.prop('disabled', true);
                html5QrCode = html5QrCode || new Html5Qrcode('cekVoucherReader');

                try {
                    await html5QrCode.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 220, height: 220 } },
                        function (decodedText) {
                            stopScan();
                            checkVoucher(decodedText);
                        },
                        function () {}
                    );
                    isScanning = true;
                } catch (err) {
                    $scanner.hide();
                    $scanBtn.prop('disabled', false);
                    showAlert('danger', 'Kamera tidak bisa dibuka. Pastikan izin kamera diberikan dan halaman dibuka lewat HTTPS atau localhost.');
                    console.error(err);
                }
            }

            $form.on('submit', function (e) {
                e.preventDefault();
                checkVoucher($input.val());
            });

            $scanBtn.on('click', startScan);
            $('#cekVoucherStopScan').on('click', stopScan);

            $modal.on('shown.bs.modal', function () {
                $input.val('').focus();
                $result.empty();
            });

            $modal.on('hidden.bs.modal', stopScan);

            $(document).on('click', '.btn-tambah-saldo', function () {
                const $btn = $(this);
                $('#tambahSaldoForm').attr('action', $btn.data('url'));
                $('#tambahSaldoKode').text($btn.data('kode'));
                $('#tambahSaldoSekarang').text($btn.data('saldo'));
                $('#tambahSaldoJumlah, #tambahSaldoKeterangan').val('');
                $('#tambahSaldoModal').modal('show');
            });

            $('#tambahSaldoModal').on('shown.bs.modal', function () {
                $('#tambahSaldoJumlah').focus();
            });

            $(document).on('click', '.btn-dana', function () {
                const isi = $(this).data('jenis') === 'isi';
                $('#danaJenis').val(isi ? 'isi' : 'tarik');
                $('#danaTitle').text(isi ? 'Isi Dana Voucher' : 'Tarik Dana Voucher');
                $('#danaSubmit').toggleClass('btn-success', isi).toggleClass('btn-danger', !isi);
                $('#danaJumlah, #danaKeterangan').val('');
                $('#danaModal').modal('show');
            });

            $('#danaModal').on('shown.bs.modal', function () {
                $('#danaJumlah').focus();
            });
        });
    </script>
@endpush
