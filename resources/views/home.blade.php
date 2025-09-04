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
                <div class="box-body">
                    <canvas id="salesChartAdmin" style="height: 300px;"></canvas>
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
                <div class="box-body">
                    <canvas id="bpjsVsUmumChartAdmin" style="height: 300px;"></canvas>
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
    @if(auth()->user()->isSuperAdmin() && $chartData)
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

    {{-- Box Omset untuk Kasir --}}
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-4">
            <div class="small-box bg-success omset-total" style="cursor:pointer" onclick="$('#modalKasirOmset').modal('show')">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetKasir ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); padding: 3px 10px; font-size: 12px;">
                    <span class="jumlah-transaksi-badge">{{ $transaksiKasir ? $transaksiKasir->count() : 0 }}</span> transaksi hari ini
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info omset-bpjs" style="cursor:pointer" onclick="$('#modalKasirBpjs').modal('show')">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetBpjs ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset BPJS Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-heartbeat"></i></div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); padding: 3px 10px; font-size: 12px;">
                    Real-time update
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning omset-umum" style="cursor:pointer" onclick="$('#modalKasirUmum').modal('show')">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetUmum ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset Umum Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-users"></i></div>
                <div class="small-box-footer" style="background: rgba(0,0,0,0.1); padding: 3px 10px; font-size: 12px;">
                    Auto refresh
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
                    <h4 class="modal-title"><i class="fa fa-money"></i> Detail Omset Hari Ini</h4>
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
                    <h4 class="modal-title"><i class="fa fa-heartbeat"></i> Detail Omset BPJS Hari Ini</h4>
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
                    <h4 class="modal-title"><i class="fa fa-users"></i> Detail Omset Umum Hari Ini</h4>
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
                    <h3 class="box-title">Transaksi Hari Ini</h3>
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
  overflow: hidden;
}

body:not(.modal-open) {
  overflow: auto;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Bootstrap 3 CDN as fallback -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
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
<!-- DataTables Bootstrap 3 compatible -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>
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

// Setup real-time connections with custom callbacks
$(function() {
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
    
    // Setup real-time omset connection
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
    
    // Setup notifications
    window.RealtimeManager.connectNotifications({
        onData: function(notification) {
            showRealtimeNotification(notification);
        }
    });
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

@if(auth()->user()->isSuperAdmin() && isset($chartData))
// Data untuk grafik
var chartData = @json($chartData);

// Grafik Penjualan 7 Hari Terakhir
var salesCtx = document.getElementById('salesChart').getContext('2d');
var salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: chartData.last_7_days.map(function(date) {
            return new Date(date).toLocaleDateString('id-ID', {day: '2-digit', month: 'short'});
        }),
        datasets: [{
            label: 'Total Penjualan (Rp)',
            data: chartData.last_7_days.map(function(date) {
                return chartData.daily_sales[date] ? chartData.daily_sales[date].total_sales : 0;
            }),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Jumlah Transaksi',
            data: chartData.last_7_days.map(function(date) {
                return chartData.daily_sales[date] ? chartData.daily_sales[date].total_transactions : 0;
            }),
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
        }
    }
});

// Grafik BPJS vs Umum
var bpjsVsUmumCtx = document.getElementById('bpjsVsUmumChart').getContext('2d');
var bpjsVsUmumChart = new Chart(bpjsVsUmumCtx, {
    type: 'doughnut',
    data: {
        labels: chartData.bpjs_vs_umum.map(function(item) {
            return item.transaction_type;
        }),
        datasets: [{
            data: chartData.bpjs_vs_umum.map(function(item) {
                return item.total_sales;
            }),
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)'
            ],
            borderWidth: 1
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
                        return label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Grafik Status Transaksi BPJS
var bpjsStatusCtx = document.getElementById('bpjsStatusChart').getContext('2d');
var bpjsStatusChart = new Chart(bpjsStatusCtx, {
    type: 'bar',
    data: {
        labels: chartData.bpjs_status.map(function(item) {
            return item.transaction_status || 'Normal';
        }),
        datasets: [{
            label: 'Jumlah Transaksi',
            data: chartData.bpjs_status.map(function(item) {
                return item.total_transactions;
            }),
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
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
                    text: 'Jumlah Transaksi'
                }
            }
        }
    }
});

@if($chartData['branch_sales'])
// Grafik Penjualan per Cabang
var branchSalesCtx = document.getElementById('branchSalesChart').getContext('2d');
var branchSalesChart = new Chart(branchSalesCtx, {
    type: 'bar',
    data: {
        labels: chartData.branch_sales.map(function(item) {
            return item.branch_name;
        }),
        datasets: [{
            label: 'Total Penjualan (Rp)',
            data: chartData.branch_sales.map(function(item) {
                return item.total_sales;
            }),
            backgroundColor: 'rgba(153, 102, 255, 0.8)',
            borderColor: 'rgba(153, 102, 255, 1)',
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
                }
            }
        },
        plugins: {
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
@endif
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
    

    
    // Fix modal close buttons for admin dashboard
    $(document).on('click', '.modal .close, .modal [data-dismiss="modal"]', function(e) {
        e.preventDefault();
        console.log('Modal close button clicked');
        var modal = $(this).closest('.modal');
        modal.modal('hide');
        // Remove backdrop if stuck
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 300);
    });
    
    // Alternative close button handler
    $(document).on('click', '.modal-header .close', function(e) {
        e.preventDefault();
        console.log('Modal header close clicked');
        var modal = $(this).closest('.modal');
        modal.modal('hide');
        // Remove backdrop if stuck
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 300);
    });
    
    // Specific handlers for admin modals
    $(document).on('click', '#modal-frame-admin .close', function(e) {
        e.preventDefault();
        console.log('Frame modal close clicked');
        $('#modal-frame-admin').modal('hide');
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 300);
    });
    
    $(document).on('click', '#modal-lensa-admin .close', function(e) {
        e.preventDefault();
        console.log('Lensa modal close clicked');
        $('#modal-lensa-admin').modal('hide');
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 300);
    });
    
    $(document).on('click', '#modal-pasien-admin .close', function(e) {
        e.preventDefault();
        console.log('Pasien modal close clicked');
        $('#modal-pasien-admin').modal('hide');
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 300);
    });
    
    $(document).on('click', '#modal-transaksi-aktif-admin .close', function(e) {
        e.preventDefault();
        console.log('Transaksi modal close clicked');
        $('#modal-transaksi-aktif-admin').modal('hide');
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 300);
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
    
    // Fix modal backdrop issues
    $(document).on('hidden.bs.modal', '.modal', function() {
        console.log('Modal hidden event triggered');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
    
    $(document).on('show.bs.modal', '.modal', function() {
        console.log('Modal show event triggered');
        // Remove any existing backdrops
        $('.modal-backdrop').remove();
    });
    
    // Click outside modal to close
    $(document).on('click', '.modal-backdrop', function() {
        console.log('Backdrop clicked');
        $('.modal').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
    

    

    

    

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
</script>
@endpush