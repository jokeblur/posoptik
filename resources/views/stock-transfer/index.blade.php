@extends('layouts.master')

@section('title', 'Transfer Stok Antar Cabang')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Transfer Stok</h3>
                <div class="box-tools pull-right">
                    @if(auth()->user()->isKasir())
                        <a href="{{ route('stock-transfer.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Buat Transfer Baru
                        </a>
                    @endif
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('stock-transfer.export') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-download"></i> Export
                        </a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Transfer</th>
                                <th>Dari Cabang</th>
                                <th>Ke Cabang</th>
                                <th>Diminta Oleh</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfers as $transfer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $transfer->kode_transfer }}</strong>
                                        @if($transfer->notes)
                                            <br><small class="text-muted">{{ Str::limit($transfer->notes, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $transfer->fromBranch->name }}</td>
                                    <td>{{ $transfer->toBranch->name }}</td>
                                    <td>{{ $transfer->requestedBy->name }}</td>
                                    <td>
                                        @switch($transfer->status)
                                            @case('Pending')
                                                <span class="label label-warning">Menunggu Persetujuan</span>
                                                @break
                                            @case('Approved')
                                                <span class="label label-success">Disetujui</span>
                                                @break
                                            @case('Rejected')
                                                <span class="label label-danger">Ditolak</span>
                                                @break
                                            @case('Completed')
                                                <span class="label label-info">Selesai</span>
                                                @break
                                            @case('Cancelled')
                                                <span class="label label-default">Dibatalkan</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        {{ $transfer->created_at->format('d/m/Y H:i') }}
                                        @if($transfer->approved_at)
                                            <br><small class="text-muted">Disetujui: {{ $transfer->approved_at->format('d/m/Y H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('stock-transfer.show', $transfer->id) }}" 
                                               class="btn btn-xs btn-info btn-flat">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            
                                            @if($transfer->canBeApprovedBy(auth()->user()))
                                                <button type="button" class="btn btn-xs btn-success btn-flat" 
                                                        onclick="approveTransfer({{ $transfer->id }})">
                                                    <i class="fa fa-check"></i> Setujui
                                                </button>
                                                <button type="button" class="btn btn-xs btn-danger btn-flat" 
                                                        onclick="rejectTransfer({{ $transfer->id }})">
                                                    <i class="fa fa-times"></i> Tolak
                                                </button>
                                            @endif
                                            
                                            @if($transfer->canBeCompletedBy(auth()->user()))
                                                <button type="button" class="btn btn-xs btn-primary btn-flat" 
                                                        onclick="completeTransfer({{ $transfer->id }})">
                                                    <i class="fa fa-check-circle"></i> Selesai
                                                </button>
                                            @endif
                                            
                                            @if($transfer->status === 'Pending' && 
                                                 (auth()->user()->id === $transfer->requested_by || 
                                                  auth()->user()->isAdmin() || 
                                                  auth()->user()->isSuperAdmin()))
                                                <button type="button" class="btn btn-xs btn-warning btn-flat" 
                                                        onclick="cancelTransfer({{ $transfer->id }})">
                                                    <i class="fa fa-ban"></i> Batal
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data transfer stok</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-center">
                    {{ $transfers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Tolak Transfer Stok</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Alasan Penolakan</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" 
                                  rows="3" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function approveTransfer(id) {
    if (confirm('Apakah Anda yakin ingin menyetujui transfer ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('stock-transfer') }}/${id}/approve`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectTransfer(id) {
    $('#rejectForm').attr('action', `{{ url('stock-transfer') }}/${id}/reject`);
    $('#rejectModal').modal('show');
}

function completeTransfer(id) {
    if (confirm('Apakah Anda yakin ingin menyelesaikan transfer ini? Stok akan dipindahkan.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('stock-transfer') }}/${id}/complete`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelTransfer(id) {
    if (confirm('Apakah Anda yakin ingin membatalkan transfer ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('stock-transfer') }}/${id}/cancel`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
