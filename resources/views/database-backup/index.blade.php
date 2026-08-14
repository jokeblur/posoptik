@extends('layouts.master')

@section('title', 'Backup Database')

@section('content')
<div class="container-fluid" style="margin-top: 30px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-database"></i> Download Backup Database</h3>
                </div>
                <div class="box-body">
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="alert alert-info" style="margin-bottom: 20px;">
                        <strong>Info koneksi aktif:</strong><br>
                        Database: {{ $databaseName }}<br>
                        Host: {{ $databaseHost }}:{{ $databasePort }}<br>
                        Connection: {{ $connectionName }}
                    </div>

                    <p>Menu ini membuat file dump `.sql` dari database aktif aplikasi, agar bisa langsung diimport ke database lokal Anda.</p>
                    <p>Backup menggunakan `mysqldump`, jadi pastikan binary MySQL tersedia di server aplikasi.</p>

                    <form method="POST" action="{{ route('database-backup.download') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-download"></i> Download Backup Database
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-default btn-lg">
                            Kembali
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection