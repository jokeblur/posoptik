@extends('layouts.master')

@section('title', 'Laporan POS')

@section('content')
<!-- Filter Cabang untuk Super Admin -->
@if($isSuperAdmin)
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Filter Laporan</h3>
            </div>
            <div class="box-body">
                <form method="GET" action="{{ route('laporan.pos') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Pilih Cabang:</label>
                                <select name="branch_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">Semua Cabang</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Bulan:</label>
                                <select name="bulan" class="form-control" onchange="this.form.submit()">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}" {{ $bulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tahun:</label>
                                <select name="tahun" class="form-control" onchange="this.form.submit()">
                                    @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('laporan.pos') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Per Cabang (hanya tampil jika tidak ada cabang yang dipilih) -->
@if(!$selectedBranchId && !empty($summaryCabang))
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Summary Per Cabang ({{ date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) }})</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Cabang</th>
                                <th>Omset Harian</th>
                                <th>Omset Bulanan</th>
                                <th>Piutang</th>
                                <th>Transaksi Harian</th>
                                <th>Transaksi Bulanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summaryCabang as $branchId => $summary)
                            <tr>
                                <td><strong>{{ $summary['name'] }}</strong></td>
                                <td>Rp {{ number_format($summary['omset_harian'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($summary['omset_bulanan'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($summary['piutang'], 0, ',', '.') }}</td>
                                <td>{{ $summary['transaksi_harian'] }} transaksi</td>
                                <td>{{ $summary['transaksi_bulanan'] }} transaksi</td>
                                <td>
                                    <a href="{{ route('laporan.pos', ['branch_id' => $branchId, 'bulan' => $bulan, 'tahun' => $tahun]) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<!-- Header Cabang Terpilih -->
@if($selectedBranch)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h4><i class="fa fa-building"></i> Laporan Cabang: {{ $selectedBranch->name }}</h4>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Ringkasan Omset
                    @if($selectedBranch) - {{ $selectedBranch->name }} @endif
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="alert alert-info">
                            <b>Omset Hari Ini:</b><br>
                            <span style="font-size: 1.5em;">Rp {{ number_format($omsetHarian,0,',','.') }}</span>
                            <br>
                            <button class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal-harian">
                                <i class="fa fa-list"></i> Detail Transaksi Harian
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-success">
                            <b>Omset Bulan {{ $bulan }}/{{ $tahun }}:</b><br>
                            <span style="font-size: 1.5em;">Rp {{ number_format($omsetBulanan,0,',','.') }}</span>
                            <br>
                            <button class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal-bulanan">
                                <i class="fa fa-list"></i> Detail Transaksi Bulanan
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-danger">
                            <b>Total Piutang (Belum Lunas):</b><br>
                            <span style="font-size: 1.5em;">Rp {{ number_format($totalPiutang,0,',','.') }}</span>
                            <br>
                            <button class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal-piutang">
                                <i class="fa fa-list"></i> Lihat Daftar Piutang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Omset Per Layanan (Bulan {{ $bulan }}/{{ $tahun }})</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Omset</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($omsetLayanan as $layanan => $omset)
                        <tr>
                            <td>{{ $layanan }}</td>
                            <td>Rp {{ number_format($omset,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Rekap DP (Belum Lunas)</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Nama Pasien</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Total</th>
                            <th>Bayar (DP)</th>
                            <th>Kekurangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapDP as $trx)
                        <tr>
                            <td>{{ $trx->kode_penjualan }}</td>
                            <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                            <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-primary">{{ $trx->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>Rp {{ number_format($trx->total,0,',','.') }}</td>
                            <td>Rp {{ number_format($trx->bayar,0,',','.') }}</td>
                            <td>Rp {{ number_format($trx->kekurangan,0,',','.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '7' : '6' }}" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Rekap Lunas</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Nama Pasien</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Total</th>
                            <th>Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapLunas as $trx)
                        <tr>
                            <td>{{ $trx->kode_penjualan }}</td>
                            <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                            <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-success">{{ $trx->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>Rp {{ number_format($trx->total,0,',','.') }}</td>
                            <td>Rp {{ number_format($trx->bayar,0,',','.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '6' : '5' }}" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Stok Menipis (Stok &lt; {{ $batasStok }})</h3>
            </div>
            <div class="box-body table-responsive">
                <h4>Lensa</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Merk</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stokLensa as $item)
                        <tr class="bg-danger">
                            <td>{{ $item->kode_lensa }}</td>
                            <td>{{ $item->merk_lensa }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-warning">{{ $item->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>{{ $item->stok }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '4' : '3' }}" class="text-center">Aman</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <h4>Frame</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Merk</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stokFrame as $item)
                        <tr class="bg-danger">
                            <td>{{ $item->kode_frame }}</td>
                            <td>{{ $item->merk_frame }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-warning">{{ $item->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>{{ $item->stok }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '4' : '3' }}" class="text-center">Aman</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <h4>Aksesoris</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stokAksesoris as $item)
                        <tr class="bg-danger">
                            <td>{{ $item->nama_produk }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-warning">{{ $item->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>{{ $item->stok }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '3' : '2' }}" class="text-center">Aman</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Daftar Piutang -->
<div class="modal fade" id="modal-piutang" tabindex="-1" role="dialog" aria-labelledby="modalPiutangLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalPiutangLabel">Daftar Piutang (Transaksi Belum Lunas)</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped datatable" id="table-piutang">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Nama Pasien</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Total</th>
                            <th>Bayar (DP)</th>
                            <th>Kekurangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($piutangList as $trx)
                        <tr>
                            <td>{{ $trx->kode_penjualan }}</td>
                            <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                            <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-primary">{{ $trx->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>Rp {{ number_format($trx->total,0,',','.') }}</td>
                            <td>Rp {{ number_format($trx->bayar,0,',','.') }}</td>
                            <td>Rp {{ number_format($trx->kekurangan,0,',','.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '7' : '6' }}" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Detail Transaksi Harian -->
<div class="modal fade" id="modal-harian" tabindex="-1" role="dialog" aria-labelledby="modalHarianLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalHarianLabel">Detail Transaksi Omset Harian</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped datatable" id="table-harian">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Nama Pasien</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailHarian as $trx)
                        <tr>
                            <td>{{ $trx->kode_penjualan }}</td>
                            <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                            <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-info">{{ $trx->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>Rp {{ number_format($trx->total,0,',','.') }}</td>
                            <td>Rp {{ number_format($trx->bayar,0,',','.') }}</td>
                            <td>{{ $trx->status }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '7' : '6' }}" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Detail Transaksi Bulanan -->
<div class="modal fade" id="modal-bulanan" tabindex="-1" role="dialog" aria-labelledby="modalBulananLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalBulananLabel">Detail Transaksi Omset Bulanan</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped datatable" id="table-bulanan">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Nama Pasien</th>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <th>Cabang</th>
                            @endif
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailBulanan as $trx)
                        <tr>
                            <td>{{ $trx->kode_penjualan }}</td>
                            <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                            <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                            @if($isSuperAdmin && !$selectedBranchId)
                            <td><span class="label label-info">{{ $trx->branch->name ?? '-' }}</span></td>
                            @endif
                            <td>Rp {{ number_format($trx->total,0,',','.') }}</td>
                            <td>Rp {{ number_format($trx->bayar,0,',','.') }}</td>
                            <td>{{ $trx->status }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="{{ $isSuperAdmin && !$selectedBranchId ? '7' : '6' }}" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
$(function() {
    $('.datatable').DataTable({
        responsive: true,
        pageLength: 10,
        order: []
    });
});
</script>
@endpush
@endsection 