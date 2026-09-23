@extends('kasir-mobile.layout')

@section('content')
@if(session('success'))
    <div class="alert alert-success" style="border-radius:10px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="border-radius:10px;">{{ session('error') }}</div>
@endif

<a href="{{ route('stock-transfer.create') }}" class="m-btn m-btn-primary" style="text-decoration:none; margin-bottom:14px;">
    <i class="fa fa-plus"></i> Buat Permintaan Stok Baru
</a>

<h4 style="font-weight:700; font-size:14px; color:#555;">Riwayat Permintaan Cabang Anda</h4>

@forelse($transfers as $t)
    <div class="m-card" style="padding:12px 14px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="min-width:0; flex:1;">
                <div style="font-weight:700; font-size:14px;">{{ $t->kode_transfer }}</div>
                <div style="font-size:12px; color:#888; margin-top:2px;">
                    <i class="fa fa-arrow-right"></i> {{ $t->fromBranch->name ?? '-' }} → {{ $t->toBranch->name ?? '-' }}
                </div>
                <div style="font-size:12px; color:#888;">
                    {{ $t->details->count() }} item · {{ $t->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
            <div style="text-align:right;">
                @switch($t->status)
                    @case('Pending')
                        <span class="label label-warning">Menunggu</span>
                        @break
                    @case('Approved')
                        <span class="label label-success">Disetujui</span>
                        @break
                    @case('Completed')
                        <span class="label label-info">Selesai</span>
                        @break
                    @case('Rejected')
                        <span class="label label-danger">Ditolak</span>
                        @break
                    @default
                        <span class="label label-default">{{ $t->status }}</span>
                @endswitch
                <div style="margin-top:6px;">
                    <a href="{{ route('stock-transfer.show', $t->id) }}" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>
                </div>
            </div>
        </div>
        @if($t->status === 'Rejected' && $t->rejection_reason)
            <div style="font-size:11px; color:#c0392b; margin-top:6px; background:#fdf0ef; padding:6px 10px; border-radius:8px;">
                <i class="fa fa-info-circle"></i> {{ $t->rejection_reason }}
            </div>
        @endif
    </div>
@empty
    <div class="m-card">
        <div class="m-empty"><i class="fa fa-exchange"></i>Belum ada permintaan transfer stok</div>
    </div>
@endforelse
@endsection
