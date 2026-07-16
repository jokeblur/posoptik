@extends('layouts.master')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Filter Laporan Stok Frame & Lensa</h3>
            </div>
            <div class="box-body">
                <form method="GET" action="{{ route('laporan.profit-loss') }}" class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cabang</label>
                            <select name="branch_id" class="form-control">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Tampilkan</button>
                                <a href="{{ route('laporan.profit-loss') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ number_format($frameStats->total_item, 0, ',', '.') }}</h3>
                <p>Jumlah Item Frame Tersedia</p>
            </div>
            <div class="icon"><i class="fa fa-eye"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ number_format($lensaStats->total_item, 0, ',', '.') }}</h3>
                <p>Jumlah Item Lensa Tersedia</p>
            </div>
            <div class="icon"><i class="fa fa-circle-o"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>{{ number_format($totalQty, 0, ',', '.') }}</h3>
                <p>Total Qty Stok</p>
            </div>
            <div class="icon"><i class="fa fa-money"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box {{ $totalSelisih >= 0 ? 'bg-olive' : 'bg-red' }}">
            <div class="inner">
                <h3>Rp {{ number_format($totalJual, 0, ',', '.') }}</h3>
                <p>Total Nilai Harga Jual Stok</p>
            </div>
            <div class="icon"><i class="fa fa-line-chart"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Rekap Frame</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <th>Jumlah Item Frame Tersedia</th>
                        <td>{{ number_format($frameStats->total_item, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Qty Stok</th>
                        <td>{{ number_format($frameStats->total_qty, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Nilai Harga Jual Stok</th>
                        <td>Rp {{ number_format($frameStats->total_jual, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Nilai Harga Beli Stok</th>
                        <td>Rp {{ number_format($frameStats->total_beli, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Selisih Nilai (Jual - Beli)</th>
                        <td>
                            @php $frameProfit = $frameStats->total_jual - $frameStats->total_beli; @endphp
                            <span class="label {{ $frameProfit >= 0 ? 'label-success' : 'label-danger' }}">
                                Rp {{ number_format($frameProfit, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Rekap Lensa</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <th>Jumlah Item Lensa Tersedia</th>
                        <td>{{ number_format($lensaStats->total_item, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Qty Stok</th>
                        <td>{{ number_format($lensaStats->total_qty, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Nilai Harga Jual Stok</th>
                        <td>Rp {{ number_format($lensaStats->total_jual, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Nilai Harga Beli Stok</th>
                        <td>Rp {{ number_format($lensaStats->total_beli, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Selisih Nilai (Jual - Beli)</th>
                        <td>
                            @php $lensaProfit = $lensaStats->total_jual - $lensaStats->total_beli; @endphp
                            <span class="label {{ $lensaProfit >= 0 ? 'label-success' : 'label-danger' }}">
                                Rp {{ number_format($lensaProfit, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Ringkasan Stok Per Cabang</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="profit-loss-table">
                    <thead>
                        <tr>
                            <th>Cabang</th>
                            <th>Item Frame</th>
                            <th>Qty Frame</th>
                            <th>Item Lensa</th>
                            <th>Qty Lensa</th>
                            <th>Total Qty</th>
                            <th>Total Nilai Jual</th>
                            <th>Total Nilai Beli</th>
                            <th>Selisih Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryPerBranch as $row)
                            <tr>
                                <td>{{ $row->branch_name }}</td>
                                <td>{{ number_format($row->frame_item, 0, ',', '.') }}</td>
                                <td>{{ number_format($row->frame_qty, 0, ',', '.') }}</td>
                                <td>{{ number_format($row->lensa_item, 0, ',', '.') }}</td>
                                <td>{{ number_format($row->lensa_qty, 0, ',', '.') }}</td>
                                <td>{{ number_format($row->total_qty, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->total_jual, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->total_beli, 0, ',', '.') }}</td>
                                <td>
                                    <span class="label {{ $row->selisih >= 0 ? 'label-success' : 'label-danger' }}">
                                        Rp {{ number_format($row->selisih, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada stok tersedia pada filter ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right">TOTAL</th>
                            <th>{{ number_format($frameStats->total_item, 0, ',', '.') }}</th>
                            <th>{{ number_format($frameStats->total_qty, 0, ',', '.') }}</th>
                            <th>{{ number_format($lensaStats->total_item, 0, ',', '.') }}</th>
                            <th>{{ number_format($lensaStats->total_qty, 0, ',', '.') }}</th>
                            <th>{{ number_format($totalQty, 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($totalJual, 0, ',', '.') }}</th>
                            <th>Rp {{ number_format($totalBeli, 0, ',', '.') }}</th>
                            <th>
                                <span class="label {{ $totalSelisih >= 0 ? 'label-success' : 'label-danger' }}">
                                    Rp {{ number_format($totalSelisih, 0, ',', '.') }}
                                </span>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#profit-loss-table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false
    });
});
</script>
@endpush
