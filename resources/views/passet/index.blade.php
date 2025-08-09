@extends('layouts.master')

@section('title', 'Daftar Pengerjaan Passet')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Pekerjaan yang Perlu Diselesaikan</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="passet-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tanggal Masuk</th>
                            <th>Kode Penjualan</th>
                            <th>Nama Pasien</th>
                            <th>Cabang</th>
                            <th>Status Pengerjaan</th>
                            <th width="15%"><i class="fa fa-cog"></i></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih User Passet -->
<div class="modal fade" id="modal-user-passet" tabindex="-1" role="dialog" aria-labelledby="modalUserPassetLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalUserPassetLabel">Pilih User Passet</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-user-passet">
          <div class="form-group">
            <label for="user_id">User Passet</label>
            <select class="form-control" id="user_id" name="user_id" required>
              <option value="">Pilih User</option>
              @foreach(\App\Models\User::where('role', \App\Models\User::ROLE_PASSET_BANTU)->get() as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </div>
          <input type="hidden" id="modal-url-passet" value="">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-user-passet">Tandai Selesai</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    let table;
    $(function() {
        table = $('#passet-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('passet.data') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'kode_penjualan', name: 'kode_penjualan' },
                { data: 'pasien_name', name: 'pasien_name' },
                { data: 'cabang_name', name: 'cabang_name' },
                { data: 'status_pengerjaan', name: 'status_pengerjaan' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ]
        });
    });

    function markAsSelesai(url) {
        // Cek role user dari backend (dengan variable blade)
        var isAdmin = @json(auth()->user()->role === \App\Models\User::ROLE_ADMIN || auth()->user()->role === \App\Models\User::ROLE_SUPER_ADMIN);
        if (isAdmin) {
            // Tampilkan modal pilih user passet
            $('#modal-url-passet').val(url);
            $('#modal-user-passet').modal('show');
        } else {
            if (confirm('Anda yakin pengerjaan untuk item ini sudah selesai?')) {
                $.post(url, { '_token': '{{ csrf_token() }}' })
                    .done(response => {
                        table.ajax.reload();
                        Swal.fire('Berhasil!', response.message, 'success');
                    })
                    .fail(errors => {
                        Swal.fire('Gagal!', 'Tidak dapat mengubah status.', 'error');
                    });
            }
        }
    }

    // Submit dari modal user passet
    $('#btn-submit-user-passet').on('click', function() {
        var url = $('#modal-url-passet').val();
        var user_id = $('#user_id').val();
        if (!user_id) {
            alert('Pilih user passet terlebih dahulu!');
            return;
        }
        $.post(url, { '_token': '{{ csrf_token() }}', 'user_id': user_id })
            .done(response => {
                $('#modal-user-passet').modal('hide');
                table.ajax.reload();
                Swal.fire('Berhasil!', response.message, 'success');
            })
            .fail(errors => {
                Swal.fire('Gagal!', 'Tidak dapat mengubah status.', 'error');
            });
    });
</script>
@endpush 