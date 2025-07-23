<div class="modal fade" id="modal-frames" tabindex="-1" role="dialog" aria-labelledby="modal-frames-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-frames-label">Pilih Frame</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped" id="table-frames">
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
                        @foreach($frames as $frame)
                        <tr>
                            <td>{{ $frame->kode_frame }}</td>
                            <td>{{ $frame->merk_frame }}</td>
                            <td>{{ $frame->stok }}</td>
                            <td>{{ format_uang($frame->harga_jual_frame) }}</td>
                            <td>
                                <a href="#" class="btn btn-primary btn-sm add-to-cart" 
                                   data-id="{{ $frame->id }}" 
                                   data-name="{{ $frame->merk_frame }}"
                                   data-price="{{ $frame->harga_jual_frame }}"
                                   data-type="frame">
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