@extends('layouts.master')

@section('title', $voucher->exists ? 'Edit Voucher' : 'Buat Voucher')

@section('breadcrumb')
    @parent
    <li><a href="{{ route('voucher.index') }}">Voucher</a></li>
    <li class="active">{{ $voucher->exists ? 'Edit' : 'Buat' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-ticket"></i> {{ $voucher->exists ? 'Edit Voucher' : 'Buat Voucher Baru' }}</h3>
            </div>
            <form method="POST" action="{{ $voucher->exists ? route('voucher.update', $voucher) : route('voucher.store') }}">
                @csrf
                @if ($voucher->exists)
                    @method('PUT')
                @endif
                <div class="box-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom:0; padding-left:20px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="kode">Kode Voucher <span class="text-danger">*</span></label>
                        <input type="text" name="kode" id="kode" class="form-control" value="{{ old('kode', $voucher->kode) }}" maxlength="100" required style="text-transform:uppercase;">
                    </div>
                    <div class="form-group">
                        <label for="nominal">Nominal Voucher <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="number" name="nominal" id="nominal" class="form-control" value="{{ old('nominal', $voucher->nominal) }}" min="0" step="1000" required>
                        </div>
                        <small class="text-muted">Nominal diisi manual, contoh: 50000.</small>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="berlaku_mulai">Berlaku Mulai</label>
                            <input type="date" name="berlaku_mulai" id="berlaku_mulai" class="form-control" value="{{ old('berlaku_mulai', optional($voucher->berlaku_mulai)->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="berlaku_sampai">Berlaku Sampai</label>
                            <input type="date" name="berlaku_sampai" id="berlaku_sampai" class="form-control" value="{{ old('berlaku_sampai', optional($voucher->berlaku_sampai)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="syarat_ketentuan">Syarat dan Ketentuan</label>
                        <textarea name="syarat_ketentuan" id="syarat_ketentuan" class="form-control" rows="6" maxlength="5000" placeholder="Contoh:\n- Berlaku untuk pembelian minimal Rp500.000\n- Tidak dapat diuangkan\n- Tidak dapat digabung dengan promo lain">{{ old('syarat_ketentuan', $voucher->syarat_ketentuan) }}</textarea>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="aktif" value="1" {{ old('aktif', $voucher->exists ? $voucher->aktif : true) ? 'checked' : '' }}>
                            Voucher aktif
                        </label>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('voucher.index') }}" class="btn btn-default">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
