<div class="modal fade" id="modal-aksesoris" tabindex="-1" role="dialog" aria-labelledby="modal-aksesoris-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-aksesoris-label">Pilih Aksesoris</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped" id="table-aksesoris">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($aksesoris as $aks)
                        <tr>
                            <td>{{ $aks->id }}</td>
                            <td>{{ $aks->nama_produk }}</td>
                            <td>
                                @if($aks->stok > 0)
                                    <span class="label label-success">{{ $aks->stok }}</span>
                                @else
                                    <span class="label label-danger">Habis</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($aks->harga_jual,0,',','.') }}</td>
                            <td>
                                @if($aks->stok > 0)
                                <a href="#" class="btn btn-warning btn-sm add-to-cart" 
                                   data-id="{{ $aks->id }}" 
                                   data-name="{{ $aks->nama_produk }}"
                                   data-price="{{ $aks->harga_jual }}"
                                   data-type="aksesoris">
                                    <i class="fa fa-plus"></i>
                                </a>
                                @else
                                <button class="btn btn-warning btn-sm" disabled>
                                    <i class="fa fa-ban"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>