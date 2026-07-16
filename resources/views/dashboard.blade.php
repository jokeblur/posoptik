@extends('layouts.master')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <h1 class="page-header">Dashboard Admin</h1>
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <div class="row" style="margin-bottom: 24px;">
        <div class="col-md-3">
            <div class="small-box bg-aqua" style="cursor: pointer;" onclick="openDetailModal('frame')">
                <div class="inner">
                    <h3>{{ $totalFrame ?? 0 }}</h3>
                    <p>Frame</p>
                </div>
                <div class="icon"><i class="fa fa-glasses"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-green" style="cursor: pointer;" onclick="openDetailModal('lensa')">
                <div class="inner">
                    <h3>{{ $totalLensa ?? 0 }}</h3>
                    <p>Lensa</p>
                </div>
                <div class="icon"><i class="fa fa-tablets"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-yellow" style="cursor: pointer;" onclick="openDetailModal('aksesoris')">
                <div class="inner">
                    <h3>{{ $totalAksesoris ?? 0 }}</h3>
                    <p>Aksesoris</p>
                </div>
                <div class="icon"><i class="fa fa-cube"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-red" style="cursor: pointer;" onclick="openDetailModal('pasien')">
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

<!-- Modal Detail Frame -->
<div class="modal fade" id="modal-detail-frame" tabindex="-1" role="dialog" aria-labelledby="modal-detail-frame-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-detail-frame-label">Detail Frame</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-striped" id="table-detail-frame">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Frame</th>
                            <th>Merk Frame</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Jenis Frame</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Lensa -->
<div class="modal fade" id="modal-detail-lensa" tabindex="-1" role="dialog" aria-labelledby="modal-detail-lensa-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-detail-lensa-label">Detail Lensa</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-striped" id="table-detail-lensa">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Lensa</th>
                            <th>Tipe Lensa</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Brand</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pasien -->
<div class="modal fade" id="modal-detail-pasien" tabindex="-1" role="dialog" aria-labelledby="modal-detail-pasien-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-detail-pasien-label">Detail Pasien</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-striped" id="table-detail-pasien">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pasien</th>
                            <th>No HP</th>
                            <th>Service Type</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Aksesoris -->
<div class="modal fade" id="modal-detail-aksesoris" tabindex="-1" role="dialog" aria-labelledby="modal-detail-aksesoris-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-detail-aksesoris-label">Detail Aksesoris</h4>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-striped" id="table-detail-aksesoris">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Aksesoris</th>
                            <th>Nama Aksesoris</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Data yang dikirim dari controller
const detailFrame = {!! json_encode($detailFrame ?? []) !!};
const detailLensa = {!! json_encode($detailLensa ?? []) !!};
const detailPasien = {!! json_encode($detailPasien ?? []) !!};
const detailAksesoris = {!! json_encode($detailAksesoris ?? []) !!};

// Fungsi untuk membuka detail modal
function openDetailModal(type) {
    let data = [];
    let tableId = '';
    let modalId = '';
    
    switch(type) {
        case 'frame':
            data = detailFrame;
            tableId = '#table-detail-frame';
            modalId = '#modal-detail-frame';
            populateFrameTable(data);
            break;
        case 'lensa':
            data = detailLensa;
            tableId = '#table-detail-lensa';
            modalId = '#modal-detail-lensa';
            populateLensaTable(data);
            break;
        case 'pasien':
            data = detailPasien;
            tableId = '#table-detail-pasien';
            modalId = '#modal-detail-pasien';
            populatePasienTable(data);
            break;
        case 'aksesoris':
            data = detailAksesoris;
            tableId = '#table-detail-aksesoris';
            modalId = '#modal-detail-aksesoris';
            populateAksesorisTable(data);
            break;
    }
    
    $(modalId).modal('show');
}

// Fungsi populate table Frame
function populateFrameTable(data) {
    const tbody = $('#table-detail-frame tbody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>');
        return;
    }
    
    data.forEach((item, index) => {
        const row = `<tr>
            <td>${index + 1}</td>
            <td>${item.kode_frame || '-'}</td>
            <td>${item.merk_frame || '-'}</td>
            <td>Rp ${item.harga_jual ? parseInt(item.harga_jual).toLocaleString('id-ID') : '0'}</td>
            <td>${item.stok || '0'}</td>
            <td>${item.jenis_frame || '-'}</td>
        </tr>`;
        tbody.append(row);
    });
}

// Fungsi populate table Lensa
function populateLensaTable(data) {
    const tbody = $('#table-detail-lensa tbody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>');
        return;
    }
    
    data.forEach((item, index) => {
        const row = `<tr>
            <td>${index + 1}</td>
            <td>${item.kode_lensa || '-'}</td>
            <td>${item.tipe_lensa || '-'}</td>
            <td>Rp ${item.harga_jual ? parseInt(item.harga_jual).toLocaleString('id-ID') : '0'}</td>
            <td>${item.stok || '0'}</td>
            <td>${item.brand || '-'}</td>
        </tr>`;
        tbody.append(row);
    });
}

// Fungsi populate table Pasien
function populatePasienTable(data) {
    const tbody = $('#table-detail-pasien tbody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>');
        return;
    }
    
    data.forEach((item, index) => {
        const createdAt = item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-';
        const row = `<tr>
            <td>${index + 1}</td>
            <td>${item.nama_pasien || '-'}</td>
            <td>${item.nohp || '-'}</td>
            <td>${item.service_type || '-'}</td>
            <td>${createdAt}</td>
        </tr>`;
        tbody.append(row);
    });
}

// Fungsi populate table Aksesoris
function populateAksesorisTable(data) {
    const tbody = $('#table-detail-aksesoris tbody');
    tbody.empty();
    
    if (data.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>');
        return;
    }
    
    data.forEach((item, index) => {
        const row = `<tr>
            <td>${index + 1}</td>
            <td>${item.kode_aksesoris || '-'}</td>
            <td>${item.nama_aksesoris || '-'}</td>
            <td>Rp ${item.harga_jual ? parseInt(item.harga_jual).toLocaleString('id-ID') : '0'}</td>
            <td>${item.stok || '0'}</td>
        </tr>`;
        tbody.append(row);
    });
}

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