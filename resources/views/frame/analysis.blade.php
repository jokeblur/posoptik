@extends('layouts.master')

@section('title')
    Analisa Frame
@endsection

@section('breadcrumb')
    @parent
    <li><a href="{{ route('frame.index') }}">Frame</a></li>
    <li class="active">Analisa Frame</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('frame.index') }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Kembali ke Data Frame
        </a>
        <form method="GET" action="{{ route('frame.analysis') }}" class="form-inline" style="display:inline-block; margin-left:10px;">
            <div class="form-group">
                <label for="month" style="margin-right:8px;">Pilih Bulan</label>
                <input type="month" class="form-control" id="month" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}">
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bar-chart"></i>
                    Analisa Frame BPJS
                </h3>
                <small class="text-muted">({{ $frameAnalysisPeriodLabel ?? '30 Hari Terakhir' }})</small>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-6">
                        <div class="small-box bg-aqua" style="margin-bottom: 0; min-height: 120px;">
                            <div class="inner">
                                <h3>{{ number_format($frameAnalysisBpjsSummary->total_qty ?? 0) }}</h3>
                                <p>Total Qty Terjual</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small-box bg-blue" style="margin-bottom: 0; min-height: 120px;">
                            <div class="inner">
                                <h3>{{ number_format($frameAnalysisBpjsSummary->total_transaksi ?? 0) }}</h3>
                                <p>Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>
                @if($frameAnalysisBpjs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Merk</th>
                                    <th>Jenis</th>
                                    <th>Qty</th>
                                    <th>Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frameAnalysisBpjs as $item)
                                    <tr>
                                        <td>{{ $item->merk_frame }}</td>
                                        <td>{{ $item->jenis_frame }}</td>
                                        <td>{{ number_format($item->total_qty) }}</td>
                                        <td>{{ number_format($item->total_transaksi) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Belum ada data penjualan frame BPJS dalam {{ $frameAnalysisPeriodLabel ?? '30 Hari Terakhir' }}.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bar-chart"></i>
                    Analisa Frame Umum
                </h3>
                <small class="text-muted">({{ $frameAnalysisPeriodLabel ?? '30 Hari Terakhir' }})</small>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-6">
                        <div class="small-box bg-green" style="margin-bottom: 0; min-height: 120px;">
                            <div class="inner">
                                <h3>{{ number_format($frameAnalysisUmumSummary->total_qty ?? 0) }}</h3>
                                <p>Total Qty Terjual</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small-box bg-olive" style="margin-bottom: 0; min-height: 120px;">
                            <div class="inner">
                                <h3>{{ number_format($frameAnalysisUmumSummary->total_transaksi ?? 0) }}</h3>
                                <p>Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>
                @if($frameAnalysisUmum->count() > 0)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Optik Melati 1</strong></div>
                                <div class="panel-body" style="padding: 0;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                                            <thead>
                                                <tr>
                                                    <th>Merk</th>
                                                    <th>Jenis</th>
                                                    <th>Qty</th>
                                                    <th>Transaksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($frameAnalysisUmum->where('cabang', 'Optik Melati 1') as $item)
                                                    <tr>
                                                        <td>{{ $item->merk_frame }}</td>
                                                        <td>{{ $item->jenis_frame }}</td>
                                                        <td>{{ number_format($item->total_qty) }}</td>
                                                        <td>{{ number_format($item->total_transaksi) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Optik Melati 2</strong></div>
                                <div class="panel-body" style="padding: 0;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                                            <thead>
                                                <tr>
                                                    <th>Merk</th>
                                                    <th>Jenis</th>
                                                    <th>Qty</th>
                                                    <th>Transaksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($frameAnalysisUmum->where('cabang', 'Optik Melati 2') as $item)
                                                    <tr>
                                                        <td>{{ $item->merk_frame }}</td>
                                                        <td>{{ $item->jenis_frame }}</td>
                                                        <td>{{ number_format($item->total_qty) }}</td>
                                                        <td>{{ number_format($item->total_transaksi) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-muted">Belum ada data penjualan frame umum dalam {{ $frameAnalysisPeriodLabel ?? '30 Hari Terakhir' }}.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart"></i> Grafik Frame BPJS</h3>
            </div>
            <div class="box-body">
                <canvas id="chart-frame-bpjs" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart"></i> Grafik Frame Umum OM1</h3>
            </div>
            <div class="box-body">
                <canvas id="chart-frame-umum-om1" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart"></i> Grafik Frame Umum OM2</h3>
            </div>
            <div class="box-body">
                <canvas id="chart-frame-umum-om2" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const bpjsLabels = @json($frameAnalysisBpjs->pluck('merk_frame')->map(fn($value) => $value ?: 'Tanpa Merk')->values());
    const bpjsData = @json($frameAnalysisBpjs->pluck('total_qty')->map(fn($value) => (int) $value)->values());

    const umumOm1Labels = @json($frameAnalysisUmum->where('cabang', 'Optik Melati 1')->pluck('merk_frame')->map(fn($value) => $value ?: 'Tanpa Merk')->values());
    const umumOm1Data = @json($frameAnalysisUmum->where('cabang', 'Optik Melati 1')->pluck('total_qty')->map(fn($value) => (int) $value)->values());

    const umumOm2Labels = @json($frameAnalysisUmum->where('cabang', 'Optik Melati 2')->pluck('merk_frame')->map(fn($value) => $value ?: 'Tanpa Merk')->values());
    const umumOm2Data = @json($frameAnalysisUmum->where('cabang', 'Optik Melati 2')->pluck('total_qty')->map(fn($value) => (int) $value)->values());

    const createChart = (canvasId, labels, data, title, color) => {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !labels.length) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: title,
                    data,
                    backgroundColor: color,
                    borderColor: color,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    };

    createChart('chart-frame-bpjs', bpjsLabels, bpjsData, 'Qty Frame BPJS', 'rgba(0, 166, 90, 0.7)');
    createChart('chart-frame-umum-om1', umumOm1Labels, umumOm1Data, 'Qty Frame Umum OM1', 'rgba(60, 141, 188, 0.7)');
    createChart('chart-frame-umum-om2', umumOm2Labels, umumOm2Data, 'Qty Frame Umum OM2', 'rgba(243, 156, 18, 0.7)');
</script>
@endpush

@endsection
