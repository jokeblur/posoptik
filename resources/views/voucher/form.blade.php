@extends('layouts.master')

@section('title', $voucher->exists ? 'Edit Voucher' : 'Buat Voucher')

@section('breadcrumb')
    @parent
    <li><a href="{{ route('voucher.index') }}">Voucher</a></li>
    <li class="active">{{ $voucher->exists ? 'Edit' : 'Buat' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-ticket"></i> {{ $voucher->exists ? 'Edit Voucher' : 'Buat Voucher Baru' }}</h3>
            </div>
            <form method="POST" enctype="multipart/form-data" action="{{ $voucher->exists ? route('voucher.update', $voucher) : route('voucher.store') }}">
                @csrf
                @if ($voucher->exists)
                    @method('PUT')
                @endif
                <div class="box-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom:0; padding-left:20px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="kode">Kode Voucher <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="kode" id="kode" class="form-control" value="{{ old('kode', $voucher->kode) }}" maxlength="100" required style="text-transform:uppercase;">
                            <span class="input-group-btn" style="display:flex; gap:6px;">
                                <button type="button" id="generateKodeBtn" class="btn btn-warning" title="Generate kode otomatis">
                                    <i class="fa fa-random"></i> Generate
                                </button>
                                <button type="button" id="generateQrBtn" class="btn btn-success" title="Generate QR Code">
                                    <i class="fa fa-qrcode"></i> QR
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="form-group" id="qrPreviewWrapper" style="display:none;">
                        <label>Preview QR Code</label>
                        <div class="text-center" style="padding:12px; border:1px solid #ddd; background:#f9f9f9; border-radius:4px;">
                            <canvas id="voucherQrCanvas" width="200" height="200" style="max-width:100%;"></canvas>
                            <div id="voucherQrLabel" style="margin-top:6px; font-weight:bold; letter-spacing:1px;"></div>
                            <button type="button" id="downloadQrBtn" class="btn btn-default btn-sm" style="margin-top:8px;">
                                <i class="fa fa-download"></i> Download QR
                            </button>
                        </div>
                    </div>

                    @php
                        // Setelah dibuat, tipe terkunci; nilai voucher uang terkunci (pakai Tambah Saldo),
                        // persen voucher diskon masih bisa diubah selama belum dipakai.
                        $lockJenis = $voucher->exists;
                        $lockNominal = $voucher->exists && (!$voucher->isDiskon() || $voucher->jumlahPemakaian() > 0);
                    @endphp
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="jenis_nominal">Tipe Nominal <span class="text-danger">*</span></label>
                            <select name="jenis_nominal" id="jenis_nominal" class="form-control" required {{ $lockJenis ? 'disabled' : '' }}>
                                <option value="uang" {{ old('jenis_nominal', $voucher->jenis_nominal ?? 'uang') === 'uang' ? 'selected' : '' }}>Uang Tunai</option>
                                <option value="diskon" {{ old('jenis_nominal', $voucher->jenis_nominal ?? 'uang') === 'diskon' ? 'selected' : '' }}>Diskon</option>
                            </select>
                        </div>
                        <div class="form-group col-md-8">
                            <label for="nominal">Nominal Voucher <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon" id="nominalPrefix">Rp</span>
                                <input type="number" name="nominal" id="nominal" class="form-control" value="{{ old('nominal', $voucher->nominal) }}" min="0" step="1000" required {{ $lockNominal ? 'readonly' : '' }}>
                            </div>
                            <small class="text-muted" id="nominalHint">Nominal diisi manual, contoh: 50000.</small>
                            @unless ($voucher->exists)
                                <div id="danaInfo" style="margin-top:4px;">
                                    <i class="fa fa-money"></i> Dana voucher tersedia:
                                    <strong class="{{ (float) $dana->saldo > 0 ? 'text-success' : 'text-danger' }}">{{ $dana->saldoLabel() }}</strong>
                                    <small class="text-muted">(saldo awal voucher diambil dari dana ini; isi dana di halaman Voucher)</small>
                                </div>
                            @endunless
                            @if ($voucher->exists && !$voucher->isDiskon())
                                <div class="text-info" style="margin-top:4px;">
                                    <i class="fa fa-info-circle"></i> Nominal mengikuti kolom <strong>Saldo Voucher</strong> di bawah.
                                </div>
                            @elseif ($lockNominal)
                                <div class="text-info" style="margin-top:4px;"><i class="fa fa-info-circle"></i> Voucher sudah dipakai, persen diskon tidak bisa diubah.</div>
                            @endif
                        </div>
                    </div>
                    @unless ($voucher->exists)
                        <div class="form-group">
                            <label for="jumlah_voucher">Jumlah Voucher</label>
                            <input type="number" name="jumlah_voucher" id="jumlah_voucher" class="form-control" value="{{ old('jumlah_voucher', 1) }}" min="1" max="100" step="1" style="max-width:160px;">
                            <small class="text-muted">Lebih dari 1: tiap voucher dapat kode berbeda (KODE-001, KODE-002, ...) agar satu kode hanya untuk satu orang.</small>
                            <div id="totalDanaInfo" style="margin-top:4px;"></div>
                        </div>
                    @endunless
                    @if ($voucher->exists && !$voucher->isDiskon())
                        @php $saldoTerkunci = $voucher->jumlahPemakaian() > 0; @endphp
                        <div class="form-group">
                            <label for="saldo">Saldo Voucher</label>
                            <div class="input-group" style="max-width:320px;">
                                <span class="input-group-addon">Rp</span>
                                <input type="number" name="saldo" id="saldo" class="form-control" value="{{ old('saldo', round((float) $voucher->saldo)) }}" min="0" step="1" {{ $saldoTerkunci ? 'readonly' : '' }}>
                            </div>
                            @if ($saldoTerkunci)
                                <small class="text-muted">Voucher sudah dipakai, saldo tidak bisa diubah.</small>
                            @else
                                <small class="text-muted">
                                    Saldo sekarang {{ $voucher->saldoLabel() }}. Dinaikkan: selisih diambil dari dana voucher
                                    (tersedia {{ $dana->saldoLabel() }}). Diturunkan: selisih kembali ke dana voucher.
                                </small>
                                <div id="saldoSelisihInfo" style="margin-top:4px;"></div>
                            @endif
                        </div>
                    @endif
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="berlaku_mulai">Berlaku Mulai</label>
                            <input type="date" name="berlaku_mulai" id="berlaku_mulai" class="form-control" value="{{ old('berlaku_mulai', optional($voucher->berlaku_mulai)->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="berlaku_sampai">Berlaku Sampai</label>
                            <input type="date" name="berlaku_sampai" id="berlaku_sampai" class="form-control" value="{{ old('berlaku_sampai', optional($voucher->berlaku_sampai)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="syarat_ketentuan">Syarat dan Ketentuan</label>
                        <textarea name="syarat_ketentuan" id="syarat_ketentuan" class="form-control" rows="6" maxlength="5000" placeholder="Contoh:\n- Berlaku untuk pembelian minimal Rp500.000\n- Tidak dapat diuangkan\n- Tidak dapat digabung dengan promo lain">{{ old('syarat_ketentuan', $voucher->syarat_ketentuan) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Desain Voucher</label>
                        <p class="text-muted" style="margin-bottom:8px;">
                            Opsional. Gambar JPG/PNG/WEBP maks. 5 MB, sebaiknya rasio 15 x 7 cm (mis. 1772 x 827 px).
                            Saat cetak, desain jadi latar voucher dan semua tulisan voucher (kode, nominal, masa berlaku, syarat) tetap dicetak di atasnya.
                            @if ($voucher->exists && $voucher->batch_kode)
                                Perubahan desain berlaku untuk semua voucher batch {{ $voucher->batch_kode }}.
                            @endif
                        </p>
                        <div class="row">
                            @foreach (['desain_depan' => 'Depan', 'desain_belakang' => 'Belakang'] as $kolom => $label)
                                @php $desainUri = $voucher->exists ? $voucher->desainDataUri($kolom === 'desain_belakang' ? 'belakang' : 'depan') : null; @endphp
                                <div class="col-sm-6" style="margin-bottom:10px;">
                                    <label class="btn btn-default btn-block" style="margin-bottom:6px;">
                                        <i class="fa fa-upload"></i> Upload Desain {{ $label }}
                                        <input type="file" name="{{ $kolom }}" accept="image/jpeg,image/png,image/webp" class="desain-input" data-preview="#preview_{{ $kolom }}" style="display:none;">
                                    </label>
                                    <div id="preview_{{ $kolom }}" style="border:1px dashed #ccc; border-radius:4px; aspect-ratio:15/7; display:flex; align-items:center; justify-content:center; overflow:hidden; background:#fafafa;">
                                        @if ($desainUri)
                                            <img src="{{ $desainUri }}" alt="Desain {{ $label }}" style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            <span class="text-muted">Belum ada desain {{ strtolower($label) }}</span>
                                        @endif
                                    </div>
                                    @if ($desainUri)
                                        <div class="checkbox" style="margin:4px 0 0;">
                                            <label><input type="checkbox" name="hapus_{{ $kolom }}" value="1"> Hapus desain {{ strtolower($label) }}</label>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="aktif" value="1" {{ old('aktif', $voucher->exists ? $voucher->aktif : true) ? 'checked' : '' }}>
                            Voucher aktif
                        </label>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('voucher.index') }}" class="btn btn-default">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.5.3/qrcode.min.js"></script>
    <script>
        $(function () {
            const $kodeInput = $('#kode');
            const $generateBtn = $('#generateKodeBtn');
            const $generateQrBtn = $('#generateQrBtn');
            const $qrPreviewWrapper = $('#qrPreviewWrapper');
            const $canvas = $('#voucherQrCanvas');
            const $qrCodeLabel = $('#voucherQrLabel');
            const $downloadQrBtn = $('#downloadQrBtn');
            const $jenisNominal = $('#jenis_nominal');
            const $nominalPrefix = $('#nominalPrefix');
            const $nominalHint = $('#nominalHint');
            const $nominalInput = $('#nominal');

            function generateQrCodeFromText(text) {
                if (typeof window.qrcode !== 'function' || !$canvas.length) {
                    console.warn('QR library not loaded');
                    alert('Library QR Code gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.');
                    return false;
                }

                const qr = window.qrcode(0, 'M');
                qr.addData(text || '');
                qr.make();

                // Quiet zone 4 modul wajib agar QR bisa dibaca scanner.
                const moduleCount = qr.getModuleCount();
                const quietZone = 4;
                const totalModules = moduleCount + quietZone * 2;
                const displaySize = 200;
                const dpr = window.devicePixelRatio || 1;
                const cell = Math.max(1, Math.floor((displaySize * dpr) / totalModules));
                const size = cell * totalModules;

                const canvas = $canvas[0];
                canvas.width = size;
                canvas.height = size;
                canvas.style.width = displaySize + 'px';
                canvas.style.height = displaySize + 'px';
                canvas.style.imageRendering = 'pixelated';

                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, size, size);
                ctx.fillStyle = '#000000';

                const offset = quietZone * cell;
                for (let row = 0; row < moduleCount; row++) {
                    for (let col = 0; col < moduleCount; col++) {
                        if (qr.isDark(row, col)) {
                            ctx.fillRect(offset + col * cell, offset + row * cell, cell, cell);
                        }
                    }
                }

                $qrCodeLabel.text(text);
                return true;
            }

            function updateNominalMeta() {
                const jenis = $jenisNominal.val() || 'uang';

                $('#danaInfo').toggle(jenis !== 'diskon');

                if (jenis === 'diskon') {
                    $nominalPrefix.text('%');
                    $nominalHint.text('Diskon dalam persen, contoh: 10 untuk 10%. Voucher diskon hanya bisa dipakai sekali.');
                    $nominalInput.attr('step', '1').attr('max', '100');
                } else {
                    $nominalPrefix.text('Rp');
                    $nominalHint.text('Menjadi saldo awal voucher, contoh: 50000. Voucher hanya bisa dipakai sekali; bila belanja lebih kecil, sisa saldo hangus.');
                    $nominalInput.attr('step', '1000').removeAttr('max');
                }
            }

            if ($kodeInput.length && $generateBtn.length) {
                $generateBtn.on('click', function (e) {
                    e.preventDefault();

                    const rand = Array.from({ length: 6 }, function () {
                        return Math.random().toString(36).charAt(2) || 'X';
                    }).join('').toUpperCase();

                    const now = new Date();
                    const stamp = now.getFullYear().toString().slice(-2)
                        + String(now.getMonth() + 1).padStart(2, '0')
                        + String(now.getDate()).padStart(2, '0');

                    $kodeInput.val('VCR-' + rand + '-' + stamp).trigger('input');
                    $kodeInput.focus();
                });
            }

            function currentKode() {
                return String($kodeInput.val() || '').trim().toUpperCase();
            }

            function renderQr() {
                const value = currentKode();
                if (!value) {
                    $qrPreviewWrapper.hide();
                    return;
                }

                if (generateQrCodeFromText(value)) {
                    $qrPreviewWrapper.show();
                }
            }

            // Kode disimpan & di-encode ke QR dalam huruf besar, sama seperti yang tampil di input.
            $kodeInput.on('input', function () {
                const pos = this.selectionStart;
                const upper = this.value.toUpperCase();
                if (this.value !== upper) {
                    this.value = upper;
                    this.setSelectionRange(pos, pos);
                }

                if ($qrPreviewWrapper.is(':visible')) {
                    renderQr();
                }
            });

            if ($generateQrBtn.length) {
                $generateQrBtn.on('click', function (e) {
                    e.preventDefault();

                    if (!currentKode()) {
                        $generateBtn.trigger('click');
                    }

                    renderQr();
                });
            }

            $downloadQrBtn.on('click', function (e) {
                e.preventDefault();
                const kode = currentKode();
                if (!kode) {
                    return;
                }

                const link = document.createElement('a');
                link.href = $canvas[0].toDataURL('image/png');
                link.download = 'qr-voucher-' + kode + '.png';
                document.body.appendChild(link);
                link.click();
                link.remove();
            });

            if (currentKode() && {{ $voucher->exists ? 'true' : 'false' }}) {
                renderQr();
            }

            if ($jenisNominal.length) {
                $jenisNominal.on('change', updateNominalMeta);
                updateNominalMeta();
            }

            // Preview desain voucher yang dipilih.
            $('.desain-input').on('change', function () {
                const file = this.files && this.files[0];
                const $preview = $($(this).data('preview'));
                if (!file) {
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran desain maksimal 5 MB.');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    $preview.empty().append($('<img>', { src: e.target.result, alt: 'Preview desain' }).css({ width: '100%', height: '100%', objectFit: 'cover' }));
                };
                reader.readAsDataURL(file);
            });

            // Buat voucher: tampilkan kode yang akan dibuat & total dana (nominal x jumlah).
            const $jumlahVoucher = $('#jumlah_voucher');
            const $totalDanaInfo = $('#totalDanaInfo');
            if ($jumlahVoucher.length) {
                const danaTersedia = {{ (float) $dana->saldo }};
                const rupiah = function (n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); };
                const esc = function (s) { return $('<span>').text(s).html(); };

                const updateTotalDana = function () {
                    const jumlah = Math.max(1, parseInt($jumlahVoucher.val(), 10) || 1);
                    const kode = currentKode() || 'KODE';
                    const contohKode = jumlah > 1
                        ? kode + '-001 s/d ' + kode + '-' + String(jumlah).padStart(3, '0')
                        : kode;
                    let html = '<small>Kode: <strong>' + esc(contohKode) + '</strong></small>';

                    if (($jenisNominal.val() || 'uang') !== 'diskon') {
                        const nominal = parseFloat($nominalInput.val()) || 0;
                        const total = nominal * jumlah;
                        const cukup = total <= danaTersedia;
                        html += '<br>Total diambil dari dana: <strong class="' + (cukup ? 'text-success' : 'text-danger') + '">'
                            + rupiah(total) + '</strong> (' + jumlah + ' x ' + rupiah(nominal) + ')'
                            + (cukup ? '' : ' <span class="text-danger">- dana tidak cukup, sisa ' + rupiah(danaTersedia) + '</span>');
                    }

                    $totalDanaInfo.html(html);
                };

                $jumlahVoucher.on('input change', updateTotalDana);
                $nominalInput.on('input change', updateTotalDana);
                $jenisNominal.on('change', updateTotalDana);
                $kodeInput.on('input', updateTotalDana);
                updateTotalDana();
            }

            // Tampilkan efek edit saldo ke dana voucher.
            const $saldoInput = $('#saldo');
            const $saldoSelisihInfo = $('#saldoSelisihInfo');
            if ($saldoInput.length && $saldoSelisihInfo.length) {
                const saldoLama = {{ (float) ($voucher->saldo ?? 0) }};
                const danaTersedia = {{ (float) $dana->saldo }};
                const rupiah = function (n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); };

                $saldoInput.on('input change', function () {
                    $nominalInput.val($saldoInput.val());
                    const selisih = (parseFloat($saldoInput.val()) || 0) - saldoLama;
                    if (selisih > 0) {
                        const cukup = selisih <= danaTersedia;
                        $saldoSelisihInfo.html('Diambil dari dana: <strong class="' + (cukup ? 'text-success' : 'text-danger') + '">' + rupiah(selisih) + '</strong>'
                            + (cukup ? '' : ' <span class="text-danger">- dana tidak cukup</span>'));
                    } else if (selisih < 0) {
                        $saldoSelisihInfo.html('Kembali ke dana: <strong class="text-info">' + rupiah(-selisih) + '</strong>');
                    } else {
                        $saldoSelisihInfo.empty();
                    }
                });
            }
        });
    </script>
@endpush
