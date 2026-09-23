@extends('layouts.master')

@section('title', 'Kwitansi Kacamata')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Kwitansi Pembayaran Kacamata</h3>
            </div>
            <form method="POST" action="{{ route('kwitansi-kacamata.print') }}" target="_blank">
                @csrf
                <div class="box-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom: 0; padding-left: 20px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="nomor_display">No. Kwitansi</label>
                            <input type="text" class="form-control" id="nomor_display" readonly>
                            <input type="hidden" id="nomor" name="nomor" value="{{ old('nomor') }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="nomor_periode">Periode No.</label>
                            <input type="text" class="form-control" id="nomor_periode" value="{{ old('nomor_periode', date('m/Y')) }}" maxlength="7" placeholder="09/2026">
                            <small class="text-muted">Format MM/YYYY</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tempat_tanggal">Tempat, Tanggal</label>
                            <input type="text" class="form-control" id="tempat_tanggal" name="tempat_tanggal" value="{{ old('tempat_tanggal', 'Teluk Kuantan, ' . date('d-m-Y')) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="penerima_dari">Telah terima dari</label>
                            <input type="text" class="form-control" id="penerima_dari" name="penerima_dari" value="{{ old('penerima_dari') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nama_pembuat">Nama Pembuat Kwitansi</label>
                            <input type="text" class="form-control" id="nama_pembuat" name="nama_pembuat" value="{{ old('nama_pembuat') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="untuk_pembayaran">Untuk Pembayaran Kacamata</label>
                            <input type="text" class="form-control" id="untuk_pembayaran" name="untuk_pembayaran" value="{{ old('untuk_pembayaran', 'Pembayaran kacamata') }}" placeholder="Contoh: Pembayaran kacamata Bapak/Ibu ..." required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Frame / Binkai</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="harga_frame">Harga Satuan</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Rp</span>
                                            <input type="number" class="form-control item-input" id="harga_frame" name="harga_frame" value="{{ old('harga_frame', 0) }}" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label>Subtotal Frame</label>
                                        <input type="text" class="form-control" id="subtotal_frame" value="Rp 0" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Lensa</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="harga_lensa">Harga Satuan</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Rp</span>
                                            <input type="number" class="form-control item-input" id="harga_lensa" name="harga_lensa" value="{{ old('harga_lensa', 0) }}" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label>Subtotal Lensa</label>
                                        <input type="text" class="form-control" id="subtotal_lensa" value="Rp 0" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-md-offset-6">
                            <div class="form-group">
                                <label for="total_display">Total Pembayaran</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="text" class="form-control" id="total_display" value="0" readonly>
                                </div>
                            </div>
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
    const formatRupiah = value => 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    const hargaFrame = document.getElementById('harga_frame');
    const hargaLensa = document.getElementById('harga_lensa');
    const subtotalFrame = document.getElementById('subtotal_frame');
    const subtotalLensa = document.getElementById('subtotal_lensa');
    const totalDisplay = document.getElementById('total_display');
    const nomorPeriode = document.getElementById('nomor_periode');
    const nomor = document.getElementById('nomor');
    const nomorDisplay = document.getElementById('nomor_display');
    const form = document.querySelector('form[action="{{ route('kwitansi-kacamata.print') }}"]');

    function updateTotal() {
        const frameTotal = Number(hargaFrame.value) || 0;
        const lensaTotal = Number(hargaLensa.value) || 0;
        subtotalFrame.value = formatRupiah(frameTotal);
        subtotalLensa.value = formatRupiah(lensaTotal);
        totalDisplay.value = Number(frameTotal + lensaTotal).toLocaleString('id-ID');
    }

    function updateNomor() {
        const period = String(nomorPeriode.value || '').trim();
        if (!/^\d{2}\/\d{4}$/.test(period)) {
            nomor.value = '';
            nomorDisplay.value = '';
            return;
        }
        const storageKey = 'kwitansi_counter_' + period;
        const lastUsed = parseInt(localStorage.getItem(storageKey) || '0', 10);
        const sequence = Number.isNaN(lastUsed) ? 1 : lastUsed + 1;
        nomor.value = String(sequence).padStart(3, '0') + '/KWT/OM/' + period;
        nomorDisplay.value = nomor.value;
        form.dataset.sequence = sequence;
        form.dataset.storageKey = storageKey;
    }

    document.querySelectorAll('.item-input').forEach(input => input.addEventListener('input', updateTotal));
    nomorPeriode.addEventListener('input', updateNomor);
    form.addEventListener('submit', function() {
        if (form.dataset.storageKey && form.dataset.sequence) {
            localStorage.setItem(form.dataset.storageKey, form.dataset.sequence);
        }
    });

    updateTotal();
    updateNomor();
})();
</script>
@endpush
