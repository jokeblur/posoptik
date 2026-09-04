@extends('layouts.master')

@section('title', 'Media Promosi Display')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-upload"></i> Upload Media</h3></div>
            <form method="POST" action="{{ route('display.media.store') }}" enctype="multipart/form-data" id="upload-media-form">
                @csrf
                <div class="box-body">
                    <div id="upload-alert-success" class="alert alert-success" style="display:none"></div>
                    <div id="upload-alert-danger" class="alert alert-danger" style="display:none"></div>
                    <div class="form-group"><label>Judul media</label><input class="form-control" name="judul" required maxlength="120" placeholder="Promo frame terbaru"></div>
                    <div class="form-group"><label>Urutan tampil</label><input class="form-control" type="number" name="urutan" value="0" min="0" max="999"></div>
                    <div class="form-group"><label>Gambar / video</label><input class="form-control" type="file" name="media" required accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/ogg"><p class="help-block">Gambar atau video, maksimal 50 MB.</p></div>
                    <div class="form-group" id="upload-progress-wrap" style="display:none">
                        <div class="progress" style="margin-bottom:5px">
                            <div id="upload-progress-bar" class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" style="width:0%">0%</div>
                        </div>
                        <p class="help-block" id="upload-progress-text">Mengupload file...</p>
                    </div>
                </div>
                <div class="box-footer"><button type="submit" class="btn btn-primary" id="upload-media-submit"><i class="fa fa-cloud-upload"></i> Upload Media</button></div>
            </form>
            <script>
            (function () {
                var form = document.getElementById('upload-media-form');
                var submitBtn = document.getElementById('upload-media-submit');
                var progressWrap = document.getElementById('upload-progress-wrap');
                var progressBar = document.getElementById('upload-progress-bar');
                var progressText = document.getElementById('upload-progress-text');
                var alertSuccess = document.getElementById('upload-alert-success');
                var alertDanger = document.getElementById('upload-alert-danger');

                function showAlert(el, message) {
                    alertSuccess.style.display = 'none';
                    alertDanger.style.display = 'none';
                    el.textContent = message;
                    el.style.display = 'block';
                }

                function setProgress(percent) {
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    alertSuccess.style.display = 'none';
                    alertDanger.style.display = 'none';
                    progressWrap.style.display = 'block';
                    setProgress(0);
                    progressText.textContent = 'Mengupload file...';
                    submitBtn.disabled = true;

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.getAttribute('action'), true);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    xhr.upload.addEventListener('progress', function (evt) {
                        if (evt.lengthComputable) {
                            var percent = Math.round((evt.loaded / evt.total) * 100);
                            setProgress(percent);
                        }
                    });

                    xhr.addEventListener('load', function () {
                        submitBtn.disabled = false;
                        var response = {};
                        try { response = JSON.parse(xhr.responseText); } catch (err) { response = {}; }

                        if (xhr.status >= 200 && xhr.status < 300) {
                            setProgress(100);
                            progressText.textContent = 'Upload selesai.';
                            showAlert(alertSuccess, response.message || 'Media promosi berhasil diupload.');
                            form.reset();
                            setTimeout(function () { window.location.reload(); }, 1000);
                            return;
                        }

                        progressWrap.style.display = 'none';

                        if (xhr.status === 422 && response.errors) {
                            var firstError = Object.values(response.errors)[0];
                            showAlert(alertDanger, Array.isArray(firstError) ? firstError[0] : firstError);
                        } else if (xhr.status === 413) {
                            showAlert(alertDanger, 'Upload gagal: ukuran file terlalu besar untuk server (413).');
                        } else if (xhr.status === 0) {
                            showAlert(alertDanger, 'Upload gagal: koneksi terputus. Periksa jaringan Anda dan coba lagi.');
                        } else {
                            showAlert(alertDanger, response.message || 'Upload gagal. Terjadi kesalahan pada server (' + xhr.status + ').');
                        }
                    });

                    xhr.addEventListener('error', function () {
                        submitBtn.disabled = false;
                        progressWrap.style.display = 'none';
                        showAlert(alertDanger, 'Upload gagal: tidak dapat terhubung ke server.');
                    });

                    xhr.addEventListener('timeout', function () {
                        submitBtn.disabled = false;
                        progressWrap.style.display = 'none';
                        showAlert(alertDanger, 'Upload gagal: waktu tunggu habis (timeout).');
                    });

                    xhr.send(new FormData(form));
                });
            })();
            </script>
        </div>
    </div>
    <div class="col-md-7">
        <div class="box box-default">
            <div class="box-header with-border"><h3 class="box-title">Daftar Media Promosi</h3></div>
            <div class="box-body table-responsive">
                <table class="table table-hover"><thead><tr><th>Media</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                @forelse($media as $item)
                    <tr><td><strong>{{ $item->judul }}</strong><br><small>Urutan {{ $item->urutan }}</small></td><td>{{ strtoupper($item->tipe) }}</td><td><span class="label {{ $item->is_active ? 'label-success' : 'label-default' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td><form method="POST" action="{{ route('display.media.toggle', $item) }}" style="display:inline">@csrf @method('PATCH')<button class="btn btn-xs btn-default"><i class="fa fa-power-off"></i></button></form> <form method="POST" action="{{ route('display.media.destroy', $item) }}" style="display:inline" onsubmit="return confirm('Hapus media ini?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button></form></td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Belum ada media yang diupload.</td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection