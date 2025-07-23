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
                <div class="row">
                    <div class="col-md-6">
                        <h4>Informasi Pasien</h4>
                        <table class="table">                            
                        <tr>
                                <th style="width: 30%;">Pasien</th>
                                <td>{{ $penjualan->pasien->nama_pasien ?? 'N/A' }}</td>
                            </tr>
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
                                <th>Dokter</th>
                                <td>{{ $penjualan->dokter->nama_dokter ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Informasi Transaksi</h4>
                        <table class="table">
                        <tr>
                                <th>Tanggal Transaksi</th>
                                <td>{{ tanggal_indonesia($penjualan->tanggal, false) }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Siap</th>
                                <td>{{ $penjualan->tanggal_siap ? tanggal_indonesia($penjualan->tanggal_siap, false) : '-' }}</td>
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

                @if($penjualan->pasien && $penjualan->pasien->prescriptions->isNotEmpty())
                    @php
                        $resep = $penjualan->pasien->prescriptions->last();
                    @endphp
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

                <h4>Detail Produk</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualan->details as $detail)
                        <tr>
                            <td>{{ $detail->itemable->merk_frame ?? $detail->itemable->merk_lensa ?? 'Produk tidak ditemukan' }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp {{ format_uang($detail->price) }}</td>
                            <td>Rp {{ format_uang($detail->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <hr>

                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                        <h4 class="text-right">Rincian Pembayaran</h4>
                        <table class="table">
                            <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td class="text-right">Rp {{ format_uang($penjualan->details->sum('subtotal')) }}</td>
                            </tr>
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
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
</script>
@endpush
@endsection 