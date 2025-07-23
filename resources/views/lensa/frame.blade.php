<div id="modalLensa" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Tambah Lensa</h4>
            </div>
            <div class="modal-body">
                <form id="formLensa">
                    @csrf
                    <div class="form-group">
                        <label>Merk Lensa</label>
                        <input type="text" class="form-control" name="merk_lensa" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Beli</label>
                        <input type="number" class="form-control" name="harga_beli" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual</label>
                        <input type="number" class="form-control" name="harga_jual" required>
                    </div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>