@extends('layouts.master')

@section('title', 'Omset Harian Kasir')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Rekap Omset Harian Kasir</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ $today }}</td>
                    </tr>
                    <tr>
                        <th>Total Omset</th>
                        <td><b>Rp {{ number_format($totalOmset,0,',','.') }}</b></td>
                    </tr>
                    <tr>
                        <th>Jumlah Transaksi</th>
                        <td>{{ $jumlahTransaksi }}</td>
                    </tr>
                </table>
                <div class="text-center" style="margin-top:20px;">
                    <button class="btn btn-success btn-lg" disabled>
                        <i class="fa fa-upload"></i> Setor Omset ke Atasan
                    </button>
                    <p class="text-muted" style="margin-top:10px;">(Fitur setor omset bisa diaktifkan sesuai kebutuhan)</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 