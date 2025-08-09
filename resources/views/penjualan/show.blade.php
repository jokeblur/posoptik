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
                                <td>{{ $penjualan->pasien->nama_pasien ?? $penjualan->nama_pasien_manual ?? 'N/A' }}</td>
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
                                <td>{{ $penjualan->dokter->nama_dokter ?? 'N/A' }}</td>
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
                                <td><a href="{{ asset('storage/' . $penjualan->photo_bpjs) }}" target="_blank">Lihat Foto</a></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>

                @if(!$hanyaAksesoris && $penjualan->pasien && $penjualan->pasien->prescriptions->isNotEmpty())
                    @php $resep = $penjualan->pasien->prescriptions->last(); @endphp
                    <h4>Resep Terakhir ({{ tanggal_indonesia($resep->tanggal, false) }})</h4>
                    <table class="table table-bordered text-center" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 25%;">Mata</th>
                                <th class="text-center" style="width: 25%;">SPH</th>
                                <th class="text-center" style="width: 25%;">CYL</th>
                                <th class="text-center" style="width: 25%;">AXIS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>OD (Kanan)</strong></td>
                                <td>{{ $resep->od_sph ?? '-' }}</td>
                                <td>{{ $resep->od_cyl ?? '-' }}</td>
                                <td>{{ $resep->od_axis ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>OS (Kiri)</strong></td>
                                <td>{{ $resep->os_sph ?? '-' }}</td>
                                <td>{{ $resep->os_cyl ?? '-' }}</td>
                                <td>{{ $resep->os_axis ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-xs-6">
                            <p><strong>ADD:</strong> {{ $resep->add ?? '-' }}</p>
                        </div>
                        <div class="col-xs-6">
                            <p><strong>PD:</strong> {{ $resep->pd ?? '-' }}</p>
                        </div>
                    </div>
                    <hr>
                @endif

                @php
                    $isBPJS = $penjualan->pasien && in_array(strtolower($penjualan->pasien->service_type), ['bpjs i', 'bpjs ii', 'bpjs iii']);
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
                            <th>{{ $isBPJS ? 'Biaya BPJS' : 'Subtotal' }}</th>
                            <th>Biaya Tambahan</th>
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
                                    Rp {{ format_uang($penjualan->bpjs_default_price) }}
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
                                    <th style="width:50%">Biaya BPJS:</th>
                                    <td class="text-right">Rp {{ format_uang($penjualan->bpjs_default_price) }}</td>
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
<script>
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
</script>
@endpush
@endsection 