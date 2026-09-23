@extends('layouts.master')

@section('title', 'Kwitansi')

@section('breadcrumb')
    @parent
    <li class="active">Kwitansi</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-file-text-o"></i> Riwayat Kwitansi</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('kwitansi.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Kwitansi Umum
                    </a>
                    <a href="{{ route('kwitansi-kacamata.create') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Kwitansi Kacamata
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
                                <th>Nomor Kwitansi</th>
                                <th>Jenis</th>
                                <th>Penerima</th>
                                <th>Untuk Pembayaran</th>
                                <th>Total</th>
                                <th>Dibuat Oleh</th>
                                <th>Waktu Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kwitansis as $index => $kwitansi)
                                <tr>
                                    <td>{{ $kwitansis->firstItem() + $index }}</td>
                                    <td>{{ $kwitansi->nomor ?: '-' }}</td>
                                    <td>
                                        <span class="label label-{{ $kwitansi->jenis === 'kacamata' ? 'success' : 'primary' }}">
                                            {{ ucfirst($kwitansi->jenis) }}
                                        </span>
                                    </td>
                                    <td>{{ $kwitansi->penerima_dari }}</td>
                                    <td>{{ $kwitansi->untuk_pembayaran ?: '-' }}</td>
                                    <td>Rp {{ number_format((float) $kwitansi->jumlah, 0, ',', '.') }}</td>
                                    <td>{{ optional($kwitansi->creator)->name ?: '-' }}</td>
                                    <td>{{ $kwitansi->created_at ? $kwitansi->created_at->format('d-m-Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data kwitansi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    {{ $kwitansis->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
