@extends('layouts.master')

@section('title', 'Media Promosi Display')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-upload"></i> Upload Media</h3></div>
            <form method="POST" action="{{ route('display.media.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="box-body">
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                    <div class="form-group"><label>Judul media</label><input class="form-control" name="judul" required maxlength="120" placeholder="Promo frame terbaru"></div>
                    <div class="form-group"><label>Urutan tampil</label><input class="form-control" type="number" name="urutan" value="0" min="0" max="999"></div>
                    <div class="form-group"><label>Gambar / video</label><input class="form-control" type="file" name="media" required accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/ogg"><p class="help-block">Gambar atau video, maksimal 50 MB.</p></div>
                </div>
                <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-cloud-upload"></i> Upload Media</button></div>
            </form>
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