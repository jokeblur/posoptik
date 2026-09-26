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
            <form method="POST" action="{{ $voucher->exists ? route('voucher.update', $voucher) : route('voucher.store') }}">
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
                            @if ($voucher->exists && !$voucher->isDiskon())
                                <div class="text-info" style="margin-top:4px;">
                                    <i class="fa fa-info-circle"></i> Sisa saldo: <strong>{{ $voucher->saldoLabel() }}</strong>.
                                    Nilai awal tidak bisa diubah; tambah saldo lewat tombol <strong>Saldo</strong> di daftar voucher.
                                </div>
                            @elseif ($lockNominal)
                                <div class="text-info" style="margin-top:4px;"><i class="fa fa-info-circle"></i> Voucher sudah dipakai, persen diskon tidak bisa diubah.</div>
                            @endif
                        </div>
                    </div>
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

                if (jenis === 'diskon') {
                    $nominalPrefix.text('%');
                    $nominalHint.text('Diskon dalam persen, contoh: 10 untuk 10%. Voucher diskon hanya bisa dipakai sekali.');
                    $nominalInput.attr('step', '1').attr('max', '100');
                } else {
                    $nominalPrefix.text('Rp');
                    $nominalHint.text('Menjadi saldo awal voucher, contoh: 50000. Voucher bisa dipakai berkali-kali sampai saldo habis.');
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
        });
    </script>
@endpush
