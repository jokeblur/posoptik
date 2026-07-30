@extends('layouts.master')

@section('title', 'Update Foto BPJS')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Update Foto BPJS (Kasir)</h3>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('penjualan.bpjs-photo-update.index') }}" class="row" style="margin-bottom: 15px;">
                    <div class="col-md-6 col-xs-12">
                        <div class="input-group">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode penjualan / nama pasien">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Cari</button>
                            </span>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pasien</th>
                                <th>Layanan</th>
                                <th>Cabang</th>
                                <th>Status Foto</th>
                                <th>Aksi Update Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualans as $penjualan)
                                <tr>
                                    <td>{{ $penjualan->kode_penjualan }}</td>
                                    <td>{{ $penjualan->pasien->nama_pasien ?? $penjualan->nama_pasien_manual ?? '-' }}</td>
                                    <td>{{ $penjualan->pasien_service_type ?? $penjualan->pasien->service_type ?? '-' }}</td>
                                    <td>{{ $penjualan->branch->name ?? '-' }}</td>
                                    <td>
                                        @if(!empty($penjualan->photo_bpjs))
                                            <span class="label label-success">Sudah Ada</span>
                                            <br>
                                            <a href="{{ route('penjualan.bpjs-photo', $penjualan->id) }}" target="_blank" class="btn btn-xs btn-default" style="margin-top:6px;">Lihat Foto</a>
                                        @else
                                            <span class="label label-warning">Belum Ada</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-primary btn-block"
                                                onclick="openCameraModal({{ $penjualan->id }}, '{{ $penjualan->kode_penjualan }}')">
                                            <i class="fa fa-camera"></i> Ambil Foto
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Data transaksi BPJS tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-right">
                    {{ $penjualans->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<form id="camera-upload-form" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="photo_bpjs_webcam" id="photo_bpjs_webcam">
</form>

<div class="modal fade" id="modal-camera-bpjs" tabindex="-1" role="dialog" aria-labelledby="modal-camera-bpjs-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal-camera-bpjs-label">Ambil Foto BPJS</h4>
            </div>
            <div class="modal-body">
                <p id="camera-transaction-info" class="text-muted" style="margin-bottom:10px;"></p>

                <div id="camera-live-wrapper">
                    <video id="camera-video" autoplay playsinline style="width:100%; max-height: 60vh; background:#000; border-radius: 6px;"></video>
                </div>

                <div id="camera-preview-wrapper" style="display:none; text-align:center;">
                    <img id="camera-preview-image" alt="Preview Foto BPJS" style="max-width:100%; max-height: 60vh; border-radius: 6px; border:1px solid #ddd;">
                </div>

                <canvas id="camera-canvas" style="display:none;"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" id="btn-retake" style="display:none;" onclick="retakePhoto()">
                    <i class="fa fa-refresh"></i> Ulang
                </button>
                <button type="button" class="btn btn-primary" id="btn-capture" onclick="capturePhoto()">
                    <i class="fa fa-camera"></i> Jepret
                </button>
                <button type="button" class="btn btn-success" id="btn-save-photo" style="display:none;" onclick="saveCapturedPhoto()">
                    <i class="fa fa-save"></i> Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let cameraStream = null;
    let currentPenjualanId = null;
    let capturedImageData = '';

    function openCameraModal(penjualanId, kodePenjualan) {
        currentPenjualanId = penjualanId;
        capturedImageData = '';

        $('#camera-transaction-info').text('Kode Transaksi: ' + (kodePenjualan || '-'));
        $('#btn-capture').show();
        $('#btn-retake').hide();
        $('#btn-save-photo').hide();
        $('#camera-live-wrapper').show();
        $('#camera-preview-wrapper').hide();

        $('#modal-camera-bpjs').modal('show');
        startCamera();
    }

    async function startCamera() {
        stopCamera();

        const video = document.getElementById('camera-video');

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' }
                },
                audio: false
            });

            video.srcObject = cameraStream;
        } catch (error) {
            alert('Kamera tidak dapat diakses. Pastikan izin kamera diaktifkan di browser HP Anda.');
            $('#modal-camera-bpjs').modal('hide');
        }
    }

    function stopCamera() {
        if (!cameraStream) {
            return;
        }

        cameraStream.getTracks().forEach(function(track) {
            track.stop();
        });

        cameraStream = null;
    }

    function capturePhoto() {
        const video = document.getElementById('camera-video');
        const canvas = document.getElementById('camera-canvas');
        const previewImage = document.getElementById('camera-preview-image');

        if (!video.videoWidth || !video.videoHeight) {
            alert('Kamera belum siap. Coba tunggu sebentar lalu jepret lagi.');
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        capturedImageData = canvas.toDataURL('image/jpeg', 0.9);
        previewImage.src = capturedImageData;

        $('#camera-live-wrapper').hide();
        $('#camera-preview-wrapper').show();
        $('#btn-capture').hide();
        $('#btn-retake').show();
        $('#btn-save-photo').show();
    }

    function retakePhoto() {
        capturedImageData = '';
        $('#camera-live-wrapper').show();
        $('#camera-preview-wrapper').hide();
        $('#btn-capture').show();
        $('#btn-retake').hide();
        $('#btn-save-photo').hide();
    }

    function saveCapturedPhoto() {
        if (!currentPenjualanId || !capturedImageData) {
            alert('Foto belum diambil.');
            return;
        }

        const uploadForm = document.getElementById('camera-upload-form');
        const photoInput = document.getElementById('photo_bpjs_webcam');

        uploadForm.action = '{{ url('penjualan') }}/' + currentPenjualanId + '/bpjs-photo-update';
        photoInput.value = capturedImageData;
        uploadForm.submit();
    }

    $('#modal-camera-bpjs').on('hidden.bs.modal', function () {
        stopCamera();
        capturedImageData = '';
        currentPenjualanId = null;
    });
</script>
@endpush
