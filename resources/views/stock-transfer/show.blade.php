@extends('layouts.master')

@section('title', 'Detail Transfer Stok')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Detail Transfer Stok #{{ $transfer->kode_transfer }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('stock-transfer.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
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

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Kode Transfer</strong></td>
                                <td>: {{ $transfer->kode_transfer }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>: 
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
                            </tr>
                            <tr>
                                <td><strong>Dari Cabang</strong></td>
                                <td>: {{ $transfer->fromBranch->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ke Cabang</strong></td>
                                <td>: {{ $transfer->toBranch->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Diminta Oleh</strong></td>
                                <td>: {{ $transfer->requestedBy->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Permintaan</strong></td>
                                <td>: {{ $transfer->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            @if($transfer->approved_by)
                                <tr>
                                    <td width="150"><strong>Disetujui Oleh</strong></td>
                                    <td>: {{ $transfer->approvedBy->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Persetujuan</strong></td>
                                    <td>: {{ $transfer->approved_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endif
                            @if($transfer->status === 'Rejected')
                                <tr>
                                    <td><strong>Alasan Penolakan</strong></td>
                                    <td>: {{ $transfer->rejection_reason }}</td>
                                </tr>
                            @endif
                            @if($transfer->status === 'Completed')
                                <tr>
                                    <td><strong>Tanggal Selesai</strong></td>
                                    <td>: {{ $transfer->completed_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endif
                            @if($transfer->notes)
                                <tr>
                                    <td><strong>Catatan</strong></td>
                                    <td>: {{ $transfer->notes }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <hr>

                <h4>Detail Produk yang Ditransfer</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfer->details as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($detail->itemable_type === 'App\Models\Frame')
                                            <span class="label label-primary">Frame</span>
                                        @else
                                            <span class="label label-success">Lensa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($detail->itemable_type === 'App\Models\Frame')
                                            {{ $detail->itemable->kode_frame }}
                                        @else
                                            {{ $detail->itemable->kode_lensa }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($detail->itemable_type === 'App\Models\Frame')
                                            {{ $detail->itemable->merk_frame }}
                                        @else
                                            {{ $detail->itemable->merk_lensa }}
                                        @endif
                                    </td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($detail->total_price, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada detail produk</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total Nilai Transfer:</th>
                                <th>Rp {{ number_format($transfer->details->sum('total_price'), 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h4>Aksi</h4>
                        <div class="btn-group">
                            @if($transfer->canBeApprovedBy(auth()->user()))
                                <button type="button" class="btn btn-success" onclick="approveTransfer()">
                                    <i class="fa fa-check"></i> Setujui Transfer
                                </button>
                                <button type="button" class="btn btn-danger" onclick="rejectTransfer()">
                                    <i class="fa fa-times"></i> Tolak Transfer
                                </button>
                            @endif
                            
                            @if($transfer->canBeCompletedBy(auth()->user()))
                                <button type="button" class="btn btn-primary" onclick="completeTransfer()">
                                    <i class="fa fa-check-circle"></i> Selesaikan Transfer
                                </button>
                            @endif
                            
                            @if($transfer->status === 'Pending' && 
                                 (auth()->user()->id === $transfer->requested_by || 
                                  auth()->user()->isAdmin() || 
                                  auth()->user()->isSuperAdmin()))
                                <button type="button" class="btn btn-warning" onclick="cancelTransfer()">
                                    <i class="fa fa-ban"></i> Batalkan Transfer
                                </button>
                            @endif
                        </div>
                    </div>
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
function approveTransfer() {
    if (confirm('Apakah Anda yakin ingin menyetujui transfer ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("stock-transfer.approve", $transfer->id) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectTransfer() {
    $('#rejectForm').attr('action', '{{ route("stock-transfer.reject", $transfer->id) }}');
    $('#rejectModal').modal('show');
}

function completeTransfer() {
    if (confirm('Apakah Anda yakin ingin menyelesaikan transfer ini? Stok akan dipindahkan.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("stock-transfer.complete", $transfer->id) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelTransfer() {
    if (confirm('Apakah Anda yakin ingin membatalkan transfer ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("stock-transfer.cancel", $transfer->id) }}';
        
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
