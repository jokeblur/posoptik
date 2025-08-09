<!-- Modal -->
<div class="modal fade" id="modal-form" tabindex="-1" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="post" class="form-horizontal" data-toggle="validator">
            @csrf
            <input type="hidden" name="_method" value="post">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Tambah Aksesoris</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_produk" class="col-sm-3 control-label">Nama Produk</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="nama_produk" id="nama_produk" required autofocus>
                        </div>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                    <div class="form-group">
                        <label for="harga_beli" class="col-sm-3 control-label">Harga Beli</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="harga_beli" id="harga_beli" required min="0">
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="harga_jual" class="col-sm-3 control-label">Harga Jual</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="harga_jual" id="harga_jual" required min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="stok" class="col-sm-3 control-label">Stok</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="stok" id="stok" required min="0">
                        </div>
                    </div>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <div class="form-group">
                        <label for="branch_id" class="col-md-3 control-label">Cabang</label>
                        <div class="col-md-6">
                            <select name="branch_id" id="branch_id" class="form-control" required>
                                <option value="">Pilih Cabang</option>
                                @foreach(\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}" {{ (isset($aksesoris) && $aksesoris->branch_id == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-custom-close" data-dismiss="modal">Batal</button>
                    <button class="btn btn-sm btn-custom">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div> 