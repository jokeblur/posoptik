@extends('layouts.master')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <h1 class="page-header">Dashboard Admin</h1>
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <div class="row" style="margin-bottom: 24px;">
        <div class="col-md-3">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $totalFrame ?? 0 }}</h3>
                    <p>Frame</p>
                </div>
                <div class="icon"><i class="fa fa-glasses"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $totalLensa ?? 0 }}</h3>
                    <p>Lensa</p>
                </div>
                <div class="icon"><i class="fa fa-tablets"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $totalAksesoris ?? 0 }}</h3>
                    <p>Aksesoris</p>
                </div>
                <div class="icon"><i class="fa fa-cube"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $totalPasien ?? 0 }}</h3>
                    <p>Pasien</p>
                </div>
                <div class="icon"><i class="fa fa-user"></i></div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $totalTransaksiAktif ?? 0 }}</h3>
                    <p>Transaksi Aktif Hari Ini</p>
                </div>
                <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Grafik Penjualan Bulanan</h3>
                </div>
                <div class="box-body">
                    <div class="chart">
                        <canvas id="monthlySalesChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        {{-- Konten lain dashboard bisa diletakkan di sini --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Fetch chart data via AJAX
    $.ajax({
        url: '{{ route('dashboard.chart-data') }}',
        method: 'GET',
        success: function(response) {
            if (response.monthly_sales_branches) {
                var ctx = document.getElementById('monthlySalesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: response.monthly_sales_branches.labels,
                        datasets: response.monthly_sales_branches.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Penjualan Bulanan Cabang 1 vs Cabang 2 (12 Bulan Terakhir)'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching chart data:', error);
        }
    });
});
</script>
@endpush