@extends('layouts.master')

@section('title', 'Detail Penjualan')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Detail Transaksi: {{ $penjualan->kode_penjualan }}</h3>
            </div>
            <div class="box-body">
                @php
                    $hanyaAksesoris = $penjualan->details->count() > 0 && $penjualan->details->every(function($d) {
                        return $d->itemable_type === 'App\\Models\\Aksesoris';
                    });
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        <h4>Informasi Pasien</h4>
                        <table class="table">
                            <tr>
                                <th style="width: 30%;">Pasien</th>
                                <td>{{ $penjualan->nama_pasien ?? 'N/A' }}</td>
                            </tr>
                            @if(!$hanyaAksesoris)
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $penjualan->pasien->alamat ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>No. Telepon</th>
                                <td>{{ $penjualan->pasien->nohp ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Layanan</th>
                                <td><span class="label label-info">{{ $penjualan->pasien->service_type ?? 'N/A' }}</span></td>
                            </tr>
                            <tr>
                                <th>Status Transaksi</th>
                                <td>
                                    @if($penjualan->transaction_status == 'Naik Kelas')
                                        <span class="label label-warning">{{ $penjualan->transaction_status }}</span>
                                    @else
                                        <span class="label label-success">{{ $penjualan->transaction_status ?? 'Normal' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Jenis Transaksi</th>
                                <td>
                                    <span class="label label-{{ $penjualan->jenis_transaksi == 'Gosok' ? 'warning' : 'info' }}">{{ $penjualan->jenis_transaksi ?? 'Stock' }}</span>
                                </td>
                            </tr>
                            @if($penjualan->pasien_service_type)
                            <tr>
                                <th>Jenis Layanan BPJS</th>
                                <td><span class="label label-info">{{ $penjualan->pasien_service_type }}</span></td>
                            </tr>
                            <tr>
                                <th>Harga Default BPJS</th>
                                <td>Rp {{ format_uang($penjualan->bpjs_default_price) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Dokter</th>
                                <td>{{ $penjualan->nama_dokter ?? 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Informasi Transaksi</h4>
                        <table class="table">
                            <tr>
                                <th>Kode Transaksi</th>
                                <td>{{ $penjualan->kode_penjualan }}</td>
                            </tr>
                            <tr>
                                <th>Barcode</th>
                                <td>
                                    <strong>{{ $penjualan->barcode ?? 'Belum dibuat' }}</strong>
                                    @if($penjualan->barcode)
                                        <a href="{{ route('barcode.print', $penjualan->id) }}" target="_blank" class="btn btn-xs btn-info">
                                            <i class="fa fa-print"></i> Print Barcode
                                        </a>
                                    @else
                                        <button onclick="generateBarcode({{ $penjualan->id }})" class="btn btn-xs btn-success">
                                            <i class="fa fa-barcode"></i> Generate Barcode
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Transaksi</th>
                                <td>{{ tanggal_indonesia($penjualan->tanggal, false) }}</td>
                            </tr>
                            <tr>
                                <th>Kasir</th>
                                <td>{{ $penjualan->user->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Cabang</th>
                                <td>{{ $penjualan->branch->name ?? 'N/A' }}</td>
                            </tr>
                            @if($penjualan->photo_bpjs)
                            <tr>
                                <th>Bukti BPJS</th>
                                <td>
                                    <a href="{{ route('penjualan.bpjs-photo', $penjualan->id) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fa fa-image"></i> Lihat Foto
                                    </a>
                                    <br><br>
                                    <img src="{{ route('penjualan.bpjs-photo', $penjualan->id) }}" 
                                         alt="Bukti BPJS" 
                                         style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; border-radius: 5px;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display: none; color: red; font-size: 12px;">
                                        <i class="fa fa-exclamation-triangle"></i> Foto tidak dapat dimuat. 
                                        <a href="{{ route('penjualan.bpjs-photo', $penjualan->id) }}" target="_blank">Coba buka langsung</a>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @if($penjualan->signature_bpjs)
                            <tr>
                                <th>Tanda Tangan BPJS</th>
                                <td>
                                    <div class="signature-display">
                                        <img src="{{ $penjualan->signature_bpjs }}" alt="Tanda Tangan Pasien" style="max-width: 300px; border: 1px solid #ddd; border-radius: 5px;">
                                        <br><small class="text-muted">Ditandatangani pada: {{ $penjualan->signature_date ? tanggal_indonesia($penjualan->signature_date, false) : 'N/A' }}</small>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>

                @if(!$hanyaAksesoris && $penjualan->pasien && $penjualan->pasien->prescriptions->isNotEmpty())
                    @php $resep = $penjualan->pasien->prescriptions->last(); @endphp
                    <h4>Resep Terakhir ({{ tanggal_indonesia($resep->tanggal, false) }})</h4>
                    <table class="table table-condensed" style="width: 100%; margin-bottom: 10px;">
                        <tbody>
                            <tr>
                                <td style="width: 8%; font-weight: bold; text-align: center;">OD</td>
                                <td style="width: 12%; text-align: center;"><strong>SPH</strong><br>{{ $resep->od_sph ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>CYL</strong><br>{{ $resep->od_cyl ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>AXIS</strong><br>{{ $resep->od_axis ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>ADD</strong><br>{{ $resep->add_kanan ?? $resep->add ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>PD</strong><br>{{ $resep->pd_kanan ?? $resep->pd ?? '-' }}</td>
                                <td style="width: 12%; text-align: center; font-size: 11px; font-weight: bold; color: #666;">Kanan</td>
                            </tr>
                            <tr>
                                <td style="width: 8%; font-weight: bold; text-align: center;">OS</td>
                                <td style="width: 12%; text-align: center;"><strong>SPH</strong><br>{{ $resep->os_sph ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>CYL</strong><br>{{ $resep->os_cyl ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>AXIS</strong><br>{{ $resep->os_axis ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>ADD</strong><br>{{ $resep->add_kiri ?? $resep->add ?? '-' }}</td>
                                <td style="width: 12%; text-align: center;"><strong>PD</strong><br>{{ $resep->pd_kiri ?? $resep->pd ?? '-' }}</td>
                                <td style="width: 12%; text-align: center; font-size: 11px; font-weight: bold; color: #666;">Kiri</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr>
                @endif

                @php
                    $isBPJS = $penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']);
                    // Debug: Log BPJS information
                    \Log::info('BPJS Debug in show view:', [
                        'penjualan_id' => $penjualan->id,
                        'pasien_service_type' => $penjualan->pasien->service_type ?? 'N/A',
                        'bpjs_default_price' => $penjualan->bpjs_default_price ?? 'N/A',
                        'isBPJS' => $isBPJS,
                        'pasien_service_type_from_penjualan' => $penjualan->pasien_service_type ?? 'N/A',
                        'photo_bpjs_path' => $penjualan->photo_bpjs ?? 'N/A',
                        'photo_bpjs_url' => $penjualan->photo_bpjs ? asset('storage/' . $penjualan->photo_bpjs) : 'N/A'
                    ]);
                @endphp
                
                <h4>Detail Produk</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jumlah</th>
                            @if(!$isBPJS)
                            <th>Harga Satuan</th>
                            @endif
                            <th>{{ $isBPJS ? 'Harga Jual Produk' : 'Subtotal' }}</th>
                            <th>Biaya Tambahan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualan->details as $detail)
                        <tr>
                            <td>
                                @if($detail->itemable_type === 'App\\Models\\Aksesoris')
                                    {{ $detail->itemable->nama_produk ?? 'Aksesoris' }}
                                @elseif($detail->itemable_type === 'App\\Models\\Frame')
                                    {{ $detail->itemable->merk_frame ?? 'Frame' }}
                                    @if($detail->itemable && $detail->itemable->jenis_frame)
                                        <br><small class="text-muted">({{ $detail->itemable->jenis_frame }})</small>
                                    @endif
                                @elseif($detail->itemable_type === 'App\\Models\\Lensa')
                                    {{ $detail->itemable->merk_lensa ?? 'Lensa' }}
                                @else
                                    Produk tidak ditemukan
                                @endif
                            </td>
                            <td>{{ $detail->quantity }}</td>
                            @if(!$isBPJS)
                            <td>Rp {{ format_uang($detail->price) }}</td>
                            @endif
                            <td>
                                @if($isBPJS)
                                    @if($detail->itemable_type === 'App\\Models\\Frame')
                                        Rp {{ format_uang($detail->itemable->harga_jual_frame ?? 0) }}
                                    @elseif($detail->itemable_type === 'App\\Models\\Lensa')
                                        Rp {{ format_uang($detail->itemable->harga_jual_lensa ?? 0) }}
                                    @elseif($detail->itemable_type === 'App\\Models\\Aksesoris')
                                        Rp {{ format_uang($detail->itemable->harga_jual ?? 0) }}
                                    @else
                                        Rp {{ format_uang($detail->price) }}
                                    @endif
                                @else
                                    Rp {{ format_uang($detail->subtotal) }}
                                @endif
                            </td>
                            <td>
                                @if($detail->additional_cost > 0)
                                    <span class="label label-warning">Rp {{ format_uang($detail->additional_cost) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($detail->itemable_type === 'App\\Models\\Lensa' && in_array($penjualan->status_pengerjaan, ['Sedang Dikerjakan', 'Selesai Dikerjakan']))
                                    <button class="btn btn-xs btn-danger" onclick="openReplaceLensaModal({{ $detail->id }}, '{{ $detail->itemable->merk_lensa }}')" title="Lensa Rusak">
                                        <i class="fa fa-exchange"></i> Ganti
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <hr>

                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                        <h4 class="text-right">Rincian Pembayaran</h4>
                        <table class="table">
                            @if($isBPJS)
                                <tr>
                                    <th style="width:50%">Total Harga Jual Produk:</th>
                                    <td class="text-right">
                                        @php
                                            $totalSalePrice = 0;
                                            foreach($penjualan->details as $detail) {
                                                if($detail->itemable_type === 'App\\Models\\Frame') {
                                                    $totalSalePrice += ($detail->itemable->harga_jual_frame ?? 0) * $detail->quantity;
                                                } elseif($detail->itemable_type === 'App\\Models\\Lensa') {
                                                    $totalSalePrice += ($detail->itemable->harga_jual_lensa ?? 0) * $detail->quantity;
                                                } elseif($detail->itemable_type === 'App\\Models\\Aksesoris') {
                                                    $totalSalePrice += ($detail->itemable->harga_jual ?? 0) * $detail->quantity;
                                                }
                                            }
                                        @endphp
                                        Rp {{ format_uang($totalSalePrice) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:50%">Biaya BPJS:</th>
                                    <td class="text-right">
                                        @if($penjualan->bpjs_default_price > 0)
                                            Rp {{ format_uang($penjualan->bpjs_default_price) }}
                                        @else
                                            <span class="text-danger">Rp 0 (BPJS price not set)</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($penjualan->details->sum('additional_cost') > 0)
                                <tr>
                                    <th>Total Biaya Tambahan:</th>
                                    <td class="text-right"><span class="label label-warning">Rp {{ format_uang($penjualan->details->sum('additional_cost')) }}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Status:</th>
                                    <td class="text-right"><span class="label label-{{ $penjualan->status == 'Lunas' ? 'success' : 'warning' }}">{{ $penjualan->status }}</span></td>
                                </tr>
                            @else
                                <tr>
                                    <th style="width:50%">Subtotal:</th>
                                    <td class="text-right">Rp {{ format_uang($penjualan->details->sum('subtotal')) }}</td>
                                </tr>
                                @if($penjualan->details->sum('additional_cost') > 0)
                                <tr>
                                    <th>Total Biaya Tambahan:</th>
                                    <td class="text-right"><span class="label label-warning">Rp {{ format_uang($penjualan->details->sum('additional_cost')) }}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Diskon:</th>
                                    <td class="text-right">Rp {{ format_uang($penjualan->diskon) }}</td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td class="text-right"><strong>Rp {{ format_uang($penjualan->total) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Dibayar:</th>
                                    <td class="text-right">Rp {{ format_uang($penjualan->bayar) }}</td>
                                </tr>
                                <tr>
                                    <th>Kekurangan:</th>
                                    <td class="text-right"><strong>Rp {{ format_uang($penjualan->kekurangan) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td class="text-right"><span class="label label-{{ $penjualan->status == 'Lunas' ? 'success' : 'warning' }}">{{ $penjualan->status }}</span></td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <a href="{{ route('penjualan.index') }}" class="btn btn-default">Kembali ke Daftar Penjualan</a>

                @if($penjualan->status == 'Belum Lunas')
                <button class="btn btn-success pull-right" id="btn-bayar-lunas" data-url="{{ route('penjualan.lunas', $penjualan->id) }}" style="margin-left: 10px;"><i class="fa fa-check"></i> Bayar Lunas</button>
                @endif
                
                <a href="{{ route('penjualan.cetak', $penjualan->id) }}" target="_blank" class="btn btn-primary pull-right"><i class="fa fa-print"></i> Cetak Struk</a>
                <a href="{{ route('penjualan.cetak-half', $penjualan->id) }}" target="_blank" class="btn btn-info pull-right" style="margin-right: 10px;"><i class="fa fa-print"></i> Cetak Half Page</a>

                @php
                    $pasienPhone = $penjualan->pasien?->nohp ?? '';
                    $normalizedPhone = preg_replace('/\D+/', '', $pasienPhone);
                    if (strpos($normalizedPhone, '0') === 0) {
                        $normalizedPhone = '62' . substr($normalizedPhone, 1);
                    }
                    if (strpos($normalizedPhone, '62') !== 0 && $normalizedPhone !== '') {
                        $normalizedPhone = '62' . ltrim($normalizedPhone, '0');
                    }
                @endphp

                <button
                    type="button"
                    class="btn btn-success pull-right"
                    id="btn-kirim-wa-barcode"
                    data-phone="{{ $normalizedPhone }}"
                    data-pasien="{{ $penjualan->nama_pasien }}"
                    data-kode="{{ $penjualan->kode_penjualan }}"
                    data-penjualan-id="{{ $penjualan->id }}"
                    data-generate-url="{{ route('penjualan.generate-barcode-image', $penjualan->id) }}"
                    style="margin-right: 10px;"
                >
                    <i class="fa fa-whatsapp"></i> Kirim WA (Barcode)
                </button>
                
                @php
                    $user = auth()->user();
                    $canDelete = ($user->isSuperAdmin() || $user->isAdmin()) && 
                                ($user->role === 'super admin' || $penjualan->branch_id === $user->branch_id);
                @endphp
                
                @if($canDelete)
                <button class="btn btn-danger pull-right" id="btn-hapus-transaksi" data-url="{{ route('penjualan.destroy', $penjualan->id) }}" style="margin-right: 10px;"><i class="fa fa-trash"></i> Hapus Transaksi</button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<style>
/* Fix Select2 in modal */
.modal .select2-container {
    z-index: 1050 !important;
    width: 100% !important;
}

.select2-dropdown {
    z-index: 1050 !important;
    background-color: #ffffff !important;
    border: 1px solid #ddd !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

.select2-dropdown.select2-dropdown--below {
    margin-top: 2px !important;
}

.select2-results__options {
    background-color: #ffffff !important;
    max-height: 300px !important;
    overflow-y: auto !important;
}

.select2-results__option {
    background-color: #ffffff !important;
    color: #333333 !important;
    padding: 8px 12px !important;
    border-bottom: 1px solid #f0f0f0 !important;
}

.select2-results__option:hover {
    background-color: #e8f4f8 !important;
    color: #000 !important;
}

.select2-results__option--highlighted {
    background-color: #007bff !important;
    color: #ffffff !important;
}

.select2-results__option--selected {
    background-color: #f8f9fa !important;
    color: #333333 !important;
}

.select2-container--open .select2-dropdown {
    background-color: #ffffff !important;
    opacity: 1 !important;
}

.select2-container .select2-selection--single {
    background-color: #ffffff !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    min-height: 38px !important;
}

.select2-container--focus .select2-selection--single {
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
}

.select2-search__field {
    background-color: #ffffff !important;
    color: #333333 !important;
    border: 1px solid #ddd !important;
    padding: 6px 8px !important;
}

.select2-search__field::placeholder {
    color: #999999 !important;
}

.hidden-accessible {
    position: absolute !important;
    left: -9999px !important;
}
</style>
<script>
// Helper function untuk render receipt ke image
async function renderReceiptToImage(captureUrl) {
    return new Promise((resolve, reject) => {
        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.left = '-99999px';
        iframe.style.top = '0';
        iframe.style.width = '420px';
        iframe.style.height = '700px';
        iframe.src = captureUrl;

        iframe.onload = async function () {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                const target = iframeDoc.body;

                const canvas = await html2canvas(target, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    width: target.scrollWidth,
                    height: target.scrollHeight,
                    windowWidth: target.scrollWidth,
                    windowHeight: target.scrollHeight
                });

                const imageData = canvas.toDataURL('image/png');
                document.body.removeChild(iframe);
                resolve(imageData);
            } catch (error) {
                if (iframe.parentNode) {
                    document.body.removeChild(iframe);
                }
                reject(error);
            }
        };

        iframe.onerror = function () {
            if (iframe.parentNode) {
                document.body.removeChild(iframe);
            }
            reject(new Error('Gagal memuat halaman nota untuk diubah ke gambar'));
        };

        document.body.appendChild(iframe);
    });
}

// Tunggu jQuery siap sebelum menjalankan handler
function initWAHandlers() {
    console.log('✓ Initializing WA handlers...');
    
    // Handler untuk tombol Kirim WA Barcode
    $(document).on('click', '#btn-kirim-wa-barcode', async function(e) {
        e.preventDefault();
        console.log('✓ Barcode button clicked!');
        
        const btn = $(this);
        let phone = btn.data('phone');
        const pasien = btn.data('pasien') || 'Pasien';
        const kode = btn.data('kode') || '-';
        const generateUrl = btn.data('generate-url');

        // Jika tidak ada nomor HP, minta input dari user
        if (!phone || phone === '' || phone === '0') {
            const { value: inputPhone } = await Swal.fire({
                title: 'Input Nomor WhatsApp',
                input: 'tel',
                inputLabel: 'Masukkan nomor WhatsApp pasien (cth: 62812XXXXXX)',
                inputValue: phone || '',
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal'
            });

            if (!inputPhone) {
                Swal.fire('Dibatalkan', 'Kirim WA dibatalkan karena tidak ada nomor.', 'info');
                return;
            }

            // Normalize nomor
            phone = inputPhone.replace(/\D+/g, '');
            if (phone.startsWith('0')) {
                phone = '62' + phone.substring(1);
            } else if (!phone.startsWith('62')) {
                phone = '62' + phone;
            }

            console.log('Phone input normalized:', phone);
        }

        console.log('Phone:', phone, 'URL:', generateUrl);

        try {
            Swal.fire({
                title: 'Menyiapkan Barcode',
                text: 'Sedang membuat barcode QR code, mohon tunggu...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Call server to generate barcode image
            const result = await $.ajax({
                url: generateUrl,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                },
                timeout: 30000
            });

            console.log('Generate result:', result);

            if (!result.success || !result.image_url) {
                throw new Error(result.message || 'Gagal membuat barcode');
            }

            const message = `Halo ${pasien}, berikut QR code nota Anda (${kode}): ${result.image_url}`;
            const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

            Swal.close();
            window.open(waUrl, '_blank');
        } catch (error) {
            console.error('Error:', error);
            let errorMsg = 'Tidak dapat membuat barcode.';
            if (error.responseJSON && error.responseJSON.message) {
                errorMsg = error.responseJSON.message;
            } else if (error.message) {
                errorMsg = error.message;
            } else if (error.statusText) {
                errorMsg = error.statusText;
            }
            Swal.fire('Gagal', errorMsg, 'error');
        }
    });

    // Handler untuk tombol Kirim WA Gambar Nota
    $(document).on('click', '#btn-kirim-wa-gambar', async function(e) {
        e.preventDefault();
        console.log('✓ Gambar button clicked!');
        
        const btn = $(this);
        const phone = btn.data('phone');
        const pasien = btn.data('pasien') || 'Pasien';
        const kode = btn.data('kode') || '-';
        const captureUrl = btn.data('capture-url');
        const uploadUrl = btn.data('upload-url');

        try {
            Swal.fire({
                title: 'Menyiapkan Nota Gambar',
                text: 'Sedang membuat gambar nota, mohon tunggu...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const imageData = await renderReceiptToImage(captureUrl);

            const uploadResult = await $.ajax({
                url: uploadUrl,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                    image_data: imageData
                },
                timeout: 30000
            });

            if (!uploadResult.success || !uploadResult.image_url) {
                throw new Error(uploadResult.message || 'Gagal membuat link gambar nota');
            }

            const message = `Halo ${pasien}, berikut nota transaksi Anda (${kode}) dalam bentuk gambar: ${uploadResult.image_url}`;
            const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

            Swal.close();
            window.open(waUrl, '_blank');
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Gagal', error.message || 'Tidak dapat mengirim nota gambar ke WhatsApp.', 'error');
        }
    });
}

// Cek apakah jQuery sudah loaded
if (typeof jQuery !== 'undefined') {
    initWAHandlers();
} else {
    document.addEventListener('DOMContentLoaded', initWAHandlers);
}

function generateBarcode(transaksiId) {
    Swal.fire({
        title: 'Generate Barcode',
        text: 'Apakah Anda yakin ingin membuat barcode untuk transaksi ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Generate!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("barcode.generate") }}',
                method: 'POST',
                data: {
                    transaksi_id: transaksiId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat generate barcode';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', message, 'error');
                }
            });
        }
    });
}

// Fungsi buka modal ganti lensa - GLOBAL SCOPE
window.openReplaceLensaModal = function(detailId, oldLensaMerk) {
    console.log('=== Opening modal for detail:', detailId, oldLensaMerk);
    console.log('Modal element exists:', $('#modal-replace-lensa').length > 0);
    console.log('Dropdown element exists:', $('#new_lensa_id').length > 0);
    
    $('#detail_id').val(detailId);
    $('#old_lensa_name').val(oldLensaMerk);
    
    // Destroy Select2 sebelum reset
    if ($('#new_lensa_id').data('select2')) {
        console.log('Destroying existing Select2...');
        $('#new_lensa_id').select2('destroy');
    }
    
    $('#new_lensa_id').val('').html('<option value="">-- Pilih Lensa --</option>');
    $('#reason').val('');
    
    // Show modal dan load lensa stok setelah modal shown
    $('#modal-replace-lensa').off('shown.bs.modal').on('shown.bs.modal', function() {
        console.log('Modal shown event fired, loading lensa stock...');
        loadLensaStock();
    }).modal('show');
};

// Load lensa yang tersedia
window.loadLensaStock = function() {
    console.log('=== Loading lensa stock...');
    $.ajax({
        url: '{{ route("penjualan.lensa-stok") }}',
        method: 'GET',
        dataType: 'json',
        timeout: 5000,
        success: function(response) {
            console.log('AJAX Success - Lensa count:', response.data ? response.data.length : 0);
            let options = '<option value="">-- Pilih Lensa --</option>';
            if (response.data && response.data.length > 0) {
                response.data.forEach(lensa => {
                    if (lensa.stok > 0) {
                        // Format: Merk (Ukuran | Type | Coating) - Stok: X
                        let displayText = lensa.merk_lensa;
                        let specs = [];
                        
                        if (lensa.index && lensa.index !== '-') {
                            specs.push('Ukuran: ' + lensa.index);
                        }
                        if (lensa.type && lensa.type !== '-') {
                            specs.push(lensa.type);
                        }
                        if (lensa.coating && lensa.coating !== '-') {
                            specs.push(lensa.coating);
                        }
                        
                        if (specs.length > 0) {
                            displayText += ' (' + specs.join(' | ') + ')';
                        }
                        displayText += ' - Stok: ' + lensa.stok;
                        
                        options += '<option value="' + lensa.id + '" data-stok="' + lensa.stok + 
                                   '" data-index="' + (lensa.index || '-') + 
                                   '" data-type="' + (lensa.type || '-') + 
                                   '" data-harga="' + lensa.harga_jual_lensa + '">' + 
                                   displayText + '</option>';
                    }
                });
                console.log('Generated options count:', (options.match(/option/g) || []).length);
            }
            
            // Set HTML
            $('#new_lensa_id').html(options);
            console.log('HTML set. Element value:', $('#new_lensa_id').val());
            console.log('HTML length:', $('#new_lensa_id').html().length);
            
            // Wait a bit then initialize Select2
            setTimeout(function() {
                try {
                    console.log('Initializing Select2...');
                    $('#new_lensa_id').select2({
                        width: '100%',
                        placeholder: '-- Cari dan Pilih Lensa --',
                        allowClear: true,
                        minimumInputLength: 0,
                        matcher: function(params, data) {
                            if ($.trim(params.term) === '') {
                                return data;
                            }
                            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                                return data;
                            }
                            return null;
                        }
                    });
                    console.log('Select2 initialized. Data attribute:', $('#new_lensa_id').data('select2'));
                } catch(e) {
                    console.error('Error initializing Select2:', e);
                }
            }, 100);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            Swal.fire('Error!', 'Gagal memuat data lensa. ' + error, 'error');
        }
    });
};

$(document).on('click', '#btn-bayar-lunas', function() {
    let url = $(this).data('url');
    
    Swal.fire({
        title: 'Konfirmasi Pelunasan',
        text: "Apakah Anda yakin ingin melunasi transaksi ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Lunasi!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(url, { '_token': '{{ csrf_token() }}' })
                .done(function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload(); // Muat ulang halaman untuk melihat perubahan
                    });
                })
                .fail(function(jqXHR) {
                    let errorMessage = 'Gagal memproses pelunasan.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMessage = jqXHR.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errorMessage, 'error');
                });
        }
    });
});

$(document).on('click', '#btn-hapus-transaksi', function() {
    let url = $(this).data('url');
    
    Swal.fire({
        title: 'Konfirmasi Penghapusan',
        text: "Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { '_token': '{{ csrf_token() }}' },
                success: function(response) {
                    Swal.fire('Berhasil!', response.message, 'success')
                        .then(() => {
                            window.location.href = '{{ route("penjualan.index") }}';
                        });
                },
                error: function(xhr) {
                    let message = 'Tidak dapat menghapus transaksi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', message, 'error');
                }
            });
        }
    });
});

// Event handler: Update info stok ketika lensa dipilih
$(document).on('change', '#new_lensa_id', function() {
    let selectedValue = $(this).val();
    console.log('Selected lensa:', selectedValue);
    
    if (selectedValue) {
        let selectedOption = $(this).find('option:selected');
        let stok = selectedOption.data('stok');
        let index = selectedOption.data('index');
        let type = selectedOption.data('type');
        let harga = selectedOption.data('harga');
        
        console.log('Stock for selected lensa:', stok);
        
        // Tampilkan info detail
        let infoText = '✓ Stok: ' + stok + ' unit';
        if (index && index !== '-') {
            infoText += ' | Ukuran: ' + index;
        }
        if (type && type !== '-') {
            infoText += ' | Type: ' + type;
        }
        if (harga) {
            infoText += ' | Harga: Rp ' + parseInt(harga).toLocaleString('id-ID');
        }
        
        $('#lensa_stok_info').html(infoText);
        $('#lensa_stok_info').css('color', '#28a745');
    } else {
        $('#lensa_stok_info').html('').css('color', '#666');
    }
});

// Event handler: Submit form ganti lensa
$(document).on('submit', '#form-replace-lensa', function(e) {
    e.preventDefault();
    
    let detailId = $('#detail_id').val();
    let newLensaId = $('#new_lensa_id').val();
    let reason = $('#reason').val();
    let penjualanId = {{ $penjualan->id }};
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    console.log('Form submitted:', { detailId, newLensaId, reason, penjualanId, csrfToken });

    if (!newLensaId) {
        Swal.fire('Peringatan!', 'Silakan pilih lensa pengganti terlebih dahulu', 'warning');
        return;
    }

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Lensa akan diganti dan stok akan diupdate. Lanjutkan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Ganti Lensa',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/penjualan/' + penjualanId + '/replace-lensa-damaged',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    detail_id: detailId,
                    new_lensa_id: newLensaId,
                    reason: reason
                }),
                success: function(response) {
                    console.log('Success response:', response);
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            html: '<strong>Lensa Lama:</strong> ' + response.old_lensa.merk + '<br>' +
                                  '<strong>Lensa Baru:</strong> ' + response.new_lensa.merk + '<br>' +
                                  '<strong>Harga:</strong> Rp ' + response.new_lensa.harga.toLocaleString('id-ID') + '<br><br>' +
                                  'Stok sudah diupdate otomatis.',
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                        $('#modal-replace-lensa').modal('hide');
                    } else {
                        Swal.fire('Gagal!', response.message || 'Gagal mengganti lensa', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    let message = 'Gagal mengganti lensa. Silakan coba lagi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        message = 'Anda tidak memiliki izin untuk mengganti lensa';
                    } else if (xhr.status === 422) {
                        message = 'Data tidak valid atau stok tidak cukup';
                    } else if (xhr.status === 500) {
                        message = 'Error server. Cek console untuk detail.';
                    }
                    console.log('Response Text:', xhr.responseText);
                    Swal.fire('Gagal!', message, 'error');
                }
            });
        }
    });
});
</script>
@endpush

<!-- Modal Ganti Lensa Rusak -->
<div class="modal fade" id="modal-replace-lensa" tabindex="-1" role="dialog" aria-labelledby="modal-replace-lensa-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-replace-lensa-label">Ganti Lensa Rusak</h4>
            </div>
            <form id="form-replace-lensa">
                @csrf
                <input type="hidden" id="detail_id" name="detail_id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Lensa Lama:</label>
                        <input type="text" id="old_lensa_name" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="new_lensa_id">Pilih Lensa Pengganti: <span class="text-danger">*</span></label>
                        <select id="new_lensa_id" name="new_lensa_id" class="form-control" required>
                            <option value="">-- Pilih Lensa --</option>
                        </select>
                        <small class="form-text text-muted" id="lensa_stok_info"></small>
                    </div>
                    <div class="form-group">
                        <label for="reason">Alasan Penggantian:</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3" placeholder="Misalnya: Lensa pecah saat pengerjaan, cacat produksi, dll"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ganti Lensa</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection 