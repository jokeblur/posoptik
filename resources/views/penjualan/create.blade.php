@extends('layouts.master')

@section('title', 'Transaksi Penjualan')

<style>
    .equal-height-boxes {
        display: flex;
        flex-wrap: wrap;
    }
    
    .equal-height-boxes .col-md-6 {
        display: flex;
        margin-bottom: 15px;
    }
    
    .equal-height-boxes .box {
        width: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .equal-height-boxes .box-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        padding: 15px;
    }
    
    .info-card {
        background: rgba(60, 141, 188, 0.1);
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #3c8dbc;
        margin-bottom: 0;
        text-align: left;
    }
    
    .resep-card {
        background: rgba(40, 167, 69, 0.1);
        padding: 8px;
        border-radius: 5px;
        border: 1px solid rgba(40, 167, 69, 0.3);
        margin-bottom: 5px;
    }
    
    .pd-card {
        background: rgba(255, 193, 7, 0.1);
        padding: 8px;
        border-radius: 5px;
        border: 1px solid rgba(255, 193, 7, 0.3);
        margin-bottom: 5px;
    }
    
    .dokter-card {
        background: rgba(108, 117, 125, 0.1);
        padding: 8px;
        border-radius: 5px;
        border: 1px solid rgba(108, 117, 125, 0.3);
        margin-bottom: 5px;
    }
    
    @media (max-width: 768px) {
        .equal-height-boxes .col-md-6 {
            width: 100%;
            margin-bottom: 15px;
        }
    }
</style>

@section('content')
@if(isset($error_message))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h4><i class="icon fa fa-ban"></i> Peringatan!</h4>
    {{ $error_message }}
</div>
@endif

<form action="{{ route('penjualan.store') }}" method="POST" id="form-penjualan" enctype="multipart/form-data">
    @csrf
    <div class="row">
    {{-- Right Column - Transaction Details --}}
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border"><h3 class="box-title">Detail Transaksi</h3></div>
                <div class="box-body">
                    <div class="form-group col-md-4">
                        <label for="kode_penjualan">Kode Transaksi</label>
                        <input type="text" class="form-control" name="kode_penjualan" value="{{ 'MLT-' . time() }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal">Tanggal Transaksi</label>
                        <input type="text" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal_siap">Tanggal Siap</label>
                        <input type="date" class="form-control" name="tanggal_siap">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Pasien</label>
                        <div class="input-group">
                            <input type="hidden" name="pasien_id" id="pasien_id">
                            <input type="text" class="form-control" id="pasien_name" name="pasien_name" required placeholder="Pilih Pasien atau Input Manual" style="border-radius: 25px; border: 2px solid #ddd; padding: 8px 15px; font-size: 14px;">
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
                                <option value="{{ $dokter->id_dokter }}">{{ $dokter->nama_dokter }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="dokter_manual">Dokter Manual</label>
                        <input type="text" class="form-control" id="dokter_manual" name="dokter_manual" placeholder="Nama dokter manual (opsional)" style="border-radius: 25px; border: 2px solid #ddd; padding: 8px 15px; font-size: 14px;">
                        <small class="text-muted">Isi jika dokter tidak ada di dropdown</small>
                    </div>
                    <div class="form-group col-md-12">
                    <div class="row equal-height-boxes" id="pasien-details-container" style="display: none; margin-bottom: 15px;">
                        <div class="col-md-6">
                            <div class="box box-info" style="margin-bottom:0;">
                                <div class="box-header with-border">
                                    <h4 style="margin:0;"><i class="fa fa-user"></i> Informasi Pasien</h4>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 style="margin-top:0; margin-bottom:15px; color: #3c8dbc; text-align: left;"><strong><span id="detail-nama"></span></strong></h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="info-card">
                                                <p style="margin-bottom:8px;"><strong>📍 Alamat:</strong> <span id="detail-alamat"></span></p>
                                                <p style="margin-bottom:8px;"><strong>📱 No. HP:</strong> <span id="detail-nohp"></span></p>
                                                <p style="margin-bottom:8px;"><strong>🏥 Jenis Layanan:</strong> <span class="label label-info" id="detail-jenis_layanan"></span></p>
                                                <p style="margin-bottom:8px;"><strong>🆔 No. BPJS:</strong> <span id="detail-no-bpjs"></span></p>
                                                <p style="margin-bottom:8px;"><strong>👨‍⚕️ Dokter:</strong> <span id="detail-dokter"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="box box-success" style="margin-bottom:0;">
                                <div class="box-header with-border">
                                    <h4 style="margin:0;"><i class="fa fa-stethoscope"></i> Resep Terakhir</h4>
                                    <small class="text-muted">(<span id="resep-tanggal"></span>)</small>
                                </div>
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-condensed text-center" style="margin-bottom:15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <thead>
                                                <tr class="bg-success" style="color: white;">
                                                    <th class="text-center" style="width: 20%;">👁️ Mata</th>
                                                    <th class="text-center" style="width: 20%;">🔍 SPH</th>
                                                    <th class="text-center" style="width: 20%;">🔬 CYL</th>
                                                    <th class="text-center" style="width: 20%;">📐 AXIS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>OD (Kanan)</strong></td>
                                                    <td id="resep-od-sph"></td>
                                                    <td id="resep-od-cyl"></td>
                                                    <td id="resep-od-axis"></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>OS (Kiri)</strong></td>
                                                    <td id="resep-os-sph"></td>
                                                    <td id="resep-os-cyl"></td>
                                                    <td id="resep-os-axis"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-md-6 text-center">
                                            <div class="resep-card">
                                                <strong style="color: #28a745;">➕ ADD:</strong> <span id="resep-add" style="color: #28a745;"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <div class="pd-card">
                                                <strong style="color: #856404;">📏 PD:</strong> <span id="resep-pd" style="color: #856404;"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <div class="dokter-card">
                                                <strong style="color: #6c757d;">👨‍⚕️ Dokter:</strong> <span id="resep-dokter" style="color: #6c757d;"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
</div>
                </div>
            </div>            
        </div>
        <!-- <div class="col-md-4">
        <div class="box">
                 <div class="box-header with-border"><h3 class="box-title">Pelanggan & Dokter</h3></div>
                 <div class="box-body">
                
                    <div class="form-group">
                        <label for="dokter_id">Dokter</label>
                        <select name="dokter_id" id="dokter_id" class="form-control">
                            <option value="">Pilih Dokter</option>
                             @foreach($dokters as $dokter)
                                <option value="{{ $dokter->id_dokter }}">{{ $dokter->nama_dokter }}</option>
                            @endforeach
                        </select>
                    </div>
                 </div>
            </div>
        </div> -->
    
    {{-- Left Column - Products & Cart --}}
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-frames">Cari Frame</button>
                       </div>
                    <div class="btn-group">                        
                        <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-lenses">Cari Lensa</button>
                        
                    </div>
                    <div class="btn-group">                        
                       
                        <button type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#modal-aksesoris">Cari Aksesoris</button>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th width="5%">No</th> -->
                                <th>Produk</th>
                                <th width="15%">Jumlah</th>
                                <th width="20%">Harga</th>
                                <th width="20%">Subtotal</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cart-table">
                            {{-- Cart items --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

       
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="diskon">Diskon (Rp)</label>
                                <input type="number" name="diskon" id="diskon" class="form-control" value="0">
                            </div>
                            <div class="form-group">
                                <label for="bayar">Jumlah Bayar (Rp)</label>
                                <input type="number" name="bayar" id="bayar" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <h4 style="font-size: large;">Subtotal: <span id="subtotal-amount">Rp 0</span></h4>
                            <h2 style="font-weight: bold;">Total: <span id="total-amount">Rp 0</span></h2>
                            <h3 id="kekurangan-container" style="font-weight: bold; display: none;">Kekurangan: <span id="kekurangan-amount">Rp 0</span></h3>
                            <h3 id="kembalian-container" style="font-weight: bold; display: none;">Kembalian: <span id="kembalian-amount">Rp 0</span></h3>
                            <div id="bpjs-summary" style="display: none; margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                                <h5 style="margin-top: 0;"><i class="fa fa-calculator"></i> Ringkasan BPJS</h5>
                                <div id="bpjs-summary-details"></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group" id="photo-bpjs-container" style="display: none;">
                        <label for="photo_bpjs">Foto Bukti BPJS</label>
                        <div class="input-group">
                            <input type="file" name="photo_bpjs" id="photo_bpjs" class="form-control" accept="image/*" capture="environment">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" id="btn-open-webcam" data-toggle="modal" data-target="#modal-webcam">
                                    <i class="fa fa-camera"></i> Buka Webcam
                                </button>
                            </span>
                        </div>
                        <p class="help-block">Wajib diisi untuk pasien BPJS. Ambil foto langsung atau dari webcam.</p>
                    </div>
                    
                    <div class="form-group" id="signature-bpjs-container" style="display: none;">
                        <label for="signature_bpjs">Tanda Tangan Pasien BPJS</label>
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="signature-canvas" width="400" height="200" style="border: 2px solid #ddd; border-radius: 8px; cursor: crosshair; background: #fff;"></canvas>
                                <input type="hidden" name="signature_bpjs" id="signature_bpjs">
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group-vertical" style="width: 100%;">
                                    <button type="button" class="btn btn-warning btn-sm" id="btn-clear-signature" style="margin-bottom: 5px;">
                                        <i class="fa fa-eraser"></i> Hapus
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" id="btn-save-signature" style="margin-bottom: 5px;">
                                        <i class="fa fa-save"></i> Simpan
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="btn-load-signature">
                                        <i class="fa fa-upload"></i> Upload
                                    </button>
                                </div>
                                <div style="margin-top: 10px;">
                                    <small class="text-muted">
                                        <i class="fa fa-info-circle"></i> Tanda tangan wajib untuk pasien BPJS
                                    </small>
                                </div>
                            </div>
                        </div>
                        <p class="help-block">Gambar tanda tangan pasien di atas canvas atau upload file tanda tangan.</p>
                    </div>
                    <input type="hidden" name="total" id="total-input">
                    <input type="hidden" name="kekurangan" id="kekurangan-input">
                    <input type="hidden" name="items" id="items-input">
                    <button type="submit" class="btn btn-sm btn-custom">Simpan Transaksi</button>
                </div>
            </div>
        </div>
    </div>
</form>

@include('penjualan.modal_frame')
@include('penjualan.modal_lensa')
@include('penjualan.modal_aksesoris')
@include('penjualan.modal_pasien')
@include('penjualan.modal_webcam')
<!-- Modal Input Manual Pasien -->
<div class="modal fade" id="modal-input-manual-pasien" tabindex="-1" role="dialog" aria-labelledby="modal-input-manual-pasien-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-input-manual-pasien-label">Input Manual Nama Pasien</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="manual-pasien-name">Nama Pasien</label>
                                            <input type="text" class="form-control" id="manual-pasien-name" placeholder="Masukkan nama pasien" style="border-radius: 25px; border: 2px solid #ddd; padding: 8px 15px; font-size: 14px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-custom" id="btn-simpan-manual-pasien">Pilih</button>
                <button type="button" class="btn btn-sm btn-custom-close" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    // Initialize DataTables
    $('#table-frames').DataTable();
    $('#table-lenses').DataTable();
    $('#table-aksesoris').DataTable();
    $('#table-pasien').DataTable();

    let cart = [];
    let currentBPJSLevel = null; // Variabel global untuk menyimpan level BPJS
    
    // Signature functionality
    let isDrawing = false;
    let signatureCanvas = document.getElementById('signature-canvas');
    let signatureCtx = signatureCanvas.getContext('2d');
    
    // Initialize signature canvas
    if (signatureCanvas) {
        signatureCtx.strokeStyle = '#000';
        signatureCtx.lineWidth = 2;
        signatureCtx.lineCap = 'round';
        signatureCtx.lineJoin = 'round';
    }

    // Auto-fill pasien data jika ada selected_pasien dari form pasien
    @if(isset($selected_pasien) && $selected_pasien)
        // Auto-fill data pasien
        $('#pasien_id').val('{{ $selected_pasien->id_pasien }}');
        $('#pasien_name').val('{{ $selected_pasien->nama_pasien }}');
        
        // Set dokter jika ada
        @if($selected_pasien->prescriptions && $selected_pasien->prescriptions->count() > 0)
            @php
                $latestPrescription = $selected_pasien->prescriptions->last();
            @endphp
            @if($latestPrescription->dokter_id)
                $('#dokter_id').val('{{ $latestPrescription->dokter_id }}');
                $('#dokter_manual').val('');
            @elseif($latestPrescription->dokter_manual)
                $('#dokter_id').val('');
                $('#dokter_manual').val('{{ $latestPrescription->dokter_manual }}');
            @endif
        @endif
        
        // Tampilkan detail pasien
        $('#detail-nama').text('{{ $selected_pasien->nama_pasien }}');
        $('#detail-alamat').text('{{ $selected_pasien->alamat }}');
        $('#detail-nohp').text('{{ $selected_pasien->nohp }}');
        $('#detail-jenis_layanan').text('{{ $selected_pasien->service_type }}');
        $('#detail-no-bpjs').text('{{ $selected_pasien->no_bpjs ?? "-" }}');
        
        // Tampilkan resep jika ada
        @if($selected_pasien->prescriptions && $selected_pasien->prescriptions->count() > 0)
            @php
                $latestPrescription = $selected_pasien->prescriptions->last();
            @endphp
            $('#resep-tanggal').text('{{ $latestPrescription->tanggal }}');
            $('#resep-od-sph').text('{{ $latestPrescription->od_sph ?? "-" }}');
            $('#resep-od-cyl').text('{{ $latestPrescription->od_cyl ?? "-" }}');
            $('#resep-od-axis').text('{{ $latestPrescription->od_axis ?? "-" }}');
            $('#resep-os-sph').text('{{ $latestPrescription->os_sph ?? "-" }}');
            $('#resep-os-cyl').text('{{ $latestPrescription->os_cyl ?? "-" }}');
            $('#resep-os-axis').text('{{ $latestPrescription->os_axis ?? "-" }}');
            $('#resep-add').text('{{ $latestPrescription->add ?? "-" }}');
            $('#resep-pd').text('{{ $latestPrescription->pd ?? "-" }}');
            $('#resep-dokter').text('{{ $latestPrescription->dokter_manual ?? ($latestPrescription->dokter->nama_dokter ?? "-") }}');
            $('#detail-dokter').text('{{ $latestPrescription->dokter_manual ?? ($latestPrescription->dokter->nama_dokter ?? "-") }}');
        @else
            $('#resep-tanggal').text('N/A');
            $('#resep-od-sph, #resep-od-cyl, #resep-od-axis, #resep-os-sph, #resep-os-cyl, #resep-os-axis, #resep-add, #resep-pd, #resep-dokter').text('-');
            $('#detail-dokter').text('-');
        @endif
        
        // Tampilkan kontainer detail
        $('#pasien-details-container').slideDown();
        
        // Tampilkan input foto BPJS dan tanda tangan jika layanan BPJS
        @if(str_contains(strtolower($selected_pasien->service_type), 'bpjs'))
            $('#photo-bpjs-container').slideDown();
            $('#photo_bpjs').prop('required', true);
            $('#signature-bpjs-container').slideDown();
            $('#signature_bpjs').prop('required', true);
        @endif
        
        // Tampilkan pesan sukses
        Swal.fire({
            icon: 'success',
            title: 'Data Pasien Berhasil Diisi!',
            text: 'Data pasien {{ $selected_pasien->nama_pasien }} telah otomatis diisi. Silakan lanjutkan dengan memilih produk.',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    // All event listeners for patient & product selection remain the same...

    // Select Pasien
    $(document).on('click', '.select-pasien', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let name = $(this).data('name');
        let url = "{{ route('pasien.details', ['id' => ':id']) }}";
        url = url.replace(':id', id);

        $('#pasien_id').val(id);
        $('#pasien_name').val(name);
        $('#modal-pasien').modal('hide');
        
        // AJAX call to get patient details and prescriptions
        $.get(url)
            .done(function(response) {
                // Tampilkan detail pasien
                $('#detail-nama').text(response.nama_pasien || '-');
                $('#detail-alamat').text(response.alamat || '-');
                $('#detail-nohp').text(response.nohp || '-');
                $('#detail-jenis_layanan').text(response.service_type || '-');
                $('#detail-no-bpjs').text(response.no_bpjs || '-');
                $('#detail-dokter').text(response.dokter_nama || '-');
                
                // Set dokter di dropdown jika ada
                if (response.dokter_id) {
                    $('#dokter_id').val(response.dokter_id);
                    $('#dokter_manual').val(''); // Kosongkan dokter manual
                } else if (response.dokter_nama && response.dokter_nama !== '-') {
                    $('#dokter_id').val(''); // Kosongkan dropdown
                    $('#dokter_manual').val(response.dokter_nama); // Set ke manual
                } else {
                    $('#dokter_id').val('');
                    $('#dokter_manual').val('');
                }

                // Logika untuk menampilkan input foto BPJS dan tanda tangan
                if (response.service_type && response.service_type.toLowerCase().includes('bpjs')) {
                    $('#photo-bpjs-container').slideDown();
                    $('#photo_bpjs').prop('required', true);
                    $('#signature-bpjs-container').slideDown();
                    $('#signature_bpjs').prop('required', true);
                } else {
                    $('#photo-bpjs-container').slideUp();
                    $('#photo_bpjs').prop('required', false);
                    $('#signature-bpjs-container').slideUp();
                    $('#signature_bpjs').prop('required', false);
                }
                
                // Tampilkan resep dengan format baru
                if (response.prescriptions && response.prescriptions.length > 0) {
                    let resep = response.prescriptions[response.prescriptions.length - 1]; // Ambil resep terakhir
                    
                    $('#resep-tanggal').text(resep.tanggal);
                    $('#resep-od-sph').text(resep.od_sph || '-');
                    $('#resep-od-cyl').text(resep.od_cyl || '-');
                    $('#resep-od-axis').text(resep.od_axis || '-');
                    $('#resep-os-sph').text(resep.os_sph || '-');
                    $('#resep-os-cyl').text(resep.os_cyl || '-');
                    $('#resep-os-axis').text(resep.os_axis || '-');
                    $('#resep-add').text(resep.add || '-');
                    $('#resep-pd').text(resep.pd || '-');
                    $('#resep-dokter').text(response.prescriptions?.[response.prescriptions.length-1]?.dokter_nama || '-');

                } else {
                    // Jika tidak ada resep, kosongkan semua field
                    $('#resep-tanggal').text('N/A');
                    $('#resep-od-sph, #resep-od-cyl, #resep-od-axis, #resep-os-sph, #resep-os-cyl, #resep-os-axis, #resep-add, #resep-pd, #resep-dokter').text('-');
                }

                // Tampilkan kontainer detail
                $('#pasien-details-container').slideDown();
                
                // Simpan level BPJS ke variabel global jika ada
                if (response.service_type && response.service_type.toLowerCase().includes('bpjs')) {
                    let serviceTypeLower = response.service_type.toLowerCase();
                    let bpjsLevel = '';
                    
                    // Deteksi level BPJS dengan lebih spesifik
                    if (serviceTypeLower.includes('bpjs iii') || serviceTypeLower.includes('bpjs 3')) {
                        bpjsLevel = 'III';
                    } else if (serviceTypeLower.includes('bpjs ii') || serviceTypeLower.includes('bpjs 2')) {
                        bpjsLevel = 'II';
                    } else if (serviceTypeLower.includes('bpjs i') || serviceTypeLower.includes('bpjs 1')) {
                        bpjsLevel = 'I';
                    }
                    
                    // Simpan level BPJS ke variabel global
                    currentBPJSLevel = bpjsLevel;
                } else {
                    currentBPJSLevel = '';
                }
                
                // Recalculate totals after patient selection
                renderCartAndTotals();
            })
            .fail(function() {
                alert('Gagal mengambil detail pasien.');
            });
    });
    
    // Add item to cart from modal
    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        let product = { 
            id: $(this).data('id'), 
            name: $(this).data('name'), 
            price: parseFloat($(this).data('price')), 
            type: $(this).data('type'), 
            quantity: 1,
            jenis_frame: $(this).data('jenis-frame') || ''
        };
        
        // Validasi untuk BPJS: hanya boleh ada 1 frame dan 1 lensa
        let serviceType = $('#detail-jenis_layanan').text().toLowerCase();
        let isBPJS = serviceType.includes('bpjs');
        
        if (isBPJS) {
            let existingFrame = cart.find(item => item.type === 'frame');
            let existingLensa = cart.filter(item => item.type === 'lensa');
            
            if (product.type === 'frame' && existingFrame) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Pasien BPJS hanya boleh memilih 1 frame.',
                });
                return;
            }
            
            if (product.type === 'lensa' && existingLensa.length >= 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Pasien BPJS hanya boleh memilih maksimal 2 lensa.',
                });
                return;
            }
        }
        
        addToCart(product);
        $(this).closest('.modal').modal('hide');
    });

    function addToCart(product) {
        let existingItem = cart.find(item => item.id === product.id && item.type === product.type);
        if (existingItem) { 
            existingItem.quantity++; 
        } else { 
            cart.push(product); 
        }
        
        // Validasi komposisi cart untuk BPJS
        validateBPJSCart();
        
        $('#bayar').data('user-has-changed', false); // Reset
        renderCartAndTotals();
    }
    
    function validateBPJSCart() {
        let serviceType = $('#detail-jenis_layanan').text().toLowerCase();
        let isBPJS = serviceType.includes('bpjs');
        
        if (isBPJS) {
            let frameItems = cart.filter(item => item.type === 'frame');
            let lensaItems = cart.filter(item => item.type === 'lensa');
            
            // Tampilkan peringatan jika ada terlalu banyak frame
            if (frameItems.length > 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Pasien BPJS hanya boleh memilih 1 frame. Frame tambahan akan dihapus.',
                });
                // Hapus frame tambahan, sisakan yang pertama
                let firstFrame = frameItems[0];
                cart = cart.filter(item => item.type !== 'frame');
                cart.push(firstFrame);
            }
            
            // Tampilkan peringatan jika ada terlalu banyak lensa (maksimal 2)
            if (lensaItems.length > 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Pasien BPJS hanya boleh memilih maksimal 2 lensa. Lensa tambahan akan dihapus.',
                });
                // Hapus lensa tambahan, sisakan 2 yang pertama
                let firstTwoLensa = lensaItems.slice(0, 2);
                cart = cart.filter(item => item.type !== 'lensa');
                firstTwoLensa.forEach(lensa => cart.push(lensa));
            }
        }
    }
    
    function showBPJSSummary(pasienServiceType, calculatedPrice, lensaItems, aksesorisItems) {
        let summary = `<p><strong>Jenis Layanan:</strong> ${pasienServiceType}</p>`;
        summary += `<p><strong>Harga Dihitung:</strong> Rp ${calculatedPrice.toLocaleString('id-ID')}</p>`;
        
        if (lensaItems.length > 0) {
            summary += `<p><strong>Lensa:</strong> ${lensaItems.length} item`;
            if (lensaItems.length > 1) {
                summary += ` (${lensaItems.map(l => l.name).join(', ')})`;
            } else {
                summary += ` (${lensaItems[0].name})`;
            }
            summary += `</p>`;
        }
        
        if (aksesorisItems.length > 0) {
            summary += `<p><strong>Aksesoris:</strong> ${aksesorisItems.length} item</p>`;
        }
        
        $('#bpjs-summary-details').html(summary);
        $('#bpjs-summary').show();
    }

    function renderCartAndTotals() {
        let cartTable = $('#cart-table');
        cartTable.empty();
        let subtotal = 0;
        let total = 0;

        // Ambil jenis layanan pasien
        let serviceType = $('#detail-jenis_layanan').text().toLowerCase();
        let isBPJS = serviceType.includes('bpjs');
        let bpjsLevel = currentBPJSLevel; // Gunakan level BPJS yang sudah disimpan
        
        // Jika belum ada level BPJS yang tersimpan, deteksi dari service type
        if (isBPJS && !bpjsLevel) {
            if (serviceType.includes('bpjs iii') || serviceType.includes('bpjs 3')) {
                bpjsLevel = 'III';
            } else if (serviceType.includes('bpjs ii') || serviceType.includes('bpjs 2')) {
                bpjsLevel = 'II';
            } else if (serviceType.includes('bpjs i') || serviceType.includes('bpjs 1')) {
                bpjsLevel = 'I';
            }
        }

        if (cart.length === 0) {
            cartTable.append('<tr><td colspan="5" class="text-center">Keranjang kosong</td></tr>');
        } else {
            // Hitung subtotal normal terlebih dahulu
            cart.forEach((item, index) => {
                let itemSubtotal = item.price * item.quantity;
                subtotal += itemSubtotal;
                let row = `<tr><td>${item.name}</td><td><input type="number" class="form-control quantity-input" value="${item.quantity}" data-index="${index}" min="1" style="width: 70px;"></td><td>Rp ${item.price.toLocaleString('id-ID')}</td><td>Rp ${itemSubtotal.toLocaleString('id-ID')}</td><td><button class="btn btn-danger btn-sm remove-item" data-index="${index}">&times;</button></td></tr>`;
                cartTable.append(row);
            });

            // Terapkan logika pricing BPJS menggunakan API
            if (isBPJS && bpjsLevel) {
                let frameItems = cart.filter(item => item.type === 'frame');
                let lensaItems = cart.filter(item => item.type === 'lensa');
                let aksesorisItems = cart.filter(item => item.type === 'aksesoris');
                
                // Default harga berdasarkan level BPJS
                let defaultPrice = 0;
                switch(bpjsLevel) {
                    case 'I': defaultPrice = 330000; break;
                    case 'II': defaultPrice = 220000; break;
                    case 'III': defaultPrice = 165000; break;
                }

                // Jika ada frame dan lensa, gunakan API untuk kalkulasi
                if (frameItems.length > 0 && lensaItems.length > 0) {
                    let frameItem = frameItems[0];
                    let pasienId = $('#pasien_id').val();
                    
                    // Gunakan API untuk kalkulasi harga BPJS
                    if (pasienId && frameItem.id) {
                        console.log('Calling BPJS pricing API:', {
                            pasien_id: pasienId,
                            frame_id: frameItem.id
                        });
                        
                        $.ajax({
                            url: '{{ route("penjualan.calculate_bpjs_price") }}',
                            method: 'POST',
                            data: {
                                pasien_id: pasienId,
                                frame_id: frameItem.id,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log('BPJS pricing API response:', response);
                                if (response.success) {
                                    // Update harga frame berdasarkan hasil API
                                    frameItem.price = response.data.calculated_price;
                                    
                                    // Hitung total dengan harga yang sudah dikalkulasi
                                    let totalLensaPrice = 0;
                                    lensaItems.forEach(lensa => {
                                        totalLensaPrice += lensa.price * lensa.quantity;
                                    });
                                    
                                    total = response.data.calculated_price + totalLensaPrice;
                                    
                                    // Tambahkan harga aksesoris
                                    aksesorisItems.forEach(item => {
                                        total += item.price * item.quantity;
                                    });
                                    
                                    // Tampilkan informasi pricing
                                    displayBPJSPricingInfo(response.data, defaultPrice, lensaItems, aksesorisItems);
                                    
                                    // Update total di UI
                                    updateTotalDisplay(total);
                                } else {
                                    console.error('BPJS pricing API error:', response.message);
                                    calculateBPJSPriceFallback(frameItems, lensaItems, aksesorisItems, bpjsLevel, defaultPrice);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('BPJS pricing API failed:', {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                                // Fallback ke logika lama jika API gagal
                                calculateBPJSPriceFallback(frameItems, lensaItems, aksesorisItems, bpjsLevel, defaultPrice);
                            }
                        });
                    } else {
                        console.log('Skipping BPJS API call - missing data:', {
                            pasienId: pasienId,
                            frameId: frameItem.id
                        });
                        // Fallback jika tidak ada pasien_id atau frame_id
                        calculateBPJSPriceFallback(frameItems, lensaItems, aksesorisItems, bpjsLevel, defaultPrice);
                    }
                } else {
                    // Jika tidak ada frame atau lensa, gunakan default price
                    total = defaultPrice;
                    
                    // Tambahkan harga aksesoris jika ada
                    aksesorisItems.forEach(item => {
                        total += item.price * item.quantity;
                    });
                    
                    // Tampilkan informasi pricing default
                    displayBPJSPricingInfo({
                        pasien_service_type: 'BPJS ' + bpjsLevel,
                        frame_type: 'N/A',
                        calculated_price: defaultPrice,
                        additional_cost: 0,
                        reason: 'Harga default BPJS ' + bpjsLevel
                    }, defaultPrice, [], aksesorisItems);
                    
                    updateTotalDisplay(total);
                }
            } else {
                // Non-BPJS: gunakan subtotal normal
                total = subtotal;
                $('#bpjs-summary').hide();
                updateTotalDisplay(total);
            }
        }
    }
    
    function calculateBPJSPriceFallback(frameItems, lensaItems, aksesorisItems, bpjsLevel, defaultPrice) {
        let frameItem = frameItems[0];
        let frameJenis = frameItem.jenis_frame || '';
        let isFrameBPJS = frameJenis.includes('BPJS');
        let frameLevel = null;
        
        if (isFrameBPJS) {
            if (frameJenis.includes('BPJS III')) frameLevel = 'III';
            else if (frameJenis.includes('BPJS II')) frameLevel = 'II';
            else if (frameJenis.includes('BPJS I')) frameLevel = 'I';
        }

        let total = 0;
        if (isFrameBPJS && frameLevel) {
            // Logika penambahan untuk frame BPJS
            let additionalCost = 0;
            
            if (bpjsLevel === 'III') {
                if (frameLevel === 'II') additionalCost = 55000;
                else if (frameLevel === 'I') additionalCost = 165000;
            } else if (bpjsLevel === 'II' && frameLevel === 'I') {
                additionalCost = 110000;
            }
            
            total = defaultPrice + additionalCost;
        } else {
            // Frame umum: harga frame + total harga lensa
            let totalLensaPrice = 0;
            lensaItems.forEach(lensa => {
                totalLensaPrice += lensa.price * lensa.quantity;
            });
            total = frameItem.price + totalLensaPrice;
        }
        
        // Tambahkan harga aksesoris
        aksesorisItems.forEach(item => {
            total += item.price * item.quantity;
        });
        
        updateTotalDisplay(total);
    }
    
    function displayBPJSPricingInfo(apiData, defaultPrice, lensaItems, aksesorisItems) {
        let cartTable = $('#cart-table');
        
        // Tampilkan informasi pricing di cart
        let pricingInfo = `<tr class="info"><td colspan="3"><strong>Pricing BPJS:</strong></td><td colspan="2">${apiData.pasien_service_type} - ${apiData.reason}</td></tr>`;
        cartTable.append(pricingInfo);
        
        // Tampilkan informasi tambahan jika ada penambahan biaya
        if (apiData.additional_cost > 0) {
            let additionalInfo = '';
            if (apiData.frame_type === 'Umum') {
                additionalInfo = `<tr class="warning"><td colspan="3"><strong>BPJS Menanggung:</strong></td><td colspan="2">Rp ${apiData.additional_cost.toLocaleString('id-ID')}</td></tr>`;
            } else {
                additionalInfo = `<tr class="warning"><td colspan="3"><strong>Penambahan Biaya:</strong></td><td colspan="2">+ Rp ${apiData.additional_cost.toLocaleString('id-ID')}</td></tr>`;
            }
            cartTable.append(additionalInfo);
        }
        
        // Tampilkan informasi jumlah lensa
        if (lensaItems.length > 1) {
            let lensaInfo = `<tr class="info"><td colspan="3"><strong>Jumlah Lensa:</strong></td><td colspan="2">${lensaItems.length} lensa</td></tr>`;
            cartTable.append(lensaInfo);
        }
        
        // Tampilkan ringkasan BPJS
        showBPJSSummary(apiData.pasien_service_type, apiData.calculated_price, lensaItems, aksesorisItems);
    }
    
    function updateTotalDisplay(total) {
        let diskon = parseFloat($('#diskon').val()) || 0;
        total = total - diskon;

        // Atur default jumlah bayar sama dengan total, hanya jika belum diubah manual oleh user
        let bayarInput = $('#bayar');
        if (!bayarInput.data('user-has-changed')) {
            bayarInput.val(total);
        }
        
        let bayar = parseFloat(bayarInput.val()) || 0;
        let selisih = bayar - total;

        // Tampilkan kekurangan atau kembalian
        if (selisih < 0) {
            $('#kekurangan-container').show();
            $('#kembalian-container').hide();
            $('#kekurangan-amount').text('Rp ' + Math.abs(selisih).toLocaleString('id-ID'));
            $('#kekurangan-input').val(Math.abs(selisih));
        } else {
            $('#kekurangan-container').hide();
            $('#kembalian-container').show();
            $('#kembalian-amount').text('Rp ' + selisih.toLocaleString('id-ID'));
            $('#kekurangan-input').val(0);
        }

        $('#subtotal-amount').text('Rp ' + total.toLocaleString('id-ID'));
        $('#total-amount').text('Rp ' + total.toLocaleString('id-ID'));
        $('#total-input').val(total);
        $('#items-input').val(JSON.stringify(cart));
    }

    // Tandai jika user sudah mengubah input bayar secara manual
    $('#bayar').on('input', function() {
        $(this).data('user-has-changed', true);
    });

    // Event listeners for financial inputs
    $('#diskon, #bayar').on('keyup change', function() {
        renderCartAndTotals();
    });

    // Event listeners for cart item quantity change and removal
    $(document).on('change', '.quantity-input', function() {
        let index = $(this).data('index');
        let newQuantity = parseInt($(this).val());
        if (newQuantity > 0) { 
            cart[index].quantity = newQuantity; 
        } else {
            // Jika quantity 0 atau negatif, hapus item
            cart.splice(index, 1);
        }
        renderCartAndTotals();
    });

    $(document).on('click', '.remove-item', function() {
        let index = $(this).data('index');
        cart.splice(index, 1);
        $('#bayar').data('user-has-changed', false); // Reset
        renderCartAndTotals();
    });

    // Event listener untuk perubahan pasien
    $(document).on('change', '#pasien_id', function() {
        // Reset cart dan recalculate ketika pasien berubah
        cart = [];
        $('#bayar').data('user-has-changed', false);
        renderCartAndTotals();
    });

    // Event listener untuk perubahan diskon atau bayar
    $('#diskon, #bayar').on('keyup change', function() {
        // Hanya update display, tidak recalculate pricing
        let currentTotal = parseFloat($('#total-input').val()) || 0;
        updateTotalDisplay(currentTotal);
    });

    renderCartAndTotals(); // Initial render

    // New logic for form submission
    $('#form-penjualan').on('submit', function(e) {
        e.preventDefault(); // Stop normal form submission

        // Validasi form
        let serviceType = $('#detail-jenis_layanan').text().toLowerCase();
        let isBPJS = serviceType.includes('bpjs');
        
        if (isBPJS) {
            let frameItems = cart.filter(item => item.type === 'frame');
            let lensaItems = cart.filter(item => item.type === 'lensa');
            
            if (frameItems.length === 0 || lensaItems.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Pasien BPJS harus memilih 1 frame dan minimal 1 lensa.',
                });
                return;
            }
        }
        
        if (cart.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Keranjang tidak boleh kosong.',
            });
            return;
        }

        let form = $(this);
        let url = form.attr('action');
        // Gunakan FormData untuk mengirim file
        let data = new FormData(this);

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            processData: false, // Penting untuk FormData
            contentType: false, // Penting untuk FormData
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect_url;
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorMessage,
                });
            }
        });
    });

    // Logika Webcam
    let video = document.getElementById('webcam-video');
    let canvas = document.getElementById('webcam-canvas');
    let photoPreview = document.getElementById('photo-preview');
    let snapButton = document.getElementById('btn-snap-photo');
    let useButton = document.getElementById('btn-use-photo');
    let closeButton = document.getElementById('btn-close-webcam');
    let stream;

    $('#btn-open-webcam').on('click', function() {
        // Reset tampilan
        video.style.display = 'block';
        photoPreview.style.display = 'none';
        snapButton.style.display = 'inline-block';
        useButton.style.display = 'none';

        // Akses kamera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(s) {
                stream = s;
                video.srcObject = stream;
            })
            .catch(function(err) {
                console.error("Error accessing webcam: ", err);
                alert('Tidak dapat mengakses webcam. Pastikan Anda memberikan izin.');
                $('#modal-webcam').modal('hide');
            });
    });

    snapButton.addEventListener('click', function() {
        // Atur ukuran canvas sesuai video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        // Gambar frame saat ini dari video ke canvas
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Tampilkan hasil foto, sembunyikan video
        photoPreview.src = canvas.toDataURL('image/png');
        video.style.display = 'none';
        photoPreview.style.display = 'block';

        // Ubah tombol
        snapButton.style.display = 'none';
        useButton.style.display = 'inline-block';
    });

    useButton.addEventListener('click', function() {
        // Konversi data canvas ke Blob (seperti file)
        canvas.toBlob(function(blob) {
            // Buat file baru dari blob
            let file = new File([blob], "webcam_capture.png", { type: "image/png" });

            // Gunakan DataTransfer untuk memasukkan file ke input
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('photo_bpjs').files = dataTransfer.files;

            // Tutup modal dan hentikan stream
            closeWebcamStream();
            $('#modal-webcam').modal('hide');
        }, 'image/png');
    });

    closeButton.addEventListener('click', function() {
        closeWebcamStream();
    });

    $('#modal-webcam').on('hidden.bs.modal', function () {
        closeWebcamStream();
    });

    function closeWebcamStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    // Tombol input manual pasien dari input group
    $('#btn-input-manual-pasien').on('click', function() {
        $('#modal-input-manual-pasien').modal('show');
    });
    // Simpan manual pasien
    $('#btn-simpan-manual-pasien').on('click', function() {
        var nama = $('#manual-pasien-name').val();
        if (nama.trim() === '') {
            alert('Nama pasien harus diisi!');
            return;
        }
        $('#pasien_id').val('');
        $('#pasien_name').val(nama);
        $('#modal-input-manual-pasien').modal('hide');
        $('#pasien-details-container').slideUp();
        
        $('#bpjs-summary').hide();
        
        // Reset dokter untuk input manual
        $('#dokter_id').val('');
        $('#dokter_manual').val('');
        
        // Reset BPJS level untuk input manual
        currentBPJSLevel = null;
        
        // Reset BPJS pricing for manual input
        renderCartAndTotals();
    });

    // Event listener untuk dropdown dokter - kosongkan input manual jika dipilih
    $('#dokter_id').on('change', function() {
        if ($(this).val()) {
            $('#dokter_manual').val('');
        }
    });

    // Event listener untuk input dokter manual - kosongkan dropdown jika diisi
    $('#dokter_manual').on('input', function() {
        if ($(this).val().trim()) {
            $('#dokter_id').val('');
        }
    });
    
    // Setup real-time stock updates untuk halaman penjualan
    if (typeof window.RealtimeManager !== 'undefined') {
        window.RealtimeManager.connectStockUpdates({
            onData: function(data) {
                updateProductStockInModal(data);
                showStockUpdateNotification(data);
            }
        });
    }
    
    function updateProductStockInModal(data) {
        if (!data.updates) return;
        
        data.updates.forEach(update => {
            // Update stock in frame modal
            if (update.type === 'frame_stock_update') {
                const stockCell = document.querySelector(`#modal-frame tr[data-id="${update.product_id}"] .stock-display`);
                if (stockCell) {
                    stockCell.textContent = update.new_stock;
                    
                    // Add visual feedback
                    const row = stockCell.closest('tr');
                    if (row) {
                        row.classList.add('stock-updated');
                        setTimeout(() => row.classList.remove('stock-updated'), 2000);
                        
                        // Add stock level classes
                        row.classList.remove('stock-low', 'stock-medium', 'stock-normal');
                        row.classList.add(`stock-${update.alert_level}`);
                    }
                }
            }
            
            // Update stock in lensa modal
            if (update.type === 'lensa_stock_update') {
                const stockCell = document.querySelector(`#modal-lensa tr[data-id="${update.product_id}"] .stock-display`);
                if (stockCell) {
                    stockCell.textContent = update.new_stock;
                    
                    const row = stockCell.closest('tr');
                    if (row) {
                        row.classList.add('stock-updated');
                        setTimeout(() => row.classList.remove('stock-updated'), 2000);
                        row.classList.remove('stock-low', 'stock-medium', 'stock-normal');
                        row.classList.add(`stock-${update.alert_level}`);
                    }
                }
            }
            
            // Update stock in aksesoris modal
            if (update.type === 'aksesoris_stock_update') {
                const stockCell = document.querySelector(`#modal-aksesoris tr[data-id="${update.product_id}"] .stock-display`);
                if (stockCell) {
                    stockCell.textContent = update.new_stock;
                    
                    const row = stockCell.closest('tr');
                    if (row) {
                        row.classList.add('stock-updated');
                        setTimeout(() => row.classList.remove('stock-updated'), 2000);
                        row.classList.remove('stock-low', 'stock-medium', 'stock-normal');
                        row.classList.add(`stock-${update.alert_level}`);
                    }
                }
            }
        });
    }
    
    function showStockUpdateNotification(data) {
        if (data.total_updates > 0 && typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            let message = `Stok ${data.total_updates} produk diupdate`;
            if (data.low_stock_alerts > 0) {
                message += ` (${data.low_stock_alerts} stok rendah!)`;
            }
            
            Toast.fire({
                icon: data.low_stock_alerts > 0 ? 'warning' : 'info',
                title: 'Update Stok Real-time',
                text: message
            });
        }
    }
    
    // Signature event handlers
    if (signatureCanvas) {
        // Mouse events
        signatureCanvas.addEventListener('mousedown', startDrawing);
        signatureCanvas.addEventListener('mousemove', draw);
        signatureCanvas.addEventListener('mouseup', stopDrawing);
        signatureCanvas.addEventListener('mouseout', stopDrawing);
        
        // Touch events for mobile
        signatureCanvas.addEventListener('touchstart', startDrawing);
        signatureCanvas.addEventListener('touchmove', draw);
        signatureCanvas.addEventListener('touchend', stopDrawing);
    }
    
    // Signature functions
    function startDrawing(e) {
        isDrawing = true;
        draw(e);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        e.preventDefault();
        let rect = signatureCanvas.getBoundingClientRect();
        let x, y;
        
        if (e.type.includes('touch')) {
            x = e.touches[0].clientX - rect.left;
            y = e.touches[0].clientY - rect.top;
        } else {
            x = e.clientX - rect.left;
            y = e.clientY - rect.top;
        }
        
        signatureCtx.lineTo(x, y);
        signatureCtx.stroke();
        signatureCtx.beginPath();
        signatureCtx.moveTo(x, y);
    }
    
    function stopDrawing() {
        isDrawing = false;
        signatureCtx.beginPath();
    }
    
    // Clear signature button
    $(document).on('click', '#btn-clear-signature', function() {
        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        $('#signature_bpjs').val('');
    });
    
    // Save signature button
    $(document).on('click', '#btn-save-signature', function() {
        let signatureData = signatureCanvas.toDataURL('image/png');
        $('#signature_bpjs').val(signatureData);
        
        Swal.fire({
            icon: 'success',
            title: 'Tanda Tangan Tersimpan!',
            text: 'Tanda tangan telah disimpan dan siap dikirim.',
            timer: 2000,
            showConfirmButton: false
        });
    });
    
    // Load signature button
    $(document).on('click', '#btn-load-signature', function() {
        // Create file input for signature upload
        let fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.onchange = function(e) {
            let file = e.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let img = new Image();
                    img.onload = function() {
                        // Clear canvas and draw uploaded image
                        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                        signatureCtx.drawImage(img, 0, 0, signatureCanvas.width, signatureCanvas.height);
                        
                        // Convert to base64 and save
                        let signatureData = signatureCanvas.toDataURL('image/png');
                        $('#signature_bpjs').val(signatureData);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Tanda Tangan Diupload!',
                            text: 'Tanda tangan telah berhasil diupload dan disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
        fileInput.click();
    });
});
</script>

<style>
/* Stock update styles for penjualan modals */
.stock-updated {
    background-color: rgba(255, 255, 0, 0.3) !important;
    transition: background-color 2s ease-out;
}

.stock-low {
    background-color: rgba(255, 0, 0, 0.1) !important;
}

.stock-medium {
    background-color: rgba(255, 165, 0, 0.1) !important;
}

.stock-normal {
    background-color: rgba(0, 255, 0, 0.1) !important;
}

.realtime-stock-indicator {
    position: relative;
}

.realtime-stock-indicator:after {
    content: "●";
    color: #00ff00;
    animation: blink 2s infinite;
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 10px;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
}

/* Patient details alignment styles */
#pasien-details-container .col-md-6 {
    display: flex;
    flex-direction: column;
}

#pasien-details-container .box {
    flex: 1;
    display: flex;
    flex-direction: column;
}

#pasien-details-container .box-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

#pasien-details-container .box-header {
    flex-shrink: 0;
}

#pasien-details-container .table-responsive {
    flex: 1;
}

/* Ensure equal heights for both columns */
#pasien-details-container .row {
    align-items: stretch;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #pasien-details-container .col-md-6 {
        margin-bottom: 15px;
    }
}
</style>
@endpush 