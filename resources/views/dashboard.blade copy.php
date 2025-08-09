@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="page-header">Dashboard</h1>
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
    @endif
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        {{-- Konten lain dashboard bisa diletakkan di sini --}}
    </div>
</div>
@endsection
