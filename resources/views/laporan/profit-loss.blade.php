@extends('layouts.master')

@section('title', 'Laporan Laba Rugi')

@section('breadcrumb')
    @parent
    <li class="active">Laba Rugi</li>
@endsection

@section('content')

{{-- Filter --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter"></i> Filter Periode</h3>
            </div>
            <div class="box-body">
                <form method="GET" action="{{ route('laporan.profit-loss') }}" class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                @php
                                    $namaBulanArr = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                @endphp
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int)$bulan === $m ? 'selected' : '' }}>{{ $namaBulanArr[$m] }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cabang</label>
                            <select name="branch_id" class="form-control">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2" style="padding-top:25px;">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Tampilkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
@php $namaBulanLabel = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pendapatan Penjualan</span>
                <span class="info-box-number">Rp {{ number_format($pendapatan, 0, ',', '.') }}</span>
                <span class="progress-description">{{ $jumlahTransaksi }} transaksi</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">HPP (Harga Pokok)</span>
                <span class="info-box-number">Rp {{ number_format($totalHpp, 0, ',', '.') }}</span>
                <span class="progress-description">Laba Kotor: Rp {{ number_format($labaKotor, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Beban</span>
                <span class="info-box-number">Rp {{ number_format($totalBeban, 0, ',', '.') }}</span>
                <span class="progress-description">Gaji + Operasional</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box {{ $labaBersih >= 0 ? 'bg-aqua' : 'bg-red' }}">
            <span class="info-box-icon"><i class="fa fa-balance-scale"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ $labaBersih >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}</span>
                <span class="info-box-number">Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}</span>
                <span class="progress-description">{{ $namaBulanLabel[(int)$bulan] }} {{ $tahun }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Laporan Formal + Detail --}}
<div class="row">
    <div class="col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-line-chart"></i> Laporan Laba / Rugi — {{ $namaBulanLabel[(int)$bulan] }} {{ $tahun }}</h3>
            </div>
            <div class="box-body">
                <table class="table table-condensed table-bordered">
                    <thead><tr class="bg-gray-light"><th colspan="2" style="font-size:13px;text-transform:uppercase;letter-spacing:.5px;">A. PENDAPATAN</th></tr></thead>
                    <tbody>
                        <tr><td>Omset Penjualan ({{ $jumlahTransaksi }} transaksi)</td><td class="text-right">Rp {{ number_format($pendapatan, 0, ',', '.') }}</td></tr>
                        <tr><td>Pemasukan Lain-lain (non-penjualan)</td><td class="text-right">Rp {{ number_format($pemasukanLain, 0, ',', '.') }}</td></tr>
                        <tr class="bg-green-light"><th>Total Pendapatan</th><th class="text-right">Rp {{ number_format($pendapatan + $pemasukanLain, 0, ',', '.') }}</th></tr>
                    </tbody>
                    <thead><tr class="bg-gray-light"><th colspan="2" style="font-size:13px;text-transform:uppercase;letter-spacing:.5px;">B. HARGA POKOK PENJUALAN (HPP)</th></tr></thead>
                    <tbody>
                        <tr><td>HPP Frame</td><td class="text-right">Rp {{ number_format($hppFrame, 0, ',', '.') }}</td></tr>
                        <tr><td>HPP Lensa</td><td class="text-right">Rp {{ number_format($hppLensa, 0, ',', '.') }}</td></tr>
                        <tr><td>HPP Aksesoris</td><td class="text-right">Rp {{ number_format($hppAksesoris, 0, ',', '.') }}</td></tr>
                        <tr class="bg-yellow-light"><th>Total HPP</th><th class="text-right">Rp {{ number_format($totalHpp, 0, ',', '.') }}</th></tr>
                    </tbody>
                    <tbody>
                        <tr class="{{ $labaKotor >= 0 ? 'bg-light-blue' : 'bg-red' }}" style="color:#fff;">
                            <th>LABA KOTOR (Pendapatan − HPP)</th><th class="text-right">Rp {{ number_format($labaKotor, 0, ',', '.') }}</th>
                        </tr>
                    </tbody>
                    <thead><tr class="bg-gray-light"><th colspan="2" style="font-size:13px;text-transform:uppercase;letter-spacing:.5px;">C. BEBAN OPERASIONAL</th></tr></thead>
                    <tbody>
                        <tr><td>Beban Gaji Karyawan</td><td class="text-right">Rp {{ number_format($bebanGaji, 0, ',', '.') }}</td></tr>
                        <tr><td>Pengeluaran Operasional</td><td class="text-right">Rp {{ number_format($bebanKeuangan, 0, ',', '.') }}</td></tr>
                        <tr class="bg-red-light"><th>Total Beban</th><th class="text-right">Rp {{ number_format($totalBeban, 0, ',', '.') }}</th></tr>
                    </tbody>
                    <tbody>
                        <tr class="{{ $labaBersih >= 0 ? 'bg-green' : 'bg-red' }}" style="color:#fff;font-size:15px;">
                            <th>{{ $labaBersih >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</th>
                            <th class="text-right">Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> Detail Gaji Karyawan</h3>
            </div>
            <div class="box-body table-responsive">
                @if($detailGaji->count())
                <table class="table table-condensed table-bordered">
                    <thead><tr><th>Nama</th><th>Jabatan</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @foreach($detailGaji as $g)
                        <tr><td>{{ $g->nama }}</td><td><small>{{ $g->jabatan }}</small></td><td class="text-right">Rp {{ number_format($g->total_gaji, 0, ',', '.') }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="bg-red" style="color:#fff;"><th colspan="2">Total Gaji</th><th class="text-right">Rp {{ number_format($bebanGaji, 0, ',', '.') }}</th></tr></tfoot>
                </table>
                @else
                <p class="text-center text-muted"><i class="fa fa-info-circle"></i> Belum ada data gaji.<br><a href="{{ route('karyawan.index') }}">Input gaji karyawan →</a></p>
                @endif
            </div>
        </div>

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Pengeluaran per Kategori</h3>
            </div>
            <div class="box-body table-responsive">
                @if($detailPengeluaran->count())
                <table class="table table-condensed table-bordered">
                    <thead><tr><th>Kategori</th><th class="text-right">Jumlah</th></tr></thead>
                    <tbody>
                        @foreach($detailPengeluaran as $p)
                        <tr><td>{{ $p->kategori }}</td><td class="text-right">Rp {{ number_format($p->total, 0, ',', '.') }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="bg-orange" style="color:#fff;"><th>Total</th><th class="text-right">Rp {{ number_format($bebanKeuangan, 0, ',', '.') }}</th></tr></tfoot>
                </table>
                @else
                <p class="text-center text-muted"><i class="fa fa-info-circle"></i> Belum ada pengeluaran.<br><a href="{{ route('keuangan.index') }}">Catat pengeluaran →</a></p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Info Nilai Stok --}}
<div class="row">
    <div class="col-md-12">
        <div class="box box-default collapsed-box">
            <div class="box-header with-border" data-widget="collapse" style="cursor:pointer;">
                <h3 class="box-title"><i class="fa fa-archive"></i> Nilai Stok Saat Ini (informasi)</h3>
                <div class="box-tools pull-right"><button class="btn btn-box-tool"><i class="fa fa-plus"></i></button></div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr><th>Produk</th><th>Qty Stok</th><th>Nilai Harga Beli</th><th>Nilai Harga Jual</th><th>Potensi Laba Stok</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Frame</td>
                            <td>{{ number_format($frameStats->total_qty, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($frameStats->total_beli, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($frameStats->total_jual, 0, ',', '.') }}</td>
                            <td>@php $fs = $frameStats->total_jual - $frameStats->total_beli; @endphp
                                <span class="label {{ $fs >= 0 ? 'label-success' : 'label-danger' }}">Rp {{ number_format($fs, 0, ',', '.') }}</span></td>
                        </tr>
                        <tr>
                            <td>Lensa</td>
                            <td>{{ number_format($lensaStats->total_qty, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($lensaStats->total_beli, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($lensaStats->total_jual, 0, ',', '.') }}</td>
                            <td>@php $ls = $lensaStats->total_jual - $lensaStats->total_beli; @endphp
                                <span class="label {{ $ls >= 0 ? 'label-success' : 'label-danger' }}">Rp {{ number_format($ls, 0, ',', '.') }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
