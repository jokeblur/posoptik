@extends('layouts.master')

@section('title', 'Buat Kwitansi')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Form Kwitansi</h3>
            </div>
            <form method="POST" action="{{ route('kwitansi.print') }}" target="_blank">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="nomor">No. Kwitansi</label>
                            <input type="text" class="form-control" id="nomor" name="nomor" value="{{ old('nomor') }}" placeholder="Contoh: 001/KWT/OM/VIII/2026">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="tempat_tanggal">Tempat, Tanggal</label>
                            <input type="text" class="form-control" id="tempat_tanggal" name="tempat_tanggal" value="{{ old('tempat_tanggal', 'Teluk Kuantan, ' . date('d-m-Y')) }}" placeholder="Teluk Kuantan, 04-08-2026">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="jumlah_display">Jumlah Rp</label>
                            <div class="input-group">
                                <span class="input-group-addon">Rp</span>
                                <input type="text" class="form-control" id="jumlah_display" placeholder="0">
                            </div>
                            <input type="hidden" id="jumlah" name="jumlah" value="{{ old('jumlah') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="penerima_dari">Telah terima dari</label>
                            <input type="text" class="form-control" id="penerima_dari" name="penerima_dari" value="{{ old('penerima_dari') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="terbilang">Terbilang</label>
                            <input type="text" class="form-control" id="terbilang" name="terbilang" value="{{ old('terbilang') }}" placeholder="Otomatis dari jumlah" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="untuk_pembayaran">Untuk pembayaran</label>
                            <input type="text" class="form-control" id="untuk_pembayaran" name="untuk_pembayaran" value="{{ old('untuk_pembayaran') }}" placeholder="Contoh: Fee kacamata bulan November 2025">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="nama_pembuat">Nama Pembuat Kwitansi</label>
                            <input type="text" class="form-control" id="nama_pembuat" name="nama_pembuat" value="{{ old('nama_pembuat') }}" placeholder="Contoh: Udin Kasep">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-print"></i> Preview / Print Kwitansi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    function toNumber(value) {
        const cleaned = String(value || '').replace(/[^0-9]/g, '');
        return cleaned ? Number(cleaned) : 0;
    }

    function formatIdrNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function capitalizeFirstLetter(value) {
        if (!value) {
            return '';
        }

        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    function terbilang(value) {
        const angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if (value < 12) {
            return angka[value];
        }

        if (value < 20) {
            return terbilang(value - 10) + ' belas';
        }

        if (value < 100) {
            return terbilang(Math.floor(value / 10)) + ' puluh ' + terbilang(value % 10);
        }

        if (value < 200) {
            return 'seratus ' + terbilang(value - 100);
        }

        if (value < 1000) {
            return terbilang(Math.floor(value / 100)) + ' ratus ' + terbilang(value % 100);
        }

        if (value < 2000) {
            return 'seribu ' + terbilang(value - 1000);
        }

        if (value < 1000000) {
            return terbilang(Math.floor(value / 1000)) + ' ribu ' + terbilang(value % 1000);
        }

        if (value < 1000000000) {
            return terbilang(Math.floor(value / 1000000)) + ' juta ' + terbilang(value % 1000000);
        }

        if (value < 1000000000000) {
            return terbilang(Math.floor(value / 1000000000)) + ' miliar ' + terbilang(value % 1000000000);
        }

        return String(value);
    }

    const displayInput = document.getElementById('jumlah_display');
    const hiddenInput = document.getElementById('jumlah');
    const terbilangInput = document.getElementById('terbilang');

    if (!displayInput || !hiddenInput || !terbilangInput) {
        return;
    }

    const initial = toNumber(hiddenInput.value);
    if (initial > 0) {
        displayInput.value = formatIdrNumber(initial);
    }
    terbilangInput.value = initial > 0 ? capitalizeFirstLetter(terbilang(initial).replace(/\s+/g, ' ').trim()) + ' rupiah' : '';

    displayInput.addEventListener('input', function() {
        const parsed = toNumber(this.value);
        hiddenInput.value = parsed > 0 ? parsed : '';
        this.value = parsed > 0 ? formatIdrNumber(parsed) : '';
        terbilangInput.value = parsed > 0 ? capitalizeFirstLetter(terbilang(parsed).replace(/\s+/g, ' ').trim()) + ' rupiah' : '';
    });
})();
</script>
@endpush
