@extends('layouts.master')

@section('title', 'Daftar Penjualan')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="{{ route('penjualan.create') }}" class="btn btn-primary">Tambah Penjualan Baru</a>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="penjualan-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Kode Penjualan</th>
                            <th>Total</th>
                            <th>Kasir</th>
                            <th>Cabang</th>
                            <th>Passet Oleh</th>
                            <th>Status Pengerjaan</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data akan diisi oleh DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table; // Deklarasikan di sini agar bisa diakses secara global di dalam script

    $(function () {
        table = $('#penjualan-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('penjualan.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'kode_penjualan', name: 'kode_penjualan' },
                { data: 'total_harga', name: 'total_harga' },
                { data: 'kasir', name: 'kasir' },
                { data: 'cabang', name: 'cabang' },
                { data: 'passet_by', name: 'passet_by' },
                { data: 'status_pengerjaan', name: 'status_pengerjaan' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ]
        });
    });

    function tandaiDiambil(url) {
        Swal.fire({
            title: 'Konfirmasi Pengambilan',
            text: "Anda yakin barang sudah diambil oleh pelanggan?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, sudah diambil!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url, { '_token': '{{ csrf_token() }}' })
                    .done(response => {
                        Swal.fire('Berhasil!', response.message, 'success')
                            .then(() => {
                                table.ajax.reload(); // Reload setelah user menutup alert
                            });
                    })
                    .fail(errors => {
                        let message = 'Tidak dapat mengubah status.';
                        if (errors.responseJSON && errors.responseJSON.message) {
                            message = errors.responseJSON.message;
                        }
                        Swal.fire('Gagal!', message, 'error');
                    });
            }
        });
    }
</script>
@endpush 