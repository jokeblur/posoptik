@extends('layouts.master')

@section('title', 'Voucher')

@section('breadcrumb')
    @parent
    <li class="active">Voucher</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-ticket"></i> Data Voucher</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('voucher.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Buat Voucher
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Voucher</th>
                                <th>Nominal</th>
                                <th>Berlaku</th>
                                <th>Syarat dan Ketentuan</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Dibuat Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vouchers as $index => $voucher)
                                @php
                                    $isExpired = $voucher->berlaku_sampai && $voucher->berlaku_sampai->isPast();
                                    $isNotStarted = $voucher->berlaku_mulai && $voucher->berlaku_mulai->isFuture();
                                @endphp
                                <tr>
                                    <td>{{ $vouchers->firstItem() + $index }}</td>
                                    <td><strong>{{ $voucher->kode }}</strong></td>
                                    <td>Rp {{ number_format((float) $voucher->nominal, 0, ',', '.') }}</td>
                                    <td>
                                        {{ $voucher->berlaku_mulai ? $voucher->berlaku_mulai->format('d-m-Y') : '-' }}
                                        s/d
                                        {{ $voucher->berlaku_sampai ? $voucher->berlaku_sampai->format('d-m-Y') : '-' }}
                                    </td>
                                    <td style="white-space: pre-line; max-width: 260px;">{{ $voucher->syarat_ketentuan ?: '-' }}</td>
                                    <td>
                                        @if (!$voucher->aktif)
                                            <span class="label label-default">Nonaktif</span>
                                        @elseif ($isExpired)
                                            <span class="label label-danger">Kadaluarsa</span>
                                        @elseif ($isNotStarted)
                                            <span class="label label-warning">Belum Berlaku</span>
                                        @else
                                            <span class="label label-success">Aktif</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($voucher->creator)->name ?: '-' }}</td>
                                    <td>{{ $voucher->created_at ? $voucher->created_at->format('d-m-Y H:i') : '-' }}</td>
                                    <td style="white-space: nowrap;">
                                        <form action="{{ route('voucher.print', $voucher) }}" method="GET" target="_blank" style="display:inline-flex; align-items:center; gap:3px; margin-right:3px;">
                                            <input type="number" name="copies" value="1" min="1" max="50" class="form-control input-sm" style="width:58px;" title="Jumlah voucher">
                                            <button type="submit" class="btn btn-xs btn-success" title="Cetak voucher"><i class="fa fa-print"></i></button>
                                        </form>
                                        <a href="{{ route('voucher.edit', $voucher) }}" class="btn btn-xs btn-info" title="Edit"><i class="fa fa-pencil"></i></a>
                                        <form action="{{ route('voucher.destroy', $voucher) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus voucher ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center">Belum ada voucher.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center">{{ $vouchers->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
