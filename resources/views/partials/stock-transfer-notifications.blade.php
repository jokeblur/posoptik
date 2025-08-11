@php
    $pendingCount = App\Models\StockTransfer::getPendingCount(auth()->user());
    $attentionNeeded = App\Models\StockTransfer::getAttentionNeeded(auth()->user());
@endphp

@if($pendingCount > 0)
    <li class="dropdown notifications-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-bell-o"></i>
            <span class="label label-warning">{{ $pendingCount }}</span>
        </a>
        <ul class="dropdown-menu">
            <li class="header">Ada {{ $pendingCount }} transfer stok yang memerlukan perhatian</li>
            <li>
                <ul class="menu">
                    @foreach($attentionNeeded as $transfer)
                        <li>
                            <a href="{{ route('stock-transfer.show', $transfer->id) }}">
                                <i class="fa fa-exchange text-aqua"></i> 
                                Transfer {{ $transfer->kode_transfer }} dari {{ $transfer->fromBranch->name }} ke {{ $transfer->toBranch->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
            <li class="footer">
                <a href="{{ route('stock-transfer.index') }}">Lihat Semua</a>
            </li>
        </ul>
    </li>
@endif
