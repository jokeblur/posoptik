@extends('layouts.master')
@section('title', 'Open/Close Day Cabang')
@section('content')
<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">Open/Close Day Setiap Cabang ({{ $today }})</h3></div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th>Status</th>
                    <th>Waktu Open</th>
                    <th>Waktu Close</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $branch)
                @php $od = $openDays[$branch->id] ?? null; @endphp
                <tr id="row-{{ $branch->id }}">
                    <td>{{ $branch->name }}</td>
                    <td id="status-{{ $branch->id }}">
                        @if($od && $od->is_open)
                            <span class="label label-success">Buka</span>
                        @else
                            <span class="label label-danger">Tutup</span>
                        @endif
                    </td>
                    <td id="open-{{ $branch->id }}">
                        @if($od && $od->updated_at)
                            {{ $od->updated_at->format('d-m-Y H:i:s') }}
                        @else
                            -
                        @endif
                    </td>
                    <td id="close-{{ $branch->id }}">
                        @if($od && !$od->is_open && $od->updated_at)
                            {{ $od->updated_at->format('d-m-Y H:i:s') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($od && $od->is_open)
                        <form class="form-close-day" data-branch="{{ $branch->id }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                            <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-unlock"></i></button>
                        </form>
                        @else
                        <form class="form-open-day" data-branch="{{ $branch->id }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                            <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-lock"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Jakarta'
    };
    return date.toLocaleString('id-ID', options);
}

function updateRow(branchId, status, open, close) {
    $('#status-' + branchId).html(status);
    $('#open-' + branchId).text(open);
    $('#close-' + branchId).text(close);
}

$(function() {
    $(document).on('submit', '.form-open-day', function(e) {
        e.preventDefault();
        var form = $(this);
        var branchId = form.data('branch');
        $.ajax({
            url: '{{ route('open.day') }}',
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                if (res.success) {
                    Swal.fire('Berhasil', res.message, 'success');
                    updateRow(branchId, '<span class="label label-success">Buka</span>', formatDateTime(res.openDay.created_at), '-');
                    form.closest('td').html('<form class="form-close-day" data-branch="'+branchId+'" style="display:inline;">@csrf<input type="hidden" name="branch_id" value="'+branchId+'"><button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-lock"></i></button></form>');
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        });
    });
    
    $(document).on('submit', '.form-close-day', function(e) {
        e.preventDefault();
        var form = $(this);
        var branchId = form.data('branch');
        $.ajax({
            url: '{{ route('close.day') }}',
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                if (res.success) {
                    Swal.fire('Berhasil', res.message, 'success');
                    updateRow(branchId, '<span class="label label-danger">Tutup</span>', formatDateTime(res.openDay.created_at), formatDateTime(res.openDay.updated_at));
                    form.closest('td').html('<form class="form-open-day" data-branch="'+branchId+'" style="display:inline;">@csrf<input type="hidden" name="branch_id" value="'+branchId+'"><button type="submit" class="btn btn-success btn-xs"><i class="fa fa-unlock"></i></button></form>');
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        });
    });
});
</script>
@endpush 