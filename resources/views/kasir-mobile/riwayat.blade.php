@extends('kasir-mobile.layout')

@section('content')
<div class="m-card" style="padding:10px 12px;">
    <form method="GET" action="{{ route('kasir-mobile.riwayat') }}" style="display:flex; align-items:center; gap:8px;">
        <a href="{{ route('kasir-mobile.riwayat', ['date' => $selectedDate->copy()->subDay()->toDateString()]) }}" class="btn btn-default" aria-label="Hari sebelumnya" title="Hari sebelumnya">
            <i class="fa fa-chevron-left"></i>
        </a>
        <input type="date" name="date" value="{{ $selectedDate->toDateString() }}" max="{{ today()->toDateString() }}" class="m-input" style="min-width:0; flex:1;">
        <button type="submit" class="btn btn-primary" aria-label="Lihat tanggal" title="Lihat tanggal">
            <i class="fa fa-calendar"></i>
        </button>
        @if($selectedDate->lt(today()))
            <a href="{{ route('kasir-mobile.riwayat', ['date' => $selectedDate->copy()->addDay()->toDateString()]) }}" class="btn btn-default" aria-label="Hari berikutnya" title="Hari berikutnya">
                <i class="fa fa-chevron-right"></i>
            </a>
        @else
            <button type="button" class="btn btn-default" aria-label="Hari berikutnya" disabled>
                <i class="fa fa-chevron-right"></i>
            </button>
        @endif
    </form>
</div>

<div class="m-card" style="background:linear-gradient(135deg, var(--brand), var(--brand-dark)); color:#fff;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div style="font-size:12px; opacity:.85;">Penjualan {{ $selectedDate->isToday() ? 'Hari Ini' : $selectedDate->translatedFormat('d F Y') }}</div>
            <div style="font-size:22px; font-weight:700;">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</div>
            <div style="font-size:12px; opacity:.9;">{{ $jumlahTransaksi }} transaksi · {{ $jumlahTransaksiBpjs }} transaksi BPJS</div>
        </div>
        <i class="fa fa-line-chart" style="font-size:36px; opacity:.4;"></i>
    </div>
</div>

@forelse($transaksis as $t)
    <div class="m-card" style="padding:12px 14px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="min-width:0; flex:1;">
                <div style="font-weight:700; font-size:14px;">
                    {{ $t->kode_penjualan }}
                </div>
                <div style="font-size:12px; color:#888;">
                    <i class="fa fa-user"></i> {{ $t->pasien->nama_pasien ?? $t->nama_pasien_manual ?? '-' }}
                    · <i class="fa fa-clock-o"></i> {{ $t->created_at->format('H:i') }}
                </div>
                <div style="font-size:12px; margin-top:2px;">
                    <span class="label label-default">{{ strtoupper($t->metode_pembayaran ?? 'cash') }}</span>
                    <span class="label label-primary">{{ $t->pasien_service_type ?? 'Umum' }}</span>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:700; color:var(--brand); font-size:15px;">Rp {{ number_format($t->total, 0, ',', '.') }}</div>
                <div style="margin-top:6px;">
                    <a href="{{ route('penjualan.show', $t->id) }}" class="btn btn-xs btn-info" title="Detail"><i class="fa fa-eye"></i></a>
                    <a href="{{ route('penjualan.cetak-half', $t->id) }}" target="_blank" class="btn btn-xs btn-success" title="Cetak Nota"><i class="fa fa-print"></i></a>
                    <button type="button" class="btn btn-xs btn-warning" title="Print ke printer PC" onclick="kirimPrintPc({{ $t->id }}, 'half')"><i class="fa fa-desktop"></i> PC</button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="m-card">
        <div class="m-empty"><i class="fa fa-inbox"></i>Belum ada transaksi pada {{ $selectedDate->isToday() ? 'hari ini' : $selectedDate->translatedFormat('d F Y') }}.<br><a href="{{ route('kasir-mobile.index') }}" class="text-brand" style="font-weight:700;">Buat transaksi →</a></div>
    </div>
@endforelse
@endsection

@push('scripts')
@include('print-agent._client')
@endpush
