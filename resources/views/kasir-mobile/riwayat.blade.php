@extends('kasir-mobile.layout')

@section('content')
<div class="m-card" style="background:linear-gradient(135deg, var(--brand), var(--brand-dark)); color:#fff;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div style="font-size:12px; opacity:.85;">Penjualan Anda Hari Ini</div>
            <div style="font-size:22px; font-weight:700;">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</div>
            <div style="font-size:12px; opacity:.85;">{{ $jumlahTransaksi }} transaksi · {{ now()->translatedFormat('d F Y') }}</div>
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
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="m-card">
        <div class="m-empty"><i class="fa fa-inbox"></i>Belum ada transaksi hari ini.<br><a href="{{ route('kasir-mobile.index') }}" class="text-brand" style="font-weight:700;">Buat transaksi pertama →</a></div>
    </div>
@endforelse
@endsection
