@extends('kasir-mobile.layout')

@section('content')
<div class="m-card" style="padding:12px 14px;">
    <div style="font-weight:700;">Semua Transaksi Penjualan</div>
    <div style="font-size:12px; color:#888; margin-top:3px;">{{ $transaksis->total() }} transaksi</div>
</div>

<div class="m-card">
    <form method="GET" action="{{ route('kasir-mobile.penjualan') }}" id="sales-search-form">
        <label class="m-label" for="sales-search">Cari nama pasien atau scan/cari QR penjualan</label>
        <div style="display:flex; gap:8px;">
            <input type="search" name="q" id="sales-search" class="m-input" value="{{ $search }}" placeholder="Nama pasien, kode transaksi, atau barcode..." style="min-width:0; flex:1;">
            <button type="submit" class="btn btn-primary" aria-label="Cari penjualan" title="Cari">
                <i class="fa fa-search"></i>
            </button>
        </div>
        <button type="button" id="start-sales-qr" class="btn btn-default btn-block" style="margin-top:8px;">
            <i class="fa fa-qrcode"></i> Scan QR
        </button>
        <button type="button" id="stop-sales-qr" class="btn btn-danger btn-block" style="display:none; margin-top:8px;">
            <i class="fa fa-stop"></i> Hentikan Scan
        </button>
        <div id="sales-qr-reader" style="display:none; width:100%; margin-top:10px; overflow:hidden; border-radius:8px;"></div>
    </form>
</div>

@forelse($transaksis as $t)
    <div class="m-card" style="padding:12px 14px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
            <div style="min-width:0; flex:1;">
                <div style="font-weight:700; font-size:14px;">{{ $t->kode_penjualan }}</div>
                <div style="font-size:12px; color:#888; margin-top:2px;">
                    <i class="fa fa-user"></i> {{ $t->pasien->nama_pasien ?? $t->nama_pasien_manual ?? '-' }}
                </div>
                <div style="font-size:12px; color:#888; margin-top:2px;">
                    <i class="fa fa-calendar"></i> {{ $t->tanggal ? $t->tanggal->translatedFormat('d M Y') : '-' }}
                    · <i class="fa fa-clock-o"></i> {{ $t->created_at->format('H:i') }}
                </div>
                <div style="font-size:12px; margin-top:4px;">
                    <span class="label label-default">{{ strtoupper($t->metode_pembayaran ?? 'cash') }}</span>
                    <span class="label label-primary">{{ $t->pasien_service_type ?? 'Umum' }}</span>
                </div>
            </div>
            <div style="text-align:right; font-size:12px; color:#666;">
                <div style="font-weight:700; color:var(--brand); font-size:15px; margin-bottom:4px;">Rp {{ number_format($t->total, 0, ',', '.') }}</div>
                <div><span class="label {{ $t->status === 'Lunas' ? 'label-success' : 'label-warning' }}">Pembayaran: {{ $t->status ?? 'Belum Lunas' }}</span></div>
            </div>
        </div>

        <div style="margin-top:10px; font-size:12px; color:#555; border-top:1px solid #f0f0f0; padding-top:10px;">
            Status pengerjaan:
            <span class="label label-info">{{ $t->status_pengerjaan ?? 'Menunggu Pengerjaan' }}</span>
        </div>

        <form class="status-form" data-id="{{ $t->id }}" style="margin-top:10px;">
            <div style="display:grid; gap:8px;">
                <select name="status_pengerjaan" class="m-select">
                    <option value="Menunggu Pengerjaan" {{ ($t->status_pengerjaan ?? '') === 'Menunggu Pengerjaan' ? 'selected' : '' }}>Menunggu Pengerjaan</option>
                    <option value="Lensa Di Pesan" {{ ($t->status_pengerjaan ?? '') === 'Lensa Di Pesan' ? 'selected' : '' }}>Lensa Di Pesan</option>
                    <option value="Lensa Datang" {{ ($t->status_pengerjaan ?? '') === 'Lensa Datang' ? 'selected' : '' }}>Lensa Datang</option>
                    <option value="Sedang Mengerjakan" {{ ($t->status_pengerjaan ?? '') === 'Sedang Mengerjakan' ? 'selected' : '' }}>Sedang Mengerjakan</option>
                    <option value="Sudah Di Kerjakan" {{ ($t->status_pengerjaan ?? '') === 'Sudah Di Kerjakan' ? 'selected' : '' }}>Sudah Di Kerjakan</option>
                    <option value="Kirim WA" {{ ($t->status_pengerjaan ?? '') === 'Kirim WA' ? 'selected' : '' }}>Kirim WA</option>
                    <option value="Sudah Di Ambil" {{ ($t->status_pengerjaan ?? '') === 'Sudah Di Ambil' ? 'selected' : '' }}>Sudah Di Ambil</option>
                </select>
                <input type="text" name="nohp" class="m-input" value="{{ trim((string)($t->pasien->nohp ?? '')) }}" placeholder="Nomor HP pasien (opsional untuk WA)" style="display:none;">
                <button type="submit" class="btn btn-primary" style="width:100%;">Update status</button>
            </div>
        </form>
    </div>
@empty
    <div class="m-card">
        <div class="m-empty"><i class="fa fa-inbox"></i>Belum ada transaksi penjualan.<br><a href="{{ route('kasir-mobile.index') }}" class="text-brand" style="font-weight:700;">Buat transaksi →</a></div>
    </div>
@endforelse

@if($transaksis->hasPages())
    <div class="m-card" style="overflow-x:auto; text-align:center;">
        {{ $transaksis->links() }}
    </div>
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const updateStatusUrlTemplate = '{{ route("penjualan.update_status_pengerjaan", ":id") }}';
    let salesQrScanner = null;

    $('#start-sales-qr').on('click', async function () {
        if (typeof Html5Qrcode === 'undefined') {
            toast('Library pemindai QR gagal dimuat.', false);
            return;
        }

        const reader = document.getElementById('sales-qr-reader');
        reader.style.display = 'block';
        $('#start-sales-qr').hide();
        $('#stop-sales-qr').show();

        try {
            salesQrScanner = new Html5Qrcode('sales-qr-reader');
            await salesQrScanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                async function (decodedText) {
                    const search = document.getElementById('sales-search');
                    try {
                        const decodedUrl = new URL(decodedText);
                        const qrCode = decodedUrl.pathname.split('/').filter(Boolean).pop();
                        search.value = qrCode || decodedText;
                    } catch (error) {
                        search.value = decodedText;
                    }

                    await stopSalesQrScanner();
                    document.getElementById('sales-search-form').submit();
                }
            );
        } catch (error) {
            await stopSalesQrScanner();
            toast('Kamera tidak dapat dibuka. Periksa izin kamera atau masukkan kode QR secara manual.', false);
        }
    });

    async function stopSalesQrScanner() {
        if (salesQrScanner && salesQrScanner.isScanning) {
            await salesQrScanner.stop();
            salesQrScanner.clear();
        }
        salesQrScanner = null;
        document.getElementById('sales-qr-reader').style.display = 'none';
        $('#stop-sales-qr').hide();
        $('#start-sales-qr').show();
    }

    $('#stop-sales-qr').on('click', function () {
        stopSalesQrScanner().catch(function () {
            toast('Pemindai QR gagal dihentikan.', false);
        });
    });

    $('.status-form').each(function () {
        const form = $(this);
        const select = form.find('select[name="status_pengerjaan"]');
        const nohpInput = form.find('input[name="nohp"]');
        const hasSavedPhone = !!nohpInput.val().trim();

        const toggleNohp = function () {
            const visible = select.val() === 'Kirim WA' && !hasSavedPhone;
            nohpInput.toggle(visible).attr('required', visible ? true : false);
        };

        select.on('change', toggleNohp);
        toggleNohp();
    });

    $('.status-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const id = form.data('id');
        const status = form.find('select[name="status_pengerjaan"]').val();
        const nohp = form.find('input[name="nohp"]').val().trim();

        if (!status) {
            toast('Pilih status terlebih dahulu.', false);
            return;
        }

        if (status === 'Kirim WA' && !nohp) {
            form.find('input[name="nohp"]').show().attr('required', true).focus();
            toast('Isi nomor HP pasien terlebih dahulu.', false);
            return;
        }

        const whatsappWindow = status === 'Kirim WA' ? window.open('about:blank', '_blank') : null;

        $.ajax({
            url: updateStatusUrlTemplate.replace(':id', id),
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status_pengerjaan: status,
                nohp: nohp
            },
            success: function (response) {
                if (response && response.success) {
                    const whatsapp = response.whatsapp || null;
                    if (whatsapp && whatsapp.open_link && whatsapp.link) {
                        if (whatsappWindow) {
                            whatsappWindow.location = whatsapp.link;
                        } else {
                            window.location.href = whatsapp.link;
                            return;
                        }
                    } else if (whatsappWindow) {
                        whatsappWindow.close();
                    }

                    const message = response.message || 'Status berhasil diperbarui.';
                    toast(whatsapp && whatsapp.message ? message + ' ' + whatsapp.message : message);
                    setTimeout(() => window.location.reload(), 700);
                    return;
                }

                if (whatsappWindow) {
                    whatsappWindow.close();
                }
                toast((response && response.message) || 'Gagal memperbarui status.', false);
            },
            error: function (xhr) {
                if (whatsappWindow) {
                    whatsappWindow.close();
                }
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Tidak dapat mengubah status penjualan.';
                toast(message, false);
            }
        });
    });
</script>
@endpush
