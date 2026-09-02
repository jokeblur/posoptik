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
<style>
    .print-only {
        display: none;
    }

    @media print {
        @page {
            size: portrait;
            margin: 10mm;
        }

        .main-header,
        .main-sidebar,
        .content-header,
        .control-sidebar,
        .content-wrapper > .content > .row.mb-3:first-of-type form,
        .no-print {
            display: none !important;
        }

        .content-wrapper {
            margin-left: 0 !important;
        }

        .print-only {
            display: block !important;
            text-align: center;
            margin-bottom: 15px;
        }

        .print-only h2 {
            margin: 0;
            font-size: 18px;
        }

        .print-only p {
            margin: 2px 0 0;
            font-size: 12px;
        }

        body, .box {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .box {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        .row.mb-3 {
            page-break-inside: avoid;
        }

        canvas {
            max-width: 100% !important;
        }

        .table-responsive {
            overflow: visible !important;
        }
    }
</style>

<div class="print-only">
    <h2>Analisa Frame - Optik Melati</h2>
    <p>Periode: {{ $frameAnalysisPeriodLabel ?? '30 Hari Terakhir' }}</p>
</div>

<div class="row mb-3 no-print">
    <div class="col-md-12">
        <a href="{{ route('frame.index') }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Kembali ke Data Frame
        </a>
        <button type="button" onclick="window.print()" class="btn btn-success" style="margin-left:10px;">
            <i class="fa fa-print"></i> Cetak Analisa
        </button>
        <form method="GET" action="{{ route('frame.analysis') }}" class="form-inline" style="display:inline-block; margin-left:10px;">
            <div class="form-group">
                <label for="month" style="margin-right:8px;">Pilih Bulan</label>
                <input type="month" class="form-control" id="month" name="month" value="{{ $selectedMonth ?? now()->format('Y-m') }}">
            </div>
            <div class="form-group" style="margin-left:10px;">
                <label for="sales_id" style="margin-right:8px;">Nama Sales</label>
                <select class="form-control" id="sales_id" name="sales_id">
                    <option value="">Semua Sales</option>
                    @foreach($sales as $salesId => $salesName)
                        <option value="{{ $salesId }}" {{ (string) ($selectedSalesId ?? '') === (string) $salesId ? 'selected' : '' }}>
                            {{ $salesName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </form>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
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
                    <div class="col-md-4">
                        <div class="small-box bg-aqua" style="margin-bottom: 0; min-height: 120px;">
                            <div class="inner">
                                <h3>{{ number_format($frameAnalysisBpjsSummary->total_qty ?? 0) }}</h3>
                                <p>Total Qty Terjual</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-blue" style="margin-bottom: 0; min-height: 120px;">
                            <div class="inner">
                                <h3>{{ number_format($frameAnalysisBpjsSummary->total_transaksi ?? 0) }}</h3>
                                <p>Total Transaksi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-teal" style="margin-bottom: 0; min-height: 120px; cursor: pointer;"
                             data-toggle="modal"
                             data-target="#modal-bpjs-unique-patients">
                            <div class="inner">
                                <h3>{{ number_format($frameAnalysisBpjsSummary->total_pasien_unik ?? 0) }}</h3>
                                <p>Pasien BPJS Unik Beli Frame</p>
                            </div>
                            <div class="small-box-footer" style="background: rgba(0,0,0,0.1); padding: 3px 10px; font-size: 12px;">
                                Klik untuk lihat detail pasien dan frame
                            </div>
                        </div>
                    </div>
                </div>
                @if($frameAnalysisBpjs->count() > 0)
                    <div class="table-responsive">
                        <table id="table-frame-analysis-bpjs" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Merk</th>
                                    <th>Jenis</th>
                                    <th>Sales</th>
                                    <th>Qty</th>
                                    <th>Transaksi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frameAnalysisBpjs as $item)
                                    <tr>
                                        <td>{{ $item->merk_frame }}</td>
                                        <td>{{ $item->jenis_frame }}</td>
                                        <td>{{ $item->sales_name ?? '-' }}</td>
                                        <td>{{ number_format($item->total_qty) }}</td>
                                        <td>{{ number_format($item->total_transaksi) }}</td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-xs btn-info btn-flat btn-show-frame-codes"
                                                data-toggle="modal"
                                                data-target="#modal-frame-codes"
                                                data-judul="Kode Frame Terjual - BPJS"
                                                data-kode-detail='@json(($item->kode_frame_details ?? collect())->values())'
                                            >
                                                <i class="fa fa-eye"></i> Lihat Kode
                                            </button>
                                        </td>
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
</div>

<div class="row mb-3">
    <div class="col-md-12">
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
                    @php
                        $om1Summary = $frameAnalysisUmumSummaryByBranch->get('Optik Melati 1');
                        $om2Summary = $frameAnalysisUmumSummaryByBranch->get('Optik Melati 2');
                    @endphp
                    <div class="col-md-6">
                        <div class="panel panel-default" style="margin-bottom: 0;">
                            <div class="panel-heading"><strong>Optik Melati 1</strong></div>
                            <div class="panel-body">
                                <div class="small-box bg-green" style="margin-bottom: 15px; min-height: 120px;">
                                    <div class="inner">
                                        <h3>{{ number_format($om1Summary->total_qty ?? 0) }}</h3>
                                        <p>Total Qty Terjual</p>
                                    </div>
                                </div>
                                <div class="small-box bg-olive" style="margin-bottom: 0; min-height: 120px;">
                                    <div class="inner">
                                        <h3>{{ number_format($om1Summary->total_transaksi ?? 0) }}</h3>
                                        <p>Total Transaksi</p>
                                    </div>
                                </div>
                                <div class="small-box bg-teal" style="margin-top: 15px; margin-bottom: 0; min-height: 120px;">
                                    <div class="inner">
                                        <h3>{{ number_format($om1Summary->total_pasien_unik ?? 0) }}</h3>
                                        <p>Pasien Umum Unik Beli Frame Umum</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default" style="margin-bottom: 0;">
                            <div class="panel-heading"><strong>Optik Melati 2</strong></div>
                            <div class="panel-body">
                                <div class="small-box bg-green" style="margin-bottom: 15px; min-height: 120px;">
                                    <div class="inner">
                                        <h3>{{ number_format($om2Summary->total_qty ?? 0) }}</h3>
                                        <p>Total Qty Terjual</p>
                                    </div>
                                </div>
                                <div class="small-box bg-olive" style="margin-bottom: 0; min-height: 120px;">
                                    <div class="inner">
                                        <h3>{{ number_format($om2Summary->total_transaksi ?? 0) }}</h3>
                                        <p>Total Transaksi</p>
                                    </div>
                                </div>
                                <div class="small-box bg-teal" style="margin-top: 15px; margin-bottom: 0; min-height: 120px;">
                                    <div class="inner">
                                        <h3>{{ number_format($om2Summary->total_pasien_unik ?? 0) }}</h3>
                                        <p>Pasien Umum Unik Beli Frame Umum</p>
                                    </div>
                                </div>
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
                                        <table id="table-frame-analysis-umum-om1" class="table table-bordered table-striped" style="margin-bottom: 0;">
                                            <thead>
                                                <tr>
                                                    <th>Merk</th>
                                                    <th>Jenis</th>
                                                    <th>Sales</th>
                                                    <th>Qty</th>
                                                    <th>Transaksi</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($frameAnalysisUmum->where('cabang', 'Optik Melati 1') as $item)
                                                    <tr>
                                                        <td>{{ $item->merk_frame }}</td>
                                                        <td>{{ $item->jenis_frame }}</td>
                                                        <td>{{ $item->sales_name ?? '-' }}</td>
                                                        <td>{{ number_format($item->total_qty) }}</td>
                                                        <td>{{ number_format($item->total_transaksi) }}</td>
                                                        <td>
                                                            <button
                                                                type="button"
                                                                class="btn btn-xs btn-info btn-flat btn-show-frame-codes"
                                                                data-toggle="modal"
                                                                data-target="#modal-frame-codes"
                                                                data-judul="Kode Frame Terjual - Umum Optik Melati 1"
                                                                data-kode-detail='@json(($item->kode_frame_details ?? collect())->values())'
                                                            >
                                                                <i class="fa fa-eye"></i> Lihat Kode
                                                            </button>
                                                        </td>
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
                                        <table id="table-frame-analysis-umum-om2" class="table table-bordered table-striped" style="margin-bottom: 0;">
                                            <thead>
                                                <tr>
                                                    <th>Merk</th>
                                                    <th>Jenis</th>
                                                    <th>Sales</th>
                                                    <th>Qty</th>
                                                    <th>Transaksi</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($frameAnalysisUmum->where('cabang', 'Optik Melati 2') as $item)
                                                    <tr>
                                                        <td>{{ $item->merk_frame }}</td>
                                                        <td>{{ $item->jenis_frame }}</td>
                                                        <td>{{ $item->sales_name ?? '-' }}</td>
                                                        <td>{{ number_format($item->total_qty) }}</td>
                                                        <td>{{ number_format($item->total_transaksi) }}</td>
                                                        <td>
                                                            <button
                                                                type="button"
                                                                class="btn btn-xs btn-info btn-flat btn-show-frame-codes"
                                                                data-toggle="modal"
                                                                data-target="#modal-frame-codes"
                                                                data-judul="Kode Frame Terjual - Umum Optik Melati 2"
                                                                data-kode-detail='@json(($item->kode_frame_details ?? collect())->values())'
                                                            >
                                                                <i class="fa fa-eye"></i> Lihat Kode
                                                            </button>
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

<div class="modal fade" id="modal-bpjs-unique-patients" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Detail Pasien BPJS Unik Beli Frame ({{ $frameAnalysisPeriodLabel ?? 'Periode Aktif' }})</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-bpjs-unique-patients" class="table table-bordered table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>ID Pasien</th>
                                <th>Nama Pasien</th>
                                <th>Tipe Layanan</th>
                                <th class="text-right">Transaksi Frame</th>
                                <th class="text-right">Qty Frame</th>
                                <th>Merk Frame</th>
                                <th>Kode Frame</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($bpjsUniquePatientsFrameDetails ?? collect()) as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->pasien_id ?? '-' }}</td>
                                    <td>{{ $item->nama_pasien ?? '-' }}</td>
                                    <td>{{ $item->service_type ?? '-' }}</td>
                                    <td class="text-right">{{ number_format((int) ($item->total_transaksi_frame ?? 0)) }}</td>
                                    <td class="text-right">{{ number_format((int) ($item->total_qty_frame ?? 0)) }}</td>
                                    <td>{{ $item->merk_frames ?? '-' }}</td>
                                    <td>{{ $item->kode_frames ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada data pasien BPJS unik yang membeli frame pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-frame-codes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 980px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal-frame-codes-title">Kode Frame Terjual</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Kode Frame</th>
                                <th>Merk Frame</th>
                                <th>Sales</th>
                                <th style="width: 90px;" class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="modal-frame-codes-list"></tbody>
                    </table>
                </div>
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

    const initAnalysisTable = (selector, orderColumnIndex) => {
        if (!$(selector).length || $.fn.dataTable.isDataTable(selector)) {
            return;
        }

        $(selector).DataTable({
            responsive: true,
            pageLength: 10,
            order: [[orderColumnIndex, 'asc']],
            language: {
                url: window.DATATABLES_LANG_URL || '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });
    };

    initAnalysisTable('#table-frame-analysis-bpjs', 2);
    initAnalysisTable('#table-frame-analysis-umum-om1', 2);
    initAnalysisTable('#table-frame-analysis-umum-om2', 2);
    initAnalysisTable('#table-bpjs-unique-patients', 2);

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');

    $(document).on('click', '.btn-show-frame-codes', function () {
        const title = $(this).data('judul') || 'Kode Frame Terjual';
        const rawCodeDetails = $(this).attr('data-kode-detail');
        let codeDetails = [];

        try {
            codeDetails = JSON.parse(rawCodeDetails || '[]');
        } catch (error) {
            codeDetails = [];
        }

        $('#modal-frame-codes-title').text(title);

        const listElement = $('#modal-frame-codes-list');
        listElement.empty();

        if (!codeDetails.length) {
            listElement.append('<tr><td colspan="5" class="text-center text-muted">Tidak ada kode frame terjual pada data ini.</td></tr>');
            return;
        }

        codeDetails.forEach(function (item, index) {
            const code = item && item.kode_frame ? item.kode_frame : '-';
            const merk = item && item.merk_frame ? item.merk_frame : '-';
            const sales = item && item.sales_name ? item.sales_name : '-';
            const qty = item && typeof item.total_qty !== 'undefined' ? parseInt(item.total_qty, 10) : 0;

            listElement.append(
                '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + escapeHtml(code) + '</td>' +
                    '<td>' + escapeHtml(merk) + '</td>' +
                    '<td>' + escapeHtml(sales) + '</td>' +
                    '<td class="text-right">' + (isNaN(qty) ? 0 : qty) + '</td>' +
                '</tr>'
            );
        });
    });
</script>
@endpush

@endsection
