@extends('layouts.master')

@section('title', 'Edit Transaksi Penjualan')

@section('content')
@if(isset($error_message))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h4><i class="icon fa fa-ban"></i> Peringatan!</h4>
    {{ $error_message }}
</div>
@endif

<form action="{{ route('penjualan.update', $penjualan->id) }}" method="POST" id="form-penjualan" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
    {{-- Right Column - Transaction Details --}}
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border"><h3 class="box-title">Detail Transaksi</h3></div>
                <div class="box-body">
                    <div class="form-group col-md-4">
                        <label for="kode_penjualan">Kode Transaksi</label>
                        <input type="text" class="form-control" name="kode_penjualan" value="{{ $penjualan->kode_penjualan }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal">Tanggal Transaksi</label>
                        <input type="text" class="form-control" name="tanggal" value="{{ $penjualan->created_at->format('Y-m-d') }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal_siap">Tanggal Siap</label>
                        <input type="date" class="form-control" name="tanggal_siap" value="{{ $penjualan->tanggal_siap }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Pasien</label>
                        <div class="input-group">
                            <input type="hidden" name="pasien_id" id="pasien_id" value="{{ $penjualan->pasien_id }}">
                            <input type="text" class="form-control" id="pasien_name" name="pasien_name" required placeholder="Pilih Pasien atau Input Manual" value="{{ $penjualan->pasien->nama_pasien ?? '' }}" style="border-radius: 25px; border: 2px solid #ddd; padding: 8px 15px; font-size: 14px;">
                            <span class="">
                                <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-pasien" style="border-radius: 20px; padding: 8px 20px; font-weight: bold; border: 2px solid #3c8dbc; margin-right: 5px;">Cari</button>
                                <button type="button" class="btn btn-sm btn-default" id="btn-input-manual-pasien" style="border-radius: 20px; padding: 8px 20px; font-weight: bold; border: 2px solid #95a5a6; background: linear-gradient(135deg, #ecf0f1, #bdc3c7);">Input Manual</button>
                            </span>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="dokter_id">Dokter</label>
                        <select name="dokter_id" id="dokter_id" class="form-control" style="border-radius: 25px; border: 2px solid #ddd; padding: 8px 15px; font-size: 14px;">
                            <option value="">Pilih Dokter</option>
                            @foreach($dokters as $dokter)
                                <option value="{{ $dokter->id_dokter }}" {{ $penjualan->dokter_id == $dokter->id_dokter ? 'selected' : '' }}>
                                    {{ $dokter->nama_dokter }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="dokter_manual">Dokter Manual</label>
                        <input type="text" class="form-control" id="dokter_manual" name="dokter_manual" placeholder="Nama dokter manual (opsional)" value="{{ $penjualan->dokter_manual }}" style="border-radius: 25px; border: 2px solid #ddd; padding: 8px 15px; font-size: 14px;">
                        <small class="text-muted">Isi jika dokter tidak ada di dropdown</small>
                    </div>
                    <div class="row" id="pasien-details-container" style="display: {{ $penjualan->pasien ? 'block' : 'none' }}; margin-bottom: 15px;">

                        <div class="col-md-6">
                            <div class="box box-info" style="margin-bottom:0;">
                                <div class="box-body" style="padding-bottom:10px;">
                                    <h4 style="margin-top:0;"><i class="fa fa-user"></i> <span id="detail-nama">{{ $penjualan->pasien->nama_pasien ?? '' }}</span></h4>
                                    <p style="margin-bottom:4px;"><strong>Alamat:</strong> <span id="detail-alamat">{{ $penjualan->pasien->alamat ?? '' }}</span></p>
                                    <p style="margin-bottom:4px;"><strong>No. HP:</strong> <span id="detail-nohp">{{ $penjualan->pasien->no_hp ?? '' }}</span></p>
                                    <p style="margin-bottom:4px;"><strong>Jenis Layanan:</strong> <span class="label label-info" id="detail-jenis_layanan">{{ $penjualan->pasien->jenis_layanan ?? '' }}</span></p>
                                    <p style="margin-bottom:4px;"><strong>No. BPJS:</strong> <span id="detail-no-bpjs">{{ $penjualan->pasien->no_bpjs ?? '' }}</span></p>
                                    <p style="margin-bottom:4px;"><strong>Dokter:</strong> <span id="detail-dokter">{{ $penjualan->dokter->nama_dokter ?? $penjualan->dokter_manual ?? '' }}</span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default" style="margin-bottom:0;">
                                <div class="panel-heading" style="padding:6px 10px; font-size:14px; background:#f5f5f5;">
                                    <b><i class="fa fa-stethoscope"></i> Resep Terakhir</b> 
                                    @if($latestPrescription)
                                        <span class="text-muted">({{ \Carbon\Carbon::parse($latestPrescription->tanggal)->format('d/m/Y') }})</span>
                                    @else
                                        <span class="text-muted">(Tidak ada resep)</span>
                                    @endif
                                </div>
                                <div class="panel-body" style="padding:8px 10px;">
                                    @if($latestPrescription)
                                        <table class="table table-bordered table-condensed text-center" style="margin-bottom:6px;">
                                            <thead>
                                                <tr class="bg-gray">
                                                    <th class="text-center" style="width: 20%;">Mata</th>
                                                    <th class="text-center" style="width: 20%;">SPH</th>
                                                    <th class="text-center" style="width: 20%;">CYL</th>
                                                    <th class="text-center" style="width: 20%;">AXIS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>OD (Kanan)</strong></td>
                                                    <td>{{ $latestPrescription->od_sph ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->od_cyl ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->od_axis ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>OS (Kiri)</strong></td>
                                                    <td>{{ $latestPrescription->os_sph ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->os_cyl ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->os_axis ?? '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="text-center text-muted" style="padding: 20px;">
                                            <i class="fa fa-info-circle"></i> Belum ada data resep untuk pasien ini
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction Items Section --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Item Transaksi</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="transaction-items">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($penjualan->details && $penjualan->details->count() > 0)
                                    @foreach($penjualan->details as $detail)
                                    <tr>
                                        <td>
                                            @if($detail->itemable)
                                                @if($detail->itemable_type === 'App\Models\Frame')
                                                    {{ $detail->itemable->merk_frame ?? 'N/A' }} - {{ $detail->itemable->jenis_frame ?? 'N/A' }}
                                                @elseif($detail->itemable_type === 'App\Models\Lensa')
                                                    {{ $detail->itemable->merk_lensa ?? 'N/A' }} - {{ $detail->itemable->type ?? 'N/A' }}
                                                @else
                                                    {{ $detail->itemable->nama ?? 'N/A' }}
                                                @endif
                                            @else
                                                Item tidak ditemukan
                                            @endif
                                        </td>
                                        <td>Rp. {{ number_format($detail->price ?? 0, 0, ',', '.') }}</td>
                                        <td>{{ $detail->quantity ?? 1 }}</td>
                                        <td>Rp. {{ number_format($detail->subtotal ?? 0, 0, ',', '.') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-danger" onclick="removeItem(this)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fa fa-info-circle"></i> Tidak ada item transaksi
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Section --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Informasi Pembayaran</h3>
                </div>
                <div class="box-body">
                    <div class="form-group col-md-4">
                        <label for="total">Total Transaksi</label>
                        <input type="text" class="form-control" name="total" value="Rp. {{ number_format($penjualan->total, 0, ',', '.') }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="bayar">Jumlah Bayar</label>
                        <input type="number" class="form-control" name="bayar" value="{{ $penjualan->bayar }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="kekurangan">Kekurangan</label>
                        <input type="text" class="form-control" name="kekurangan" value="{{ $penjualan->kekurangan }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="status">Status Pembayaran</label>
                        <select name="status" id="status" class="form-control">
                            <option value="Belum Lunas" {{ $penjualan->status == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="Lunas" {{ $penjualan->status == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="status_pengerjaan">Status Pengerjaan</label>
                        <select name="status_pengerjaan" id="status_pengerjaan" class="form-control">
                            <option value="Menunggu Pengerjaan" {{ $penjualan->status_pengerjaan == 'Menunggu Pengerjaan' ? 'selected' : '' }}>Menunggu Pengerjaan</option>
                            <option value="Sedang Dikerjakan" {{ $penjualan->status_pengerjaan == 'Sedang Dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                            <option value="Selesai Dikerjakan" {{ $penjualan->status_pengerjaan == 'Selesai Dikerjakan' ? 'selected' : '' }}>Selesai Dikerjakan</option>
                            <option value="Sudah Diambil" {{ $penjualan->status_pengerjaan == 'Sudah Diambil' ? 'selected' : '' }}>Sudah Diambil</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> Update Transaksi
                    </button>
                    <a href="{{ route('penjualan.index') }}" class="btn btn-default btn-lg">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Include necessary modals and scripts --}}
{{-- Modals will be added here if needed --}}

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize form with existing data
    initializeForm();
    
    // Handle payment calculation
    $('#bayar').on('input', function() {
        calculatePayment();
    });
    
    // Handle status change
    $('#status').on('change', function() {
        if ($(this).val() === 'Lunas') {
            $('#bayar').val($('#total').val().replace('Rp. ', '').replace(/\./g, ''));
            calculatePayment();
        }
    });
});

function initializeForm() {
    // Set initial values and states
    if ($('#pasien_id').val()) {
        $('#pasien-details-container').show();
    }
}

function calculatePayment() {
    const total = parseFloat($('#total').val().replace('Rp. ', '').replace(/\./g, ''));
    const bayar = parseFloat($('#bayar').val()) || 0;
    const kekurangan = total - bayar;
    
    $('#kekurangan').val(kekurangan.toFixed(0));
}

function removeItem(button) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
        $(button).closest('tr').remove();
    }
}
</script>
@endpush
