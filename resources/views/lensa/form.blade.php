<!-- Modal -->
<div class="modal fade" id="modal-form" tabindex="-1" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-3"></h1>
                </div>
                <div class="modal-body">
                    <div class="form-group" style="display: none;">
                        <label for="kode_lensa" class="col-sm-2 control-label">Kode Lensa</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="kode_lensa" id="kode_lensa" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="merk_lensa" class="col-sm-2 control-label">Merk Lensa</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="merk_lensa" id="merk_lensa" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="type" class="col-sm-2 control-label">Type</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="type" id="type">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="index" class="col-sm-2 control-label">Index</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="index" id="index">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add" class="col-sm-2 control-label">add</label>
                        <div class="col-sm-10">
                            <input type="text" name="add" class="form-control" id="add"></input>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="coating" class="col-sm-2 control-label">Coating</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="coating" id="coating">
                        </div>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                    <div class="form-group">
                        <label for="harga_beli_lensa" class="col-sm-2 control-label">Harga Beli</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="harga_beli_lensa" id="harga_beli_lensa" required>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="harga_jual_lensa" class="col-sm-2 control-label">Harga Jual</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="harga_jual_lensa" id="harga_jual_lensa" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="branch_id" class="col-sm-2 control-label">Cabang</label>
                        <div class="col-sm-10">
                            <select name="branch_id" class="form-control" required>
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $key => $item)
                                  <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="stok" class="col-sm-2 control-label">Stok</label>
                        <div class="col-sm-10">
                            <input type="number" name="stok" class="form-control" id="stok" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="is_custom_order" class="col-sm-2 control-label">Tipe Stok</label>
                        <div class="col-sm-10">
                            <select name="is_custom_order" class="form-control" id="is_custom_order">
                                <option value="0">Ready Stock</option>
                                <option value="1">Custom Order</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sales_id" class="col-sm-2 control-label">Sales</label>
                        <div class="col-sm-10">
                            <select name="sales_id" class="form-control" id="sales_id">
                                <option value="">-- Pilih Sales (Optional) --</option>
                                @foreach($sales as $key => $item)
                                  <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                     <button class="btn btn-sm btn-custom">Simpan</button>
                    <button type="button" class="btn btn-sm btn-custom-close" data-dismiss="modal">Batal</button>
                   
                </div>
            </div>
        </form>
    </div>
</div>
