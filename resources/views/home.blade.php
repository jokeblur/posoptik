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
    @includeIf('partials.dashboard_modals')
    
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

    {{-- Box untuk Kasir --}}
    @if(auth()->user()->isKasir())
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-3">
            <div class="small-box bg-aqua">
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
    
    {{-- Box Omset untuk Kasir --}}
    <div class="row" style="margin-bottom: 32px;">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetKasir ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetBpjs ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset BPJS Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-heartbeat"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Rp {{ number_format($omsetUmum ?? 0, 0, ',', '.') }}</h3>
                    <p>Omset Umum Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-users"></i></div>
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
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
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
                                    <td>{{ $transaksi->created_at->format('H:i') }}</td>
                                    <td>{{ $transaksi->no_transaksi }}</td>
                                    <td>{{ $transaksi->pasien->nama ?? '-' }}</td>
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
.animated.shake {
  animation: shake 1.2s infinite;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-8px); }
  40%, 80% { transform: translateX(8px); }
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.APP_BASE_URL = '{{ url('/') }}';
@if(auth()->user()->isKasir())
var KASIR_BRANCH_ID = {{ auth()->user()->branch_id ?? 0 }};
$(function() {
    setInterval(function() {
        $.getJSON(window.APP_BASE_URL + '/api/open-day-status?branch_id=' + KASIR_BRANCH_ID, function(res) {
            if(res.is_open) {
                $('#kasir-status-info').html('<div class="alert alert-success text-center" style="font-size:16px; margin-bottom:10px;"><b>KASIR BUKA</b> &mdash; Kasir cabang sudah dibuka (' + (res.open_time ? new Date(res.open_time).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : '-') + ')</div>');
            } else {
                $('#kasir-status-info').html('<div class="alert alert-danger text-center" style="font-size:16px; margin-bottom:10px;"><b>KASIR TUTUP</b> &mdash; Kasir cabang belum dibuka atau sudah ditutup</div>');
            }
        });
    }, 5000);
});
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

$(function() {
    $('.datatable').DataTable({
        responsive: true,
        pageLength: 10,
        order: []
    });
});
</script>
@endpush