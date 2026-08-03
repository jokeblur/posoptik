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
                        @if(!empty($canEditTransactionDate))
                        <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ optional($penjualan->tanggal)->format('Y-m-d') ?? $penjualan->created_at->format('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        <small class="text-muted">Khusus admin/super admin: bisa ubah ke tanggal sebelumnya.</small>
                        @else
                        <input type="text" class="form-control" name="tanggal" id="tanggal" value="{{ optional($penjualan->tanggal)->format('Y-m-d') ?? $penjualan->created_at->format('Y-m-d') }}" readonly>
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal_siap">Tanggal Siap</label>
                        <input type="date" class="form-control" name="tanggal_siap" value="{{ $penjualan->tanggal_siap }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="jenis_transaksi">Jenis Transaksi</label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="form-control" style="border-radius: 25px; border: 2px solid #ddd; padding: 8px 15px; font-size: 14px;">
                            <option value="Stock" {{ $penjualan->jenis_transaksi === 'Stock' ? 'selected' : '' }}>Stock</option>
                            <option value="Gosok" {{ $penjualan->jenis_transaksi === 'Gosok' ? 'selected' : '' }}>Gosok</option>
                        </select>
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
                                    <p style="margin-bottom:4px;"><strong>Jenis Layanan:</strong> <span class="label label-info" id="detail-jenis_layanan">{{ $penjualan->pasien->service_type ?? '' }}</span></p>
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
                                                    <th class="text-center" style="width: 16%;">SPH</th>
                                                    <th class="text-center" style="width: 16%;">CYL</th>
                                                    <th class="text-center" style="width: 16%;">AXIS</th>
                                                    <th class="text-center" style="width: 16%;">ADD</th>
                                                    <th class="text-center" style="width: 16%;">PD</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>OD (Kanan)</strong></td>
                                                    <td>{{ $latestPrescription->od_sph ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->od_cyl ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->od_axis ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->add_kanan ?? $latestPrescription->add ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->pd_kanan ?? $latestPrescription->pd ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>OS (Kiri)</strong></td>
                                                    <td>{{ $latestPrescription->os_sph ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->os_cyl ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->os_axis ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->add_kiri ?? $latestPrescription->add ?? '-' }}</td>
                                                    <td>{{ $latestPrescription->pd_kiri ?? $latestPrescription->pd ?? '-' }}</td>
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
                    <div class="btn-group pull-right" style="margin-left: 8px;">
                        <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-frames">Cari Frame</button>
                    </div>
                    <div class="btn-group pull-right" style="margin-left: 8px;">
                        <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-lenses">Cari Lensa Stok</button>
                    </div>
                    <div class="btn-group pull-right" style="margin-left: 8px;">
                        <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-lenses-gosok">Cari Lensa Gosok</button>
                    </div>
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-aksesoris">Cari Aksesoris</button>
                    </div>
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
                            <tbody id="cart-table"></tbody>
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
                        <label for="total-display">Total Transaksi</label>
                        <input type="text" class="form-control" id="total-display" value="Rp. {{ number_format($penjualan->total, 0, ',', '.') }}" readonly>
                        <input type="hidden" name="total" id="total" value="{{ (int) $penjualan->total }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="diskon">Diskon</label>
                        <input type="number" class="form-control" id="diskon" name="diskon" value="{{ (int) $penjualan->diskon }}" min="0">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="bayar">Jumlah Bayar</label>
                        <input type="number" class="form-control" id="bayar" name="bayar" value="{{ (int) $penjualan->bayar }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="metode_pembayaran">Cara Pembayaran</label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                            <option value="cash" {{ strtolower((string) ($penjualan->metode_pembayaran ?? 'cash')) === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ strtolower((string) ($penjualan->metode_pembayaran ?? '')) === 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="qris" {{ strtolower((string) ($penjualan->metode_pembayaran ?? '')) === 'qris' ? 'selected' : '' }}>QRIS</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4" id="bank_transfer_group" style="display: none;">
                        <label for="bank_transfer">Bank Transfer</label>
                        <select name="bank_transfer" id="bank_transfer" class="form-control" disabled>
                            <option value="">Pilih Bank Transfer</option>
                            <option value="BNI" {{ strtoupper((string) ($penjualan->bank_transfer ?? '')) === 'BNI' ? 'selected' : '' }}>BNI</option>
                            <option value="BRI" {{ strtoupper((string) ($penjualan->bank_transfer ?? '')) === 'BRI' ? 'selected' : '' }}>BRI</option>
                            <option value="MANDIRI" {{ strtoupper((string) ($penjualan->bank_transfer ?? '')) === 'MANDIRI' ? 'selected' : '' }}>MANDIRI</option>
                            <option value="BSI" {{ strtoupper((string) ($penjualan->bank_transfer ?? '')) === 'BSI' ? 'selected' : '' }}>BSI</option>
                            <option value="BCA" {{ strtoupper((string) ($penjualan->bank_transfer ?? '')) === 'BCA' ? 'selected' : '' }}>BCA</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="kekurangan">Kekurangan</label>
                        <input type="text" class="form-control" id="kekurangan" name="kekurangan" value="{{ (int) $penjualan->kekurangan }}" readonly>
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
                            <option value="Sedang Mengerjakan" {{ $penjualan->status_pengerjaan == 'Sedang Mengerjakan' ? 'selected' : '' }}>Sedang Mengerjakan</option>
                            <option value="Lensa Di Pesan" {{ $penjualan->status_pengerjaan == 'Lensa Di Pesan' ? 'selected' : '' }}>Menunggu Pengerjaan</option>
                            <option value="Lensa Datang" {{ $penjualan->status_pengerjaan == 'Lensa Datang' ? 'selected' : '' }}>Lensa Datang</option>
                            <option value="Sudah Di Kerjakan" {{ $penjualan->status_pengerjaan == 'Sudah Di Kerjakan' ? 'selected' : '' }}>Sudah Di Kerjakan</option>
                            <option value="Kirim WA" {{ $penjualan->status_pengerjaan == 'Kirim WA' ? 'selected' : '' }}>Kirim WA</option>
                            <option value="Sudah Di Ambil" {{ $penjualan->status_pengerjaan == 'Sudah Di Ambil' ? 'selected' : '' }}>Sudah Di Ambil</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="transaction_status_display">Status Transaksi (BPJS)</label>
                        <input type="text" class="form-control" id="transaction_status_display" value="{{ $penjualan->transaction_status ?? 'Normal' }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="total_additional_cost_display">Biaya Tambahan BPJS</label>
                        <input type="text" class="form-control" id="total_additional_cost_display" value="Rp. {{ number_format((float) ($penjualan->total_additional_cost ?? 0), 0, ',', '.') }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="items" id="items-input">

    {{-- BPJS Evidence Section --}}
    <div class="row" id="bpjs-edit-section" style="display: none;">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Dokumen BPJS</h3>
                </div>
                <div class="box-body">
                    <div class="form-group col-md-6">
                        <label for="photo_bpjs">Foto Bukti BPJS</label>
                        <input type="file" name="photo_bpjs" id="photo_bpjs" class="form-control" accept="image/*">
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti foto bukti BPJS.</small>
                        @if($penjualan->photo_bpjs)
                            <div style="margin-top: 10px;">
                                <a href="{{ route('penjualan.bpjs-photo', $penjualan->id) }}" target="_blank" class="btn btn-xs btn-info">
                                    <i class="fa fa-image"></i> Lihat Foto Saat Ini
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label>Tanda Tangan BPJS</label>
                        <div style="margin-bottom: 8px;">
                            <canvas id="signature-canvas" width="420" height="180" style="border: 1px solid #ddd; border-radius: 6px; background: #fff; width: 100%; max-width: 420px; cursor: crosshair;"></canvas>
                            <input type="hidden" name="signature_bpjs" id="signature_bpjs" value="{{ $penjualan->signature_bpjs ?? '' }}">
                        </div>
                        <div>
                            <button type="button" class="btn btn-xs btn-warning" id="btn-clear-signature">
                                <i class="fa fa-eraser"></i> Hapus Tanda Tangan
                            </button>
                            <button type="button" class="btn btn-xs btn-primary" id="btn-save-signature">
                                <i class="fa fa-save"></i> Simpan Tanda Tangan ke Form
                            </button>
                        </div>
                        <small class="text-muted" style="display:block; margin-top: 6px;">Klik "Simpan Tanda Tangan ke Form" setelah tanda tangan digambar.</small>
                        @if($penjualan->signature_bpjs)
                            <div style="margin-top: 10px;">
                                <img src="{{ $penjualan->signature_bpjs }}" alt="Tanda Tangan Saat Ini" style="max-width: 240px; border: 1px solid #ddd; border-radius: 5px;">
                                <div><small class="text-muted">Tanda tangan saat ini</small></div>
                            </div>
                        @endif
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

@include('penjualan.modal_frame')
@include('penjualan.modal_lensa')
@include('penjualan.modal_lensa_gosok')
@include('penjualan.modal_aksesoris')
@include('penjualan.modal_pasien')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    initializeForm();
    initModalDataTables();

    function toggleBankTransferField() {
        const metodePembayaran = ($('#metode_pembayaran').val() || '').toLowerCase();
        const isTransfer = metodePembayaran === 'transfer';

        $('#bank_transfer_group').toggle(isTransfer);
        $('#bank_transfer').prop('disabled', !isTransfer);
        $('#bank_transfer').prop('required', isTransfer);

        if (!isTransfer) {
            $('#bank_transfer').val('');
        }
    }

    $('#metode_pembayaran').on('change', function() {
        toggleBankTransferField();
    });

    toggleBankTransferField();

    $('#diskon, #bayar').on('input', function() {
        renderCartAndTotals();
    });

    $('#status').on('change', function() {
        if ($(this).val() === 'Lunas') {
            $('#bayar').val($('#total').val());
            renderCartAndTotals();
        }
    });

    $('#refresh-lensa-stok').on('click', function() {
        $('#search-lensa-stok').val('');
        if (lensaStokTable) {
            lensaStokTable.ajax.reload();
        }
    });

    $('#toggle-show-outofstock-frame').on('change', function() {
        if (frameTable) {
            frameTable.draw();
        }
    });

    $('#search-lensa-stok').on('keyup', function() {
        if (lensaStokTable) {
            lensaStokTable.ajax.reload();
        }
    });

    $('#toggle-show-outofstock-lensa').on('change', function() {
        if (lensaStokTable) {
            lensaStokTable.ajax.reload();
        }
    });

    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();

        const type = ($(this).data('type') || '').toString();
        const id = Number($(this).data('id'));
        const name = ($(this).data('name') || '').toString();
        const price = Number($(this).data('price')) || 0;

        if (!id || !type || !name) {
            return;
        }

        if (type === 'frame') {
            const existingFrame = cart.find((item) => item.type === 'frame');
            if (existingFrame) {
                alert('Frame pada transaksi hanya boleh satu. Hapus frame lama terlebih dahulu.');
                return;
            }
        }

        const existingIndex = cart.findIndex((item) => item.type === type && Number(item.id) === id);
        if (existingIndex !== -1) {
            cart[existingIndex].quantity += 1;
        } else {
            cart.push({
                id: id,
                type: type,
                name: name,
                price: price,
                quantity: 1,
                jenis_frame: ($(this).data('jenis-frame') || '').toString(),
                lensaType: ($(this).data('lensa-jenis') || '').toString(),
                index: ($(this).data('index') || '').toString(),
                coating: ($(this).data('coating') || '').toString(),
                cly: ($(this).data('cly') || '').toString(),
                axis: ($(this).data('axis') || '').toString(),
                add: ($(this).data('add') || '').toString(),
            });
        }

        renderCartAndTotals();
    });

    $(document).on('change', '.cart-qty', function() {
        const index = Number($(this).data('index'));
        const quantity = Math.max(1, Number($(this).val()) || 1);
        if (typeof cart[index] !== 'undefined') {
            cart[index].quantity = quantity;
            renderCartAndTotals();
        }
    });

    $(document).on('click', '.remove-cart-item', function() {
        const index = Number($(this).data('index'));
        if (typeof cart[index] !== 'undefined') {
            cart.splice(index, 1);
            renderCartAndTotals();
        }
    });

    $(document).on('click', '.select-pasien', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        let url = "{{ route('pasien.details', ['id' => ':id']) }}";
        url = url.replace(':id', id);

        $('#pasien_id').val(id);
        $('#pasien_name').val(name);
        $('#modal-pasien').modal('hide');

        $.get(url)
            .done(function(response) {
                $('#detail-nama').text(response.nama_pasien || '-');
                $('#detail-alamat').text(response.alamat || '-');
                $('#detail-nohp').text(response.nohp || '-');
                $('#detail-jenis_layanan').text(response.service_type || '-');
                $('#detail-no-bpjs').text(response.no_bpjs || '-');
                $('#detail-dokter').text(response.dokter_nama || '-');
                $('#pasien-details-container').show();
                toggleBpjsEditSection();
                renderCartAndTotals();
            })
            .fail(function() {
                alert('Gagal mengambil detail pasien.');
            });
    });

    $('#form-penjualan').on('submit', function(e) {
        const metodePembayaran = ($('#metode_pembayaran').val() || '').toLowerCase();

        if (!metodePembayaran) {
            e.preventDefault();
            alert('Cara pembayaran wajib dipilih.');
            return;
        }

        if (metodePembayaran === 'transfer' && !$('#bank_transfer').val()) {
            e.preventDefault();
            alert('Silakan pilih bank transfer.');
            return;
        }

        $('#items-input').val(JSON.stringify(cart));
    });

    $(document).on('click', '#btn-reset-gosok-modal', function() {
        const form = $('#form-lensa-gosok-modal')[0];
        if (form) {
            form.reset();
        }
        $('#gosok_quantity_modal').val(1);
    });

    $(document).on('click', '#btn-add-gosok-modal', function() {
        const form = $('#form-lensa-gosok-modal')[0];
        if (!form) {
            return;
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const merk = $('#gosok_merk_modal').val();
        const lensaType = $('#gosok_type_modal').val() || '-';
        const indexValue = $('#gosok_index_modal').val() || '-';
        const coating = $('#gosok_coating_modal').val() || '-';
        const cly = $('#gosok_cly_modal').val() || '-';
        const axis = $('#gosok_axis_modal').val() || '-';
        const add = $('#gosok_add_modal').val() || '-';
        const harga = parseInt($('#gosok_harga_modal').val(), 10) || 0;
        const quantity = parseInt($('#gosok_quantity_modal').val(), 10) || 1;
        const catatan = $('#gosok_catatan_modal').val() || '';

        if (harga <= 0 || quantity <= 0) {
            alert('Harga dan jumlah lensa gosok harus lebih dari 0.');
            return;
        }

        cart.push({
            id: 'gosok_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
            type: 'lensa_gosok',
            name: `Lensa Gosok - ${merk}`,
            price: harga,
            quantity: quantity,
            catatan: catatan,
            merk: merk,
            lensaType: lensaType,
            index: indexValue,
            coating: coating,
            cly: cly,
            axis: axis,
            add: add
        });

        form.reset();
        $('#gosok_quantity_modal').val(1);
        $('#modal-lenses-gosok').modal('hide');
        renderCartAndTotals();
    });

    renderCartAndTotals();
});

@php
    $initialEditCart = ($penjualan->details ?? collect())
        ->map(function ($detail) {
            if (!$detail->itemable) {
                return null;
            }

            $type = 'aksesoris';
            $name = $detail->itemable->nama_produk ?? 'Produk';

            if ($detail->itemable_type === \App\Models\Frame::class) {
                $type = 'frame';
                $name = ($detail->itemable->merk_frame ?? 'Frame') . ' - ' . ($detail->itemable->jenis_frame ?? '-');
            } elseif ($detail->itemable_type === \App\Models\Lensa::class) {
                $type = 'lensa';
                $name = ($detail->itemable->merk_lensa ?? 'Lensa') . ' - ' . ($detail->itemable->type ?? '-');
            }

            return [
                'id' => $detail->itemable_id,
                'type' => $type,
                'name' => $name,
                'price' => (float) ($detail->price ?? 0),
                'quantity' => (int) ($detail->quantity ?? 1),
                'jenis_frame' => $detail->itemable->jenis_frame ?? '',
                'lensaType' => $detail->itemable->type ?? '',
                'index' => $detail->itemable->index ?? '',
                'coating' => $detail->itemable->coating ?? '',
                'cly' => $detail->itemable->cly ?? '',
                'axis' => $detail->itemable->axis ?? '',
                'add' => $detail->itemable->add ?? '',
            ];
        })
        ->filter()
        ->values()
        ->all();
@endphp

let cart = @json($initialEditCart);

let renderVersion = 0;
let lensaStokTable = null;
let frameTable = null;

$.fn.dataTable.ext.search.push(function(settings, data) {
    if (!settings || !settings.nTable || settings.nTable.id !== 'table-frames') {
        return true;
    }

    if ($('#toggle-show-outofstock-frame').is(':checked')) {
        return true;
    }

    const stokText = (data[3] || '').toString().replace(/<[^>]*>/g, '').replace(/[^0-9-]/g, '');
    const stok = parseInt(stokText, 10);
    return Number.isNaN(stok) ? true : stok > 0;
});

function initModalDataTables() {
    if ($.fn.DataTable.isDataTable('#table-frames')) {
        $('#table-frames').DataTable().destroy();
    }
    frameTable = $('#table-frames').DataTable();
    frameTable.draw();

    if ($.fn.DataTable.isDataTable('#table-aksesoris')) {
        $('#table-aksesoris').DataTable().destroy();
    }
    $('#table-aksesoris').DataTable();
}

function formatRupiah(value) {
    return 'Rp. ' + Number(value || 0).toLocaleString('id-ID');
}

function isBpjsPatient() {
    const serviceType = ($('#detail-jenis_layanan').text() || '').toUpperCase();
    return serviceType.includes('BPJS');
}

function getBpjsDefaultPrice() {
    const serviceType = ($('#detail-jenis_layanan').text() || '').toUpperCase();
    if (serviceType.includes('BPJS III') || serviceType.includes('BPJS 3')) return 165000;
    if (serviceType.includes('BPJS II') || serviceType.includes('BPJS 2')) return 220000;
    if (serviceType.includes('BPJS I') || serviceType.includes('BPJS 1')) return 330000;
    return 0;
}

function renderCartRows(frameCalculatedPrice = null) {
    const tbody = $('#cart-table');
    tbody.empty();

    if (!Array.isArray(cart) || cart.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted"><i class="fa fa-info-circle"></i> Keranjang kosong</td></tr>');
        return { subtotal: 0, total: 0, frameAdditionalCost: 0, transactionStatus: 'Normal' };
    }

    const isBpjs = isBpjsPatient();
    let subtotal = 0;
    let total = 0;
    let frameAdditionalCost = 0;
    let transactionStatus = 'Normal';

    cart.forEach((item, index) => {
        const quantity = Math.max(1, Number(item.quantity) || 1);
        const normalPrice = Number(item.price) || 0;

        let effectivePrice = normalPrice;
        if (isBpjs && item.type === 'frame' && frameCalculatedPrice !== null) {
            effectivePrice = Number(frameCalculatedPrice) || 0;
        }

        const itemSubtotal = effectivePrice * quantity;
        subtotal += normalPrice * quantity;
        total += itemSubtotal;

        const row = `
            <tr>
                <td>${item.name}</td>
                <td>${formatRupiah(effectivePrice)}</td>
                <td><input type="number" min="1" class="form-control cart-qty" data-index="${index}" value="${quantity}" style="width: 90px;"></td>
                <td>${formatRupiah(itemSubtotal)}</td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger remove-cart-item" data-index="${index}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.append(row);
    });

    if (isBpjs) {
        const defaultPrice = getBpjsDefaultPrice();
        const frameItem = cart.find((item) => item.type === 'frame');
        if (frameItem && frameCalculatedPrice !== null) {
            const normalFramePrice = Number(frameItem.price) || 0;
            frameAdditionalCost = Math.max(0, (normalFramePrice - defaultPrice) * (Number(frameItem.quantity) || 1));
            transactionStatus = frameAdditionalCost > 0 ? 'Naik Kelas' : 'Normal';
        }
    }

    return { subtotal, total, frameAdditionalCost, transactionStatus };
}

function applyTotalsToUi(subtotal, total, frameAdditionalCost, transactionStatus) {
    const diskon = Math.max(0, Number($('#diskon').val()) || 0);
    const bayar = Math.max(0, Number($('#bayar').val()) || 0);
    const finalTotal = Math.max(0, total - diskon);
    const kekurangan = finalTotal - bayar;

    $('#total').val(Math.round(finalTotal));
    $('#total-display').val(formatRupiah(finalTotal));
    $('#kekurangan').val(Math.round(kekurangan));
    $('#transaction_status_display').val(transactionStatus);
    $('#total_additional_cost_display').val(formatRupiah(frameAdditionalCost));

    if (kekurangan <= 0) {
        $('#status').val('Lunas');
    } else {
        $('#status').val('Belum Lunas');
    }
}

function renderCartAndTotals() {
    const currentVersion = ++renderVersion;
    const isBpjs = isBpjsPatient();
    const pasienId = $('#pasien_id').val();
    const frameItem = cart.find((item) => item.type === 'frame');

    if (isBpjs && pasienId && frameItem) {
        $.ajax({
            url: '{{ route("penjualan.calculate_bpjs_price") }}',
            method: 'POST',
            data: {
                pasien_id: pasienId,
                frame_id: frameItem.id,
                _token: '{{ csrf_token() }}'
            }
        }).done(function(response) {
            if (currentVersion !== renderVersion) {
                return;
            }

            const frameCalculatedPrice = response.success ? Number(response.data.calculated_price || 0) : null;
            const state = renderCartRows(frameCalculatedPrice);
            if (response.success) {
                state.frameAdditionalCost = Number(response.data.additional_cost || 0) * (Number(frameItem.quantity) || 1);
                state.transactionStatus = state.frameAdditionalCost > 0 ? 'Naik Kelas' : 'Normal';
            }
            applyTotalsToUi(state.subtotal, state.total, state.frameAdditionalCost, state.transactionStatus);
        }).fail(function() {
            if (currentVersion !== renderVersion) {
                return;
            }
            const state = renderCartRows(null);
            applyTotalsToUi(state.subtotal, state.total, state.frameAdditionalCost, state.transactionStatus);
        });
        return;
    }

    const state = renderCartRows(null);
    applyTotalsToUi(state.subtotal, state.total, state.frameAdditionalCost, state.transactionStatus);
}

function initializeForm() {
    if ($('#pasien_id').val()) {
        $('#pasien-details-container').show();
    }

    toggleBpjsEditSection();
    initSignatureCanvas();
}

function toggleBpjsEditSection() {
    const serviceTypeText = ($('#detail-jenis_layanan').text() || '').toLowerCase();
    const isBpjs = serviceTypeText.includes('bpjs');
    $('#bpjs-edit-section').toggle(isBpjs);
}

function initLensaStokTable() {
    if ($.fn.DataTable.isDataTable('#table-lenses-stok')) {
        $('#table-lenses-stok').DataTable().destroy();
    }

    lensaStokTable = $('#table-lenses-stok').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("penjualan.lensa-stok") }}',
            type: 'GET',
            data: function(d) {
                d.search = $('#search-lensa-stok').val();
                d.include_out_of_stock = $('#toggle-show-outofstock-lensa').is(':checked') ? 1 : 0;
            }
        },
        columns: [
            { data: 'kode_lensa', name: 'kode_lensa' },
            { data: 'merk_lensa', name: 'merk_lensa' },
            { data: 'type', name: 'type' },
            { data: 'index', name: 'index' },
            { data: 'coating', name: 'coating' },
            { data: 'cly', name: 'cly' },
            { data: 'add', name: 'add' },
            {
                data: 'stok',
                name: 'stok',
                render: function(data) {
                    const badgeClass = Number(data) > 0 ? 'label-success' : 'label-danger';
                    return '<span class="label ' + badgeClass + '">' + data + '</span>';
                }
            },
            { data: 'harga_formatted', name: 'harga_formatted' },
            {
                data: 'id',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const stok = Number(row.stok || 0);
                    if (stok <= 0) {
                        return '<button type="button" class="btn btn-default btn-sm" disabled>' +
                               '<i class="fa fa-ban"></i> Stok Habis</button>';
                    }

                    return '<a href="#" class="btn btn-primary btn-sm add-to-cart" ' +
                           'data-id="' + data + '" ' +
                           'data-name="' + row.merk_lensa + '" ' +
                           'data-price="' + row.harga_jual_lensa + '" ' +
                           'data-type="lensa" ' +
                           'data-lensa-jenis="' + (row.type || '') + '" ' +
                           'data-index="' + (row.index || '') + '" ' +
                           'data-coating="' + (row.coating || '') + '" ' +
                           'data-cly="' + (row.cly || '') + '" ' +
                           'data-axis="' + (row.axis || '') + '" ' +
                           'data-add="' + (row.add || '') + '">' +
                           '<i class="fa fa-plus"></i> Pilih</a>';
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: 'rtip',
        responsive: true,
        language: {
            processing: 'Memproses...',
            lengthMenu: 'Tampilkan _MENU_ data per halaman',
            zeroRecords: 'Tidak ada data lensa stok',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(disaring dari _MAX_ total data)',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Selanjutnya',
                previous: 'Sebelumnya'
            }
        }
    });
}

$('#modal-lenses').on('shown.bs.modal', function() {
    setTimeout(function() {
        initLensaStokTable();
    }, 100);
});

function initSignatureCanvas() {
    const canvas = document.getElementById('signature-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let drawing = false;

    ctx.strokeStyle = '#111';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function start(e) {
        drawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function move(e) {
        if (!drawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stop() {
        drawing = false;
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', stop);
    canvas.addEventListener('mouseleave', stop);

    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', stop);

    $('#btn-clear-signature').on('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        $('#signature_bpjs').val('');
    });

    $('#btn-save-signature').on('click', function() {
        const dataUrl = canvas.toDataURL('image/png');
        if (!dataUrl || dataUrl.length < 100) {
            alert('Silakan gambar tanda tangan terlebih dahulu.');
            return;
        }
        $('#signature_bpjs').val(dataUrl);
        alert('Tanda tangan berhasil disimpan ke form.');
    });
}

</script>
@endpush
