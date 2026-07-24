@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid" style="margin-top: 30px;">
    <!-- <h1 class="page-header" style="margin-bottom: 32px;">Dashboard</h1> -->

    {{-- Box summary baru untuk Admin & Super Admin --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-3">
            <div class="small-box bg-aqua rounded-5 shadow">
                <div class="inner">
                    <h3>{{ $jumlahFrame ?? 0 }}</h3>
                    <p>Frame</p>
                </div>
                <div class="icon"><i class="fa fa-glasses"></i></div>
                <a href="#modal-frame-admin" data-toggle="modal" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $jumlahPasien ?? 0 }}</h3>
                    <p>Pasien</p>
                </div>
                <div class="icon"><i class="fa fa-user"></i></div>
                <a href="#modal-pasien-admin" data-toggle="modal" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $jumlahLensa ?? 0 }}</h3>
                    <p>Lensa</p>
                </div>
                <div class="icon"><i class="fa fa-tablets"></i></div>
                <a href="#modal-lensa-admin" data-toggle="modal" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $jumlahTransaksiAktif ?? 0 }}</h3>
                    <p>Transaksi Aktif Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                <a href="#modal-transaksi-aktif-admin" data-toggle="modal" class="small-box-footer">Detail <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    
    {{-- Low Stock Alert Small Boxes untuk Admin & Super Admin --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-4">
            <div class="small-box bg-red" style="cursor:pointer" onclick="showLowStockModal('lensa')">
                <div class="inner">
                    <h3>{{ $lowStockLensa->count() ?? 0 }}</h3>
                    <p>Lensa Stok Menipis</p>
                </div>
                <div class="icon"><i class="fa fa-tablets"></i></div>
                <div class="small-box-footer">
                    Detail <i class="fa fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-orange" style="cursor:pointer" onclick="showLowStockModal('frame')">
                <div class="inner">
                    <h3>{{ $lowStockFrame->count() ?? 0 }}</h3>
                    <p>Frame Stok Menipis</p>
                </div>
                <div class="icon"><i class="fa fa-glasses"></i></div>
                <div class="small-box-footer">
                    Detail <i class="fa fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-yellow" style="cursor:pointer" onclick="showLowStockModal('aksesoris')">
                <div class="inner">
                    <h3>{{ $lowStockAksesoris->count() ?? 0 }}</h3>
                    <p>Aksesoris Stok Menipis</p>
                </div>
                <div class="icon"><i class="fa fa-cube"></i></div>
                <div class="small-box-footer">
                    Detail <i class="fa fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Grafik Penjualan untuk Admin & Super Admin --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-line-chart"></i> Grafik Penjualan 7 Hari Terakhir
                    </h3>
                </div>
                <div class="box-body chart-panel-body chart-panel-body-lg">
                    <canvas id="salesChartAdmin"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-pie-chart"></i> Perbandingan BPJS vs Umum
                    </h3>
                </div>
                <div class="box-body chart-panel-body chart-panel-body-md">
                    <canvas id="bpjsVsUmumChartAdmin"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-6">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-bar-chart"></i> Status Transaksi BPJS
                    </h3>
                </div>
                <div class="box-body">
                    <canvas id="bpjsStatusChartAdmin" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-building"></i> Penjualan per Cabang
                    </h3>
                </div>
                <div class="box-body">
                    <canvas id="branchSalesChartAdmin" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if(auth()->user()->isSuperAdmin())
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-12">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-trophy"></i> Analisa Frame Paling Banyak Terjual (30 Hari Terakhir)
                    </h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="chart-panel-body chart-panel-body-lg">
                                <canvas id="topFrameBrandChartAdmin"></canvas>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Merk Frame</th>
                                            <th>Jenis Frame</th>
                                            <th class="text-right">Qty Terjual</th>
                                            <th class="text-right">Transaksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(($topFrameBrands ?? collect()) as $index => $brand)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $brand->merk_frame }}</td>
                                                <td>{{ $brand->jenis_frame }}</td>
                                                <td class="text-right">{{ number_format($brand->total_qty, 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($brand->total_transaksi, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Belum ada penjualan frame dalam 30 hari terakhir.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
    
    @includeIf('partials.dashboard_modals')
    

    

    
    {{-- Modal Low Stock Detail --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <!-- Modal Lensa Low Stock -->
    <div class="modal fade" id="modal-low-stock-lensa" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-red">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa fa-tablets"></i> Lensa Stok Menipis (Stok < {{ $batasStok ?? 5 }})
                    </h4>
                </div>
                <div class="modal-body">
                    @if($lowStockLensa->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Merk</th>
                                    @if(auth()->user()->isSuperAdmin())
                                    <th>Cabang</th>
                                    @endif
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockLensa as $item)
                                <tr class="bg-danger">
                                    <td>{{ $item->kode_lensa }}</td>
                                    <td>{{ $item->merk_lensa }}</td>
                                    @if(auth()->user()->isSuperAdmin())
                                    <td><span class="label label-warning">{{ $item->branch->name ?? '-' }}</span></td>
                                    @endif
                                    <td><span class="badge bg-red">{{ $item->stok }}</span></td>
                                    <td>
                                        <a href="{{ route('lensa.edit', $item->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center" style="padding: 20px;">
                        <h4><i class="fa fa-check-circle text-success"></i> Stok Lensa Aman</h4>
                        <p>Tidak ada lensa dengan stok di bawah {{ $batasStok ?? 5 }}</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Frame Low Stock -->
    <div class="modal fade" id="modal-low-stock-frame" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa fa-glasses"></i> Frame Stok Menipis (Stok < {{ $batasStok ?? 5 }})
                    </h4>
                </div>
                <div class="modal-body">
                    @if($lowStockFrame->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Merk</th>
                                    @if(auth()->user()->isSuperAdmin())
                                    <th>Cabang</th>
                                    @endif
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockFrame as $item)
                                <tr class="bg-danger">
                                    <td>{{ $item->kode_frame }}</td>
                                    <td>{{ $item->merk_frame }}</td>
                                    @if(auth()->user()->isSuperAdmin())
                                    <td><span class="label label-warning">{{ $item->branch->name ?? '-' }}</span></td>
                                    @endif
                                    <td><span class="badge bg-red">{{ $item->stok }}</span></td>
                                    <td>
                                        <a href="{{ route('frame.edit', $item->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center" style="padding: 20px;">
                        <h4><i class="fa fa-check-circle text-success"></i> Stok Frame Aman</h4>
                        <p>Tidak ada frame dengan stok di bawah {{ $batasStok ?? 5 }}</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aksesoris Low Stock -->
    <div class="modal fade" id="modal-low-stock-aksesoris" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-yellow">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa fa-cube"></i> Aksesoris Stok Menipis (Stok < {{ $batasStok ?? 5 }})
                    </h4>
                </div>
                <div class="modal-body">
                    @if($lowStockAksesoris->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    @if(auth()->user()->isSuperAdmin())
                                    <th>Cabang</th>
                                    @endif
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockAksesoris as $item)
                                <tr class="bg-danger">
                                    <td>AKS-{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $item->nama_produk }}</td>
                                    @if(auth()->user()->isSuperAdmin())
                                    <td><span class="label label-warning">{{ $item->branch->name ?? '-' }}</span></td>
                                    @endif
                                    <td><span class="badge bg-red">{{ $item->stok }}</span></td>
                                    <td>
                                        <a href="{{ route('aksesoris.edit', $item->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center" style="padding: 20px;">
                        <h4><i class="fa fa-check-circle text-success"></i> Stok Aksesoris Aman</h4>
                        <p>Tidak ada aksesoris dengan stok di bawah {{ $batasStok ?? 5 }}</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Grafik Penjualan untuk Super Admin --}}
    @if(false && auth()->user()->isSuperAdmin() && $chartData)
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Grafik Penjualan 7 Hari Terakhir</h3>
                </div>
                <div class="box-body">
                    <canvas id="salesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Perbandingan BPJS vs Umum</h3>
                </div>
                <div class="box-body">
                    <canvas id="bpjsVsUmumChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-6">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Status Transaksi BPJS</h3>
                </div>
                <div class="box-body">
                    <canvas id="bpjsStatusChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        @if($chartData['branch_sales'])
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Penjualan per Cabang</h3>
                </div>
                <div class="box-body">
                    <canvas id="branchSalesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif

    {{-- Box untuk Passet Bantu --}}
    @if(auth()->user()->isPassetBantu())
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-12" style="margin-bottom: 15px;">
            <div class="pull-right">
                <button type="button" class="btn btn-info btn-sm" onclick="location.reload()">
                    <i class="fa fa-refresh"></i> Refresh Data
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning" style="cursor:pointer" onclick="showPassetModalMenunggu()">
                <div class="inner">
                    <h3>{{ $transaksiMenungguPengerjaan ?? 0 }}</h3>
                    <p>Pekerjaan Menunggu Pengerjaan</p>
                </div>
                <div class="icon"><i class="fa fa-clock-o"></i></div>
                <div class="small-box-footer">
                    Lihat Detail <i class="fa fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $transaksiSelesaiBulanIni ?? 0 }}</h3>
                    <p>Pekerjaan Selesai Bulan Ini (Semua Cabang)</p>
                </div>
                <div class="icon"><i class="fa fa-check-circle"></i></div>
                <div class="small-box-footer">
                    Pekerjaan Selesai
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-primary" style="cursor:pointer" onclick="window.location.href='{{ route('barcode.scan') }}'">
                <div class="inner">
                    <h3><i class="fa fa-qrcode"></i></h3>
                    <p>Scan QR Code</p>
                </div>
                <div class="icon"><i class="fa fa-qrcode"></i></div>
                <div class="small-box-footer">
                    Scan & Update Status <i class="fa fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Informasi Tambahan untuk Passet Bantu --}}
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-info-circle"></i> Informasi Pengerjaan
                    </h3>
                </div>
                <div class="box-body">
                    <div class="alert alert-info">
                        <h4><i class="fa fa-lightbulb-o"></i> Tips Pengerjaan:</h4>
                        <ul style="margin-bottom: 0;">
                            <li>Klik pada box "Pekerjaan Menunggu Pengerjaan" untuk melihat daftar pekerjaan</li>
                            <li>Box "Pekerjaan Selesai Bulan Ini" menampilkan pekerjaan yang telah Anda selesaikan bulan ini di semua cabang</li>
                            <li>Gunakan "Scan QR Code" untuk update status pengerjaan dengan cepat</li>
                            <li>Update status pengerjaan sesuai dengan progress yang telah dilakukan</li>
                            <li>Pastikan semua pekerjaan telah selesai dikerjakan sebelum customer mengambil</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Box untuk Kasir --}}
    @if(auth()->user()->isKasir())

    {{-- Quick Action Buttons untuk Kasir --}}
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12">
            <div class="text-center">
                <div class="btn-group-vertical btn-group-lg" role="group" style="display: inline-block;">
                    <a href="{{ route('penjualan.create') }}" class="btn btn-primary" style="padding: 15px 30px; font-size: 16px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); margin: 5px; min-width: 250px;">
                        <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>
                        Transaksi Penjualan Baru
                    </a>
                    <a href="{{ route('penjualan.index') }}" class="btn btn-info" style="padding: 15px 30px; font-size: 16px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); margin: 5px; min-width: 250px;">
                        <i class="fa fa-list" style="margin-right: 8px;"></i>
                        Daftar Transaksi
                    </a>
                    <a href="{{ route('pasien.index') }}" class="btn btn-success" style="padding: 15px 30px; font-size: 16px; font-weight: bold; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); margin: 5px; min-width: 250px;">
                        <i class="fa fa-users" style="margin-right: 8px;"></i>
                        Data Pasien
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <form method="GET" action="{{ url()->current() }}" class="form-inline" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                <div class="form-group">
                    <label for="omset_date" style="display:block;">Lihat Omset Tanggal</label>
                    <input type="date" class="form-control" id="omset_date" name="omset_date" value="{{ $selectedOmsetDate ?? now()->toDateString() }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-filter"></i> Tampilkan
                </button>
                <a href="{{ url()->current() }}" class="btn btn-default">
                    <i class="fa fa-refresh"></i> Hari Ini
                </a>
                @if(isset($isOmsetToday) && !$isOmsetToday)
                <span class="label label-info" style="padding: 8px 10px;">Mode histori: realtime dimatikan</span>
                @endif
            </form>
        </div>
    </div>

    {{-- Box Omset untuk Kasir --}}
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-4">
            <div class="small-box bg-success omset-total" style="cursor:pointer" onclick="$('#modalKasirOmset').modal('show')">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetKasir ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset {{ $omsetPeriodeLabel ?? 'Hari Ini' }}</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); padding: 3px 10px; font-size: 12px;">
                    <span class="jumlah-transaksi-badge">{{ $transaksiKasir ? $transaksiKasir->count() : 0 }}</span> transaksi {{ strtolower($omsetPeriodeLabel ?? 'hari ini') }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info omset-bpjs" style="cursor:pointer" onclick="$('#modalKasirBpjs').modal('show')">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetBpjs ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset BPJS {{ $omsetPeriodeLabel ?? 'Hari Ini' }}</p>
                </div>
                <div class="icon"><i class="fa fa-heartbeat"></i></div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); padding: 3px 10px; font-size: 12px;">
                    {{ (isset($isOmsetToday) && $isOmsetToday) ? 'Real-time update' : 'Data histori' }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning omset-umum" style="cursor:pointer" onclick="$('#modalKasirUmum').modal('show')">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetUmum ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset Umum {{ $omsetPeriodeLabel ?? 'Hari Ini' }}</p>
                </div>
                <div class="icon"><i class="fa fa-users"></i></div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); padding: 3px 10px; font-size: 12px;">
                    {{ (isset($isOmsetToday) && $isOmsetToday) ? 'Auto refresh' : 'Data histori' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modals: Detail Kasir -->
    <div class="modal fade" id="modalKasirOmset" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-money"></i> Detail Omset {{ $omsetPeriodeLabel ?? 'Hari Ini' }}</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableKasirOmset">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Transaksi</th>
                                    <th>Nama Pasien</th>
                                    <th>Jenis Layanan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no=1; @endphp
                                @foreach(($transaksiKasir ?? collect()) as $trx)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $trx->kode_penjualan }}</td>
                                    <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                                    <td>
                                        @php $stype = $trx->pasien->service_type ?? 'UMUM'; @endphp
                                        <span class="label label-{{ in_array($stype, ['BPJS I','BPJS II','BPJS III']) ? 'info' : 'default' }}">{{ $stype }}</span>
                                    </td>
                                    <td>Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($trx->status_pengerjaan == 'Sudah Diambil')
                                            <span class="label label-success">{{ $trx->status_pengerjaan }}</span>
                                        @else
                                            <span class="label label-warning">{{ $trx->status_pengerjaan }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKasirBpjs" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-heartbeat"></i> Detail Omset BPJS {{ $omsetPeriodeLabel ?? 'Hari Ini' }}</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableKasirBpjs">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Transaksi</th>
                                    <th>Nama Pasien</th>
                                    <th>Jenis Layanan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no=1; @endphp
                                @foreach(($transaksiKasir ?? collect())->filter(function($t){ $st=$t->pasien->service_type ?? 'UMUM'; return in_array($st,['BPJS I','BPJS II','BPJS III']); }) as $trx)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $trx->kode_penjualan }}</td>
                                    <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                                    <td><span class="label label-info">{{ $trx->pasien->service_type ?? 'BPJS' }}</span></td>
                                    <td>Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($trx->status_pengerjaan == 'Sudah Diambil')
                                            <span class="label label-success">{{ $trx->status_pengerjaan }}</span>
                                        @else
                                            <span class="label label-warning">{{ $trx->status_pengerjaan }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKasirUmum" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-users"></i> Detail Omset Umum {{ $omsetPeriodeLabel ?? 'Hari Ini' }}</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableKasirUmum">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Transaksi</th>
                                    <th>Nama Pasien</th>
                                    <th>Jenis Layanan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no=1; @endphp
                                @foreach(($transaksiKasir ?? collect())->filter(function($t){ $st=$t->pasien->service_type ?? 'UMUM'; return !in_array($st,['BPJS I','BPJS II','BPJS III']); }) as $trx)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $trx->kode_penjualan }}</td>
                                    <td>{{ $trx->pasien->nama_pasien ?? '-' }}</td>
                                    <td><span class="label label-default">{{ $trx->pasien->service_type ?? 'UMUM' }}</span></td>
                                    <td>Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($trx->status_pengerjaan == 'Sudah Diambil')
                                            <span class="label label-success">{{ $trx->status_pengerjaan }}</span>
                                        @else
                                            <span class="label label-warning">{{ $trx->status_pengerjaan }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Tabel Transaksi Hari Ini untuk Kasir --}}
    @if(isset($transaksiKasir) && $transaksiKasir->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Transaksi {{ $omsetPeriodeLabel ?? 'Hari Ini' }}</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="transaksi-terbaru-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Transaksi</th>
                                    <th>Nama Pasien</th>
                                    <th>Jenis Layanan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaksiKasir as $index => $transaksi)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $transaksi->created_at->format('d/m/Y') }}</td>
                                                       <td>{{ $transaksi->kode_penjualan }}</td>
                   <td>{{ $transaksi->pasien->nama_pasien ?? '-' }}</td>
                                    <td>
                                        @if($transaksi->pasien && $transaksi->pasien->service_type)
                                            <span class="label label-info">{{ $transaksi->pasien->service_type }}</span>
                                        @else
                                            <span class="label label-default">UMUM</span>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($transaksi->status_pengerjaan == 'Sudah Diambil')
                                            <span class="label label-success">{{ $transaksi->status_pengerjaan }}</span>
                                        @else
                                            <span class="label label-warning">{{ $transaksi->status_pengerjaan }}</span>
                                        @endif
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



    {{-- Konten lain dashboard --}}
</div>

<!-- Modal Detail Frame Admin -->
<div class="modal fade" id="modal-frame-admin" tabindex="-1" role="dialog" aria-labelledby="modal-frame-admin-label">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header bg-aqua">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-frame-admin-label"><i class="fa fa-glasses"></i> Detail Frame</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto; padding: 0;">
                <table class="table table-bordered table-striped table-hover" id="table-detail-frame-admin" style="margin-bottom: 0;">
                    <thead style="background-color: #f4f4f4; position: sticky; top: 0;">
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Frame</th>
                            <th>Merk Frame</th>
                            <th>Jenis Frame</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Lensa Admin -->
<div class="modal fade" id="modal-lensa-admin" tabindex="-1" role="dialog" aria-labelledby="modal-lensa-admin-label">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header bg-green">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-lensa-admin-label"><i class="fa fa-tablets"></i> Detail Lensa</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto; padding: 0;">
                <table class="table table-bordered table-striped table-hover" id="table-detail-lensa-admin" style="margin-bottom: 0;">
                    <thead style="background-color: #f4f4f4; position: sticky; top: 0;">
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Lensa</th>
                            <th>Tipe Lensa</th>
                            <th>Brand</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pasien Admin -->
<div class="modal fade" id="modal-pasien-admin" tabindex="-1" role="dialog" aria-labelledby="modal-pasien-admin-label">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header bg-red">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-pasien-admin-label"><i class="fa fa-user"></i> Detail Pasien</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto; padding: 0;">
                <table class="table table-bordered table-striped table-hover" id="table-detail-pasien-admin" style="margin-bottom: 0;">
                    <thead style="background-color: #f4f4f4; position: sticky; top: 0;">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Pasien</th>
                            <th>No HP</th>
                            <th>Service Type</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Aksesoris Admin -->
<div class="modal fade" id="modal-aksesoris-admin" tabindex="-1" role="dialog" aria-labelledby="modal-aksesoris-admin-label">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header bg-purple">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-aksesoris-admin-label"><i class="fa fa-cube"></i> Detail Aksesoris</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto; padding: 0;">
                <table class="table table-bordered table-striped table-hover" id="table-detail-aksesoris-admin" style="margin-bottom: 0;">
                    <thead style="background-color: #f4f4f4; position: sticky; top: 0;">
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Aksesoris</th>
                            <th>Nama Aksesoris</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi Aktif Admin -->
<div class="modal fade" id="modal-transaksi-aktif-admin" tabindex="-1" role="dialog" aria-labelledby="modal-transaksi-aktif-admin-label">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header bg-purple">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-transaksi-aktif-admin-label"><i class="fa fa-shopping-cart"></i> Transaksi Aktif Hari Ini</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto; padding: 0;">
                <table class="table table-bordered table-striped table-hover" id="table-transaksi-aktif-admin" style="margin-bottom: 0;">
                    <thead style="background-color: #f4f4f4; position: sticky; top: 0;">
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Penjualan</th>
                            <th>Nama Pasien</th>
                            <th>Service Type</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Dokter</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
.animated.pulse {
  animation: pulse 1.5s infinite;
}
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.08); }
  100% { transform: scale(1); }
}
/* Ensure passet modal overlays other UI (e.g., sidebar overlay) */
.modal { z-index: 10550 !important; }
.modal-dialog { z-index: 10551 !important; }
.modal-content { z-index: 10552 !important; }
.modal-backdrop { z-index: 10500 !important; }
.animated.shake {
  animation: shake 1.2s infinite;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-8px); }
  40%, 80% { transform: translateX(8px); }
}

/* Real-time animations */
.pulse-animation {
  animation: pulse-glow 1s ease-in-out;
}
@keyframes pulse-glow {
  0% { box-shadow: 0 0 5px rgba(0,255,0,0.3); }
  50% { box-shadow: 0 0 20px rgba(0,255,0,0.6); }
  100% { box-shadow: 0 0 5px rgba(0,255,0,0.3); }
}

.realtime-indicator {
  position: relative;
}
.realtime-indicator:after {
  content: "●";
  color: #00ff00;
  animation: blink 2s infinite;
  position: absolute;
  top: 5px;
  right: 5px;
  font-size: 12px;
}
@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0.3; }
}



/* Stock update animations */
.stock-updated {
  background-color: rgba(255, 255, 0, 0.3) !important;
  transition: background-color 2s ease-out;
}

.stock-low {
  background-color: rgba(255, 0, 0, 0.1) !important;
}

.stock-medium {
  background-color: rgba(255, 165, 0, 0.1) !important;
}

.stock-normal {
  background-color: rgba(0, 255, 0, 0.1) !important;
}

/* Fix modal backdrop issues */
.modal-backdrop {
  z-index: 1040 !important;
}

.modal {
  z-index: 1050 !important;
}

.modal-backdrop.fade {
  opacity: 0.5 !important;
}

.modal-backdrop.in {
  opacity: 0.5 !important;
}

/* Ensure backdrop is removed properly */
body.modal-open {
    overflow: hidden !important;
}

body:not(.modal-open) {
    overflow: auto;
}

/* Additional modal styling */
.modal.fade.show {
  z-index: 10550 !important;
}

.modal-open .navbar {
  z-index: 10540 !important;
}

.modal-open .sidebar {
  z-index: 10540 !important;
}

.chart-panel-body {
    position: relative;
    width: 100%;
    overflow: visible;
}

.chart-panel-body-lg {
    min-height: 360px;
}

.chart-panel-body-md {
    min-height: 360px;
}

#salesChartAdmin,
#bpjsVsUmumChartAdmin {
    display: block;
    width: 100% !important;
}

@media (max-width: 768px) {
    .chart-panel-body-lg,
    .chart-panel-body-md {
        min-height: 320px;
    }
}

</style>

@push('scripts')
<script>
// Fallback modal function
if (typeof $.fn.modal === 'undefined') {
    console.log('Bootstrap modal not found, using fallback');
    $.fn.modal = function(action) {
        if (action === 'show') {
            this.show();
        } else if (action === 'hide') {
            this.hide();
        }
    };
}
</script>
<script src="{{ asset('js/realtime.js') }}"></script>
<script>
window.APP_BASE_URL = '{{ url('/') }}';

// Auto-refresh untuk dashboard passet bantu
@if(auth()->user()->isPassetBantu())
function refreshPassetBantuDashboard() {
    // Refresh halaman setiap 30 detik untuk update data
    setTimeout(function() {
        location.reload();
    }, 30000);
}

// Mulai auto-refresh
$(document).ready(function() {
    refreshPassetBantuDashboard();
    
    // Tambahkan indikator real-time
    $('.small-box').each(function() {
        $(this).append('<div class="real-time-indicator" style="position: absolute; top: 10px; right: 10px; font-size: 12px; color: #00ff00;">●</div>');
    });
});
@endif

@if(auth()->user()->isKasir())
var KASIR_BRANCH_ID = {{ auth()->user()->branch_id ?? 0 }};
var KASIR_OMSET_IS_TODAY = @json($isOmsetToday ?? true);

// Setup real-time connections with custom callbacks
$(function() {
    // Real-time connections disabled - causing UI loading disruption
    // Uncomment below if needed, but be aware of performance impact
    /*
    // Setup real-time dashboard connection
    window.RealtimeManager.connectDashboard({
        onOpen: function() {
            console.log('Dashboard real-time connection established');
        },
        onError: function() {
            // Connection error - silently handle without showing message to user
            console.log('Dashboard real-time connection error');
        },
        onHeartbeat: function(data) {
            console.log('Dashboard heartbeat received:', data.timestamp);
        }
    });
    
    if (KASIR_OMSET_IS_TODAY) {
        // Setup real-time omset connection (khusus mode hari ini)
        window.RealtimeManager.connectOmsetKasir({
            onOpen: function() {
                console.log('Omset real-time connection established');
            },
            onData: function(data) {
                // Update omset displays
                $('.omset-total h3').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.omset_kasir || 0));
                $('.omset-bpjs h3').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.omset_bpjs || 0));
                $('.omset-umum h3').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.omset_umum || 0));
                $('.jumlah-transaksi-badge').text(data.jumlah_transaksi || 0);
                
                // Update table if data available
                if (data.transaksi_terbaru) {
                    updateTransaksiTable(data.transaksi_terbaru);
                }
                
                // Add visual feedback
                $('.omset-total, .omset-bpjs, .omset-umum').addClass('pulse-animation');
                setTimeout(function() {
                    $('.omset-total, .omset-bpjs, .omset-umum').removeClass('pulse-animation');
                }, 1000);
            },
            onError: function() {
                console.log('Omset real-time connection error');
            },
            onHeartbeat: function(data) {
                console.log('Omset heartbeat received:', data.timestamp);
            }
        });
    }
    
    // Setup notifications
    window.RealtimeManager.connectNotifications({
        onData: function(notification) {
            showRealtimeNotification(notification);
        }
    });
    */
});

function updateTransaksiTable(transaksiList) {
    const tableBody = $('#transaksi-terbaru-table tbody');
    if (!tableBody.length || !transaksiList) return;
    
    tableBody.empty();
    
    transaksiList.forEach(function(transaksi, index) {
        const row = $('<tr>').html(`
            <td>${index + 1}</td>
            <td>${transaksi.tanggal}</td>
            <td>${transaksi.no_transaksi || '-'}</td>
            <td>${transaksi.nama_pasien || '-'}</td>
            <td><span class="label label-info">${transaksi.service_type || 'UMUM'}</span></td>
            <td>Rp ${new Intl.NumberFormat('id-ID').format(transaksi.total || 0)}</td>
            <td><span class="label ${transaksi.status === 'Sudah Diambil' ? 'label-success' : 'label-warning'}">${transaksi.status || 'Sedang Dikerjakan'}</span></td>
        `);
        tableBody.append(row);
    });
}

function showRealtimeNotification(notification) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    
    let icon = 'info';
    if (notification.type === 'new_transaction') icon = 'success';
    if (notification.type === 'ready_for_pickup') icon = 'warning';
    
    Toast.fire({
        icon: icon,
        title: notification.title,
        text: notification.message
    });
}

@endif

@if((auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) && isset($chartData))
// Data untuk grafik
var chartData = @json($chartData);
function getCanvasByIds(ids) {
    for (var i = 0; i < ids.length; i++) {
        var canvas = document.getElementById(ids[i]);
        if (canvas) {
            return canvas;
        }
    }
    return null;
}

function prepareCanvasSize(canvas, targetHeight) {
    if (!canvas) return;

    var parent = canvas.parentElement;
    var parentWidth = parent ? parent.clientWidth : canvas.clientWidth;
    var width = Math.max(280, (parentWidth || 0) - 12);
    var height = Math.max(260, targetHeight || 320);

    canvas.width = width;
    canvas.height = height;
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';
}

function createSalesChartV1() {
    var canvas = getCanvasByIds(['salesChartAdmin', 'salesChart']);
    if (!canvas) return;

    prepareCanvasSize(canvas, 340);

    var labels = chartData.last_7_days.map(function(date) {
        return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    });

    var totalSales = chartData.last_7_days.map(function(date) {
        return chartData.daily_sales[date] ? Number(chartData.daily_sales[date].total_sales) : 0;
    });

    var totalTransactions = chartData.last_7_days.map(function(date) {
        return chartData.daily_sales[date] ? Number(chartData.daily_sales[date].total_transactions) : 0;
    });

    var data = {
        labels: labels,
        datasets: [
            {
                label: 'Total Penjualan (Rp)',
                fillColor: 'rgba(75, 192, 192, 0.2)',
                strokeColor: 'rgba(75, 192, 192, 1)',
                pointColor: 'rgba(75, 192, 192, 1)',
                pointStrokeColor: '#fff',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(75, 192, 192, 1)',
                data: totalSales
            },
            {
                label: 'Jumlah Transaksi',
                fillColor: 'rgba(255, 99, 132, 0.2)',
                strokeColor: 'rgba(255, 99, 132, 1)',
                pointColor: 'rgba(255, 99, 132, 1)',
                pointStrokeColor: '#fff',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(255, 99, 132, 1)',
                data: totalTransactions
            }
        ]
    };

    new Chart(canvas.getContext('2d')).Line(data, {
        responsive: false,
        maintainAspectRatio: false,
        bezierCurve: false,
        datasetFill: true,
        scaleBeginAtZero: true
    });
}

function createBpjsVsUmumChartV1() {
    var canvas = getCanvasByIds(['bpjsVsUmumChartAdmin', 'bpjsVsUmumChart']);
    if (!canvas) return;

    prepareCanvasSize(canvas, 340);

    var items = chartData.bpjs_vs_umum || [];
    if (!items.length) {
        items = [
            { transaction_type: 'BPJS', total_sales: 0 },
            { transaction_type: 'Umum', total_sales: 0 }
        ];
    }

    var colors = ['#36a2eb', '#ffce56', '#4bc0c0', '#9966ff'];
    var pieData = items.map(function(item, idx) {
        var color = colors[idx % colors.length];
        return {
            value: Number(item.total_sales || 0),
            color: color,
            highlight: color,
            label: item.transaction_type || 'Unknown'
        };
    });

    new Chart(canvas.getContext('2d')).Doughnut(pieData, {
        responsive: false,
        maintainAspectRatio: false,
        percentageInnerCutout: 40,
        tooltipTemplate: '<%=label%>: Rp <%=value%>'
    });
}

function createBpjsStatusChartV1() {
    var canvas = getCanvasByIds(['bpjsStatusChartAdmin', 'bpjsStatusChart']);
    if (!canvas) return;

    var labels = [];
    var values = [];
    (chartData.bpjs_status || []).forEach(function(item) {
        labels.push(item.transaction_status || 'Normal');
        values.push(Number(item.total_transactions || 0));
    });

    if (!labels.length) {
        labels = ['Belum Ada Data'];
        values = [0];
    }

    var data = {
        labels: labels,
        datasets: [{
            label: 'Jumlah Transaksi',
            fillColor: 'rgba(75, 192, 192, 0.8)',
            strokeColor: 'rgba(75, 192, 192, 1)',
            highlightFill: 'rgba(75, 192, 192, 1)',
            highlightStroke: 'rgba(75, 192, 192, 1)',
            data: values
        }]
    };

    new Chart(canvas.getContext('2d')).Bar(data, {
        responsive: true,
        maintainAspectRatio: false,
        scaleBeginAtZero: true
    });
}

function createBranchSalesChartV1() {
    var canvas = getCanvasByIds(['branchSalesChartAdmin', 'branchSalesChart']);
    if (!canvas || !chartData.branch_sales || !chartData.branch_sales.length) return;

    var data = {
        labels: chartData.branch_sales.map(function(item) {
            return item.branch_name;
        }),
        datasets: [{
            label: 'Total Penjualan (Rp)',
            fillColor: 'rgba(153, 102, 255, 0.8)',
            strokeColor: 'rgba(153, 102, 255, 1)',
            highlightFill: 'rgba(153, 102, 255, 1)',
            highlightStroke: 'rgba(153, 102, 255, 1)',
            data: chartData.branch_sales.map(function(item) {
                return Number(item.total_sales || 0);
            })
        }]
    };

    new Chart(canvas.getContext('2d')).Bar(data, {
        responsive: true,
        maintainAspectRatio: false,
        scaleBeginAtZero: true
    });
}

if (typeof Chart !== 'undefined') {
    createSalesChartV1();
    createBpjsVsUmumChartV1();
    createBpjsStatusChartV1();
    createBranchSalesChartV1();
} else {
    console.error('Chart.js tidak termuat');
}
@endif

@if(auth()->user()->isSuperAdmin())
var topFrameBrands = @json($topFrameBrands ?? []);

function createTopFrameBrandChartV1() {
    if (typeof Chart === 'undefined') return;

    var canvas = document.getElementById('topFrameBrandChartAdmin');
    if (!canvas) return;

    prepareCanvasSize(canvas, 340);

    var labels = [];
    var values = [];

    (topFrameBrands || []).forEach(function(item) {
        labels.push(item.merk_frame || 'Tanpa Merk');
        values.push(Number(item.total_qty || 0));
    });

    if (!labels.length) {
        labels = ['Belum Ada Data'];
        values = [0];
    }

    var data = {
        labels: labels,
        datasets: [{
            label: 'Qty Terjual',
            fillColor: 'rgba(221, 75, 57, 0.75)',
            strokeColor: 'rgba(221, 75, 57, 1)',
            highlightFill: 'rgba(221, 75, 57, 1)',
            highlightStroke: 'rgba(221, 75, 57, 1)',
            data: values
        }]
    };

    new Chart(canvas.getContext('2d')).Bar(data, {
        responsive: false,
        maintainAspectRatio: false,
        scaleBeginAtZero: true,
        barValueSpacing: 8
    });
}

createTopFrameBrandChartV1();
@endif

$(document).ready(function() {
    console.log('Dashboard JavaScript loaded');
    
    // Initialize DataTables for existing tables (excluding admin transaction table)
    $('.datatable:not(#table-transaksi-aktif-admin)').DataTable({
        responsive: true,
        pageLength: 10,
        order: [],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        columnDefs: [
            { targets: '_all', defaultContent: '-' }
        ]
    });
    
    // Specific initialization for admin transaction table with error handling
    function initAdminTransactionTable() {
        var $table = $('#table-transaksi-aktif-admin');
        if ($table.length && !$.fn.DataTable.isDataTable('#table-transaksi-aktif-admin')) {
            try {
                // Check if table has proper structure
                var headerCount = $table.find('thead th').length;
                var firstRowCount = $table.find('tbody tr:first td').length;
                var hasData = $table.find('tbody tr').length > 0;
                
                console.log('Table header columns:', headerCount);
                console.log('First row columns:', firstRowCount);
                console.log('Has data rows:', hasData);
                
                // Only initialize if we have proper structure or no data
                if (hasData && headerCount !== firstRowCount) {
                    console.error('Column count mismatch detected. Headers:', headerCount, 'Data:', firstRowCount);
                    console.log('Skipping DataTable initialization for admin transaction table');
                    return;
                }
                
                $table.DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[8, 'desc']], // Sort by date column
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                    },
                    columnDefs: [
                        { targets: '_all', defaultContent: '-' },
                        { targets: [5], className: 'text-right' }, // Total column
                        { targets: [0], className: 'text-center' } // No column
                    ],
                    drawCallback: function() {
                        console.log('Admin transaction table initialized successfully');
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable error:', error, thrown);
                    }
                });
            } catch (error) {
                console.error('Error initializing admin transaction table:', error);
                // Fallback: just add basic styling
                $table.addClass('table-striped table-hover');
            }
        }
    }
    
    // Initialize the table
    initAdminTransactionTable();
    

    
    // Unified modal close button handler
    $(document).on('click', '.modal .close, .modal [data-dismiss="modal"]', function(e) {
        var $modal = $(this).closest('.modal');
        $modal.modal('hide');
    });
    
    
    // Modal open handlers for admin dashboard
    $(document).on('click', 'a[href="#modal-frame-admin"]', function(e) {
        e.preventDefault();
        console.log('Frame modal clicked');
        $('#modal-frame-admin').modal('show');
    });
    
    $(document).on('click', 'a[href="#modal-lensa-admin"]', function(e) {
        e.preventDefault();
        console.log('Lensa modal clicked');
        $('#modal-lensa-admin').modal('show');
    });
    
    $(document).on('click', 'a[href="#modal-pasien-admin"]', function(e) {
        e.preventDefault();
        console.log('Pasien modal clicked');
        $('#modal-pasien-admin').modal('show');
    });
    
    $(document).on('click', 'a[href="#modal-transaksi-aktif-admin"]', function(e) {
        e.preventDefault();
        console.log('Transaksi modal clicked');
        $('#modal-transaksi-aktif-admin').modal('show');
    });
    
    // Reinitialize table when modal is shown
    $(document).on('shown.bs.modal', '#modal-transaksi-aktif-admin', function() {
        console.log('Admin transaction modal shown, reinitializing table...');
        setTimeout(function() {
            initAdminTransactionTable();
        }, 100);
    });
    
    // Serahkan lifecycle modal ke Bootstrap bawaan untuk menghindari layout kepotong.
    

    

    

    

});

// Function untuk menampilkan modal low stock
function showLowStockModal(type) {
    var modalId = '#modal-low-stock-' + type;
    $(modalId).modal('show');
}

// Passet: show menunggu modal
function showPassetModalMenunggu() {
    // Ensure single modal instance
    var $modal = $('#modalPassetMenunggu');
    if (!$modal.length) {
        var modalHtml = ''+
        '<div class="modal fade" id="modalPassetMenunggu" tabindex="-1" role="dialog" aria-hidden="true">'+
            '<div class="modal-dialog modal-lg" role="document">'+
                '<div class="modal-content">'+
                    '<div class="modal-header bg-warning">'+
                        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                        '<h4 class="modal-title"><i class="fa fa-clock-o"></i> Pekerjaan Menunggu Pengerjaan</h4>'+
                    '</div>'+
                    '<div class="modal-body">'+
                        '<div class="table-responsive">'+
                            '<table class="table table-bordered table-striped" id="tablePassetMenunggu" style="width:100%">'+
                                '<thead>'+
                                    '<tr>'+
                                        '<th>No</th>'+
                                        '<th>Tanggal</th>'+
                                        '<th>Kode</th>'+
                                        '<th>Pasien</th>'+
                                        '<th>Cabang</th>'+
                                        '<th>Status</th>'+
                                        '<th>Aksi</th>'+
                                    '</tr>'+
                                '</thead>'+
                                '<tbody></tbody>'+
                            '</table>'+
                        '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                        '<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>';
        $('body').append(modalHtml);
        $modal = $('#modalPassetMenunggu');
    }

    // Bring modal to body top and open
    $modal.appendTo('body');

    // Initialize / reinitialize DataTable
    var dt = $('#tablePassetMenunggu').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        autoWidth: false,
        responsive: false,
        ajax: {
            url: '{{ route('passet.data') }}',
            data: { status: 'Menunggu Pengerjaan' },
            error: function(xhr) {
                var msg = 'Gagal memuat data.';
                try { if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message; } catch(e) {}
                Swal.fire('Error', msg, 'error');
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '6%' },
            { data: 'tanggal', name: 'tanggal', width: '14%' },
            { data: 'kode_penjualan', name: 'kode_penjualan', width: '16%' },
            { data: 'pasien_name', name: 'pasien_name' },
            { data: 'cabang_name', name: 'cabang_name', width: '14%' },
            { data: 'status_pengerjaan', name: 'status_pengerjaan', width: '14%' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, width: '12%' }
        ],
        order: [[1, 'desc']]
    });

    $modal.off('shown.bs.modal.passet').on('shown.bs.modal.passet', function() {
        try { dt.columns.adjust(); } catch(e) {}
    });

    // Open modal (with Bootstrap fallback if needed)
    function openModal() { $modal.modal({ backdrop: true, keyboard: true, show: true }); }
    if (typeof $.fn.modal !== 'function') {
        var s = document.createElement('script');
        s.src = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js';
        s.onload = openModal;
        document.body.appendChild(s);
    } else {
        openModal();
    }
}

// Action handler for "Tandai Selesai" inside modal table
function markAsSelesai(url) {
    var CURRENT_USER_ID = {{ auth()->id() ?? 'null' }};
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Anda yakin pekerjaan ini sudah selesai?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (!result.isConfirmed) return;
        $.post(url, { '_token': '{{ csrf_token() }}', 'user_id': CURRENT_USER_ID })
            .done(function(resp){
                // Reload table if exists
                if ($.fn.DataTable.isDataTable('#tablePassetMenunggu')) {
                    $('#tablePassetMenunggu').DataTable().ajax.reload(null, false);
                }
                // Update small-box count (decrement by 1 if > 0)
                var $countEl = $(".small-box.bg-warning .inner h3");
                var val = parseInt(($countEl.text()||'0').replace(/[^0-9]/g,''),10) || 0;
                if (val > 0) { $countEl.text(val - 1); }
                Swal.fire('Berhasil!', resp.message || 'Status berhasil diubah.', 'success');
            })
            .fail(function(){
                Swal.fire('Gagal!', 'Tidak dapat mengubah status.', 'error');
            });
    });
}

@if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
// Grafik untuk Admin & Super Admin
$(function() {
    // Grafik Penjualan 7 Hari Terakhir untuk Admin
    var salesCtxAdmin = document.getElementById('salesChartAdmin');
    if (salesCtxAdmin) {
        var salesChartAdmin = new Chart(salesCtxAdmin.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: [1200000, 1500000, 1800000, 1400000, 2000000, 2500000, 2200000],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }, {
                    label: 'Jumlah Transaksi',
                    data: [15, 18, 22, 16, 25, 30, 28],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Total Penjualan (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Total Penjualan (Rp)') {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
    }

    // Grafik BPJS vs Umum untuk Admin
    var bpjsVsUmumCtxAdmin = document.getElementById('bpjsVsUmumChartAdmin');
    if (bpjsVsUmumCtxAdmin) {
        var bpjsVsUmumChartAdmin = new Chart(bpjsVsUmumCtxAdmin.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['BPJS', 'Umum'],
                datasets: [{
                    data: [65, 35],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + percentage + '% (' + value + ' transaksi)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Grafik Status Transaksi BPJS untuk Admin
    var bpjsStatusCtxAdmin = document.getElementById('bpjsStatusChartAdmin');
    if (bpjsStatusCtxAdmin) {
        var bpjsStatusChartAdmin = new Chart(bpjsStatusCtxAdmin.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Sudah Diambil', 'Sedang Dikerjakan', 'Menunggu Pembayaran'],
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: [45, 20, 15],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Grafik Penjualan per Cabang untuk Super Admin
    var branchSalesCtxAdmin = document.getElementById('branchSalesChartAdmin');
    if (branchSalesCtxAdmin) {
        var branchSalesChartAdmin = new Chart(branchSalesCtxAdmin.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Optik Melati', 'Optik Melati 2'],
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: [8500000, 7200000],
                    backgroundColor: [
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderColor: [
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Penjualan (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }
});
@endif

// ===== SCRIPT UNTUK POPULATE MODAL DETAIL DATA ADMIN =====
@if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
$(function() {
    // Data dari controller
    const detailFrame = {!! json_encode($detailFrame ?? []) !!};
    const detailLensa = {!! json_encode($detailLensa ?? []) !!};
    const detailPasien = {!! json_encode($detailPasien ?? []) !!};
    const detailAksesoris = {!! json_encode($detailAksesoris ?? []) !!};
    const detailTransaksiAktif = {!! json_encode($detailTransaksiAktif ?? []) !!};
    
    // Populate Frame Table
    function populateFrameTable() {
        const tbody = $('#table-detail-frame-admin tbody');
        tbody.empty();
        
        if (!detailFrame || detailFrame.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center"><em>Tidak ada data frame</em></td></tr>');
            return;
        }
        
        detailFrame.forEach((item, index) => {
            const row = `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.kode_frame || '-'}</strong></td>
                <td>${item.merk_frame || '-'}</td>
                <td>${item.jenis_frame || '-'}</td>
                <td class="text-right">Rp ${parseInt(item.harga_jual || 0).toLocaleString('id-ID')}</td>
                <td><span class="badge ${parseInt(item.stok || 0) <= 2 ? 'bg-red' : 'bg-green'}">${item.stok || 0}</span></td>
            </tr>`;
            tbody.append(row);
        });
    }
    
    // Populate Lensa Table
    function populateLensaTable() {
        const tbody = $('#table-detail-lensa-admin tbody');
        tbody.empty();
        
        if (!detailLensa || detailLensa.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center"><em>Tidak ada data lensa</em></td></tr>');
            return;
        }
        
        detailLensa.forEach((item, index) => {
            const row = `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.kode_lensa || '-'}</strong></td>
                <td>${item.tipe_lensa || '-'}</td>
                <td>${item.brand || '-'}</td>
                <td class="text-right">Rp ${parseInt(item.harga_jual || 0).toLocaleString('id-ID')}</td>
                <td><span class="badge ${parseInt(item.stok || 0) <= 2 ? 'bg-red' : 'bg-green'}">${item.stok || 0}</span></td>
            </tr>`;
            tbody.append(row);
        });
    }
    
    // Populate Pasien Table
    function populatePasienTable() {
        const tbody = $('#table-detail-pasien-admin tbody');
        tbody.empty();
        
        if (!detailPasien || detailPasien.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center"><em>Tidak ada data pasien</em></td></tr>');
            return;
        }
        
        detailPasien.forEach((item, index) => {
            const createdAt = item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID', {year: 'numeric', month: '2-digit', day: '2-digit'}) : '-';
            const serviceTypeClass = {
                'BPJS I': 'label-info',
                'BPJS II': 'label-info',
                'BPJS III': 'label-info',
                'UMUM': 'label-default'
            };
            
            const row = `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.nama_pasien || '-'}</strong></td>
                <td>${item.nohp || '-'}</td>
                <td><span class="label ${serviceTypeClass[item.service_type || 'UMUM'] || 'label-default'}">${item.service_type || 'UMUM'}</span></td>
                <td>${createdAt}</td>
            </tr>`;
            tbody.append(row);
        });
    }
    
    // Populate Aksesoris Table
    function populateAksesorisTable() {
        const tbody = $('#table-detail-aksesoris-admin tbody');
        tbody.empty();
        
        if (!detailAksesoris || detailAksesoris.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center"><em>Tidak ada data aksesoris</em></td></tr>');
            return;
        }
        
        detailAksesoris.forEach((item, index) => {
            const row = `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.kode_aksesoris || '-'}</strong></td>
                <td>${item.nama_produk || item.nama_aksesoris || '-'}</td>
                <td class="text-right">Rp ${parseInt(item.harga_jual || 0).toLocaleString('id-ID')}</td>
                <td><span class="badge ${parseInt(item.stok || 0) <= 2 ? 'bg-red' : 'bg-green'}">${item.stok || 0}</span></td>
            </tr>`;
            tbody.append(row);
        });
    }
    
    // Populate Transaksi Aktif Table
    function populateTransaksiAktifTable() {
        const tbody = $('#table-transaksi-aktif-admin tbody');
        tbody.empty();
        
        if (!detailTransaksiAktif || detailTransaksiAktif.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center"><em>Tidak ada transaksi aktif hari ini</em></td></tr>');
            return;
        }
        
        detailTransaksiAktif.forEach((item, index) => {
            const createdAt = item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'}) : '-';
            const serviceType = item.pasien?.service_type || 'UMUM';
            const statusClass = item.status_pengerjaan === 'Sudah Diambil' ? 'label-success' : 'label-warning';
            
            const row = `<tr>
                <td>${index + 1}</td>
                <td><strong>${item.kode_penjualan || '-'}</strong></td>
                <td>${item.pasien?.nama_pasien || item.nama_pasien_manual || '-'}</td>
                <td><span class="label label-${serviceType.includes('BPJS') ? 'info' : 'default'}">${serviceType}</span></td>
                <td class="text-right">Rp ${parseInt(item.total || 0).toLocaleString('id-ID')}</td>
                <td><span class="label ${statusClass}">${item.status_pengerjaan || '-'}</span></td>
                <td>${item.dokter?.nama || item.dokter_manual || '-'}</td>
                <td>${createdAt}</td>
            </tr>`;
            tbody.append(row);
        });
    }
    
    // Handle modal frame show
    $('#modal-frame-admin').on('show.bs.modal', function() {
        populateFrameTable();
    });
    
    // Handle modal lensa show
    $('#modal-lensa-admin').on('show.bs.modal', function() {
        populateLensaTable();
    });
    
    // Handle modal pasien show
    $('#modal-pasien-admin').on('show.bs.modal', function() {
        populatePasienTable();
    });
    
    // Handle modal aksesoris show
    $('#modal-aksesoris-admin').on('show.bs.modal', function() {
        populateAksesorisTable();
    });
    
    // Handle modal transaksi show
    $('#modal-transaksi-aktif-admin').on('show.bs.modal', function() {
        populateTransaksiAktifTable();
    });
    
    // Initial population when page loads
    populateFrameTable();
    populateLensaTable();
    populatePasienTable();
    populateAksesorisTable();
    populateTransaksiAktifTable();
});
@endif

</script>
@endpush