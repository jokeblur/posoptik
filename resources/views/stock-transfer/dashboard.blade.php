@extends('layouts.master')

@section('title', 'Dashboard Transfer Stok')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Dashboard Transfer Stok</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('stock-transfer.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Buat Transfer Baru
                    </a>
                    <a href="{{ route('stock-transfer.index') }}" class="btn btn-info btn-sm">
                        <i class="fa fa-list"></i> Lihat Semua
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-blue">
                            <div class="inner">
                                <h3>{{ $totalTransfers }}</h3>
                                <p>Total Transfer</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-exchange"></i>
                            </div>
                            <a href="{{ route('stock-transfer.index') }}" class="small-box-footer">
                                Lihat Semua <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                <h3>{{ $pendingTransfers }}</h3>
                                <p>Menunggu Persetujuan</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <a href="{{ route('stock-transfer.index') }}?status=Pending" class="small-box-footer">
                                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{ $completedTransfers }}</h3>
                                <p>Transfer Selesai</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <a href="{{ route('stock-transfer.index') }}?status=Completed" class="small-box-footer">
                                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red">
                            <div class="inner">
                                <h3>{{ $rejectedTransfers }}</h3>
                                <p>Transfer Ditolak</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-times-circle"></i>
                            </div>
                            <a href="{{ route('stock-transfer.index') }}?status=Rejected" class="small-box-footer">
                                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Transfers -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Transfer Terbaru</h3>
                                <div class="box-tools pull-right">
                                    <a href="{{ route('stock-transfer.index') }}" class="btn btn-xs btn-info btn-flat">
                                        Lihat Semua
                                    </a>
                                </div>
                            </div>
                            <div class="box-body">
                                @if($recentTransfers->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Status</th>
                                                    <th>Tanggal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentTransfers as $transfer)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('stock-transfer.show', $transfer->id) }}">
                                                                {{ $transfer->kode_transfer }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            @switch($transfer->status)
                                                                @case('Pending')
                                                                    <span class="label label-warning">Pending</span>
                                                                    @break
                                                                @case('Approved')
                                                                    <span class="label label-success">Approved</span>
                                                                    @break
                                                                @case('Rejected')
                                                                    <span class="label label-danger">Rejected</span>
                                                                    @break
                                                                @case('Completed')
                                                                    <span class="label label-info">Completed</span>
                                                                    @break
                                                                @case('Cancelled')
                                                                    <span class="label label-default">Cancelled</span>
                                                                    @break
                                                            @endswitch
                                                        </td>
                                                        <td>{{ $transfer->created_at->format('d/m/Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted text-center">Belum ada transfer stok</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Transfer Berdasarkan Cabang</h3>
                            </div>
                            <div class="box-body">
                                @if($branchStats->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Cabang</th>
                                                    <th>Total Transfer</th>
                                                    <th>Selesai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($branchStats as $stat)
                                                    <tr>
                                                        <td>{{ $stat->branch_name }}</td>
                                                        <td>{{ $stat->total_transfers }}</td>
                                                        <td>{{ $stat->completed_transfers }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted text-center">Belum ada data cabang</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Aksi Cepat</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <a href="{{ route('stock-transfer.create') }}" class="btn btn-primary btn-block">
                                            <i class="fa fa-plus fa-2x"></i><br>
                                            Buat Transfer Baru
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('stock-transfer.index') }}" class="btn btn-info btn-block">
                                            <i class="fa fa-list fa-2x"></i><br>
                                            Lihat Semua Transfer
                                        </a>
                                    </div>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                        <div class="col-md-3">
                                            <a href="{{ route('stock-transfer.export') }}" class="btn btn-success btn-block">
                                                <i class="fa fa-download fa-2x"></i><br>
                                                Export Data
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="{{ route('stock-transfer.index') }}?status=Pending" class="btn btn-warning btn-block">
                                                <i class="fa fa-clock-o fa-2x"></i><br>
                                                Transfer Pending
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
