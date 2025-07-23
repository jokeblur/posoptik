<div class="modal fade" id="modal-lenses" tabindex="-1" role="dialog" aria-labelledby="modal-lenses-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-lenses-label">Pilih Lensa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped" id="table-lenses">
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
                         @foreach($lenses as $lensa)
                        <tr>
                            <td>{{ $lensa->kode_lensa }}</td>
                            <td>{{ $lensa->merk_lensa }}</td>
                            <td>{{ $lensa->stok }}</td>
                            <td>{{ format_uang($lensa->harga_jual_lensa) }}</td>
                            <td>
                                <a href="#" class="btn btn-primary btn-sm add-to-cart" 
                                   data-id="{{ $lensa->id }}" 
                                   data-name="{{ $lensa->merk_lensa }}"
                                   data-price="{{ $lensa->harga_jual_lensa }}"
                                   data-type="lensa">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 