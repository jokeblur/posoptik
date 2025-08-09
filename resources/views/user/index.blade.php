@extends('layouts.master')

@section('title', 'Manajemen User')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('user.store') }}')" class="btn btn-custom btn-sm"><i class="fa fa-plus-circle"></i> Tambah User Baru</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="user-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Cabang</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@include('user.modal_form')
@endsection

@push('scripts')
<script>
    let table;

    $(function () {
        table = $('#user-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('user.data') }}',
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'role', name: 'role'},
                {data: 'branch_name', name: 'branch_name'},
                {data: 'aksi', name: 'aksi', orderable: false, searchable: false},
            ]
        });

        $('#modal-form form').on('submit', function (e) {
            e.preventDefault();
            $('.form-group').removeClass('has-error');
            $('.help-block').text('');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#modal-form').modal('hide');
                    table.ajax.reload();
                    Swal.fire('Berhasil!', response.message, 'success');
                },
                error: function(errors) {
                    if (errors.status === 422) { // Validation error
                        Swal.fire('Gagal!', 'Periksa kembali data yang Anda masukkan.', 'error');
                        let errorResponse = errors.responseJSON.errors;
                        for (let key in errorResponse) {
                            let input = $('#modal-form [name=' + key + ']');
                            input.closest('.form-group').addClass('has-error');
                            input.closest('.form-group').find('.help-block').text(errorResponse[key][0]);
                        }
                    } else {
                        Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error');
                    }
                }
            });
        });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah User Baru');

        $('#modal-form form')[0].reset();
        $('.form-group').removeClass('has-error');
        $('.help-block').text('');

        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#password, #password_confirmation').attr('required', true);
    }

    function editForm(url_show, url_update) {
        $('#modal-form .modal-title').text('Edit User');
        $('#modal-form form')[0].reset();
        $('.form-group').removeClass('has-error');
        $('.help-block').text('');

        $('#modal-form form').attr('action', url_update);
        $('#modal-form [name=_method]').val('put');
        $('#password, #password_confirmation').attr('required', false);

        $.get(url_show)
            .done((response) => {
                $('#modal-form [name=name]').val(response.name);
                $('#modal-form [name=email]').val(response.email);
                $('#modal-form [name=role]').val(response.role);
                $('#modal-form [name=branch_id]').val(response.branch_id);
                $('#modal-form').modal('show');
            })
            .fail((errors) => {
                alert('Tidak dapat menampilkan data.');
            });
    }

    function deleteData(url) {
        if (confirm('Anda yakin ingin menghapus user ini?')) {
            $.post(url, {
                    '_token': '{{ csrf_token() }}',
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                    Swal.fire('Berhasil!', response.message, 'success');
                })
                .fail((errors) => {
                    Swal.fire('Gagal!', 'Tidak dapat menghapus user.', 'error');
                });
        }
    }
</script>
@endpush 