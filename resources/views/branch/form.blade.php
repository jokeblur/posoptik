<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="name" class="col-lg-2 col-lg-offset-1 control-label">Nama Cabang</label>
                        <div class="col-lg-6">
                            <input type="text" name="name" id="name" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="code" class="col-lg-2 col-lg-offset-1 control-label">Kode Cabang</label>
                        <div class="col-lg-6">
                            <input type="text" name="code" id="code" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="address" class="col-lg-2 col-lg-offset-1 control-label">Alamat</label>
                        <div class="col-lg-6">
                            <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="phone" class="col-lg-2 col-lg-offset-1 control-label">Telepon</label>
                        <div class="col-lg-6">
                            <input type="text" name="phone" id="phone" class="form-control">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="email" class="col-lg-2 col-lg-offset-1 control-label">Email</label>
                        <div class="col-lg-6">
                            <input type="email" name="email" id="email" class="form-control">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="manager_id" class="col-lg-2 col-lg-offset-1 control-label">Manager</label>
                        <div class="col-lg-6">
                            <select name="manager_id" id="manager_id" class="form-control">
                                <option value="">Pilih Manager</option>
                                <!-- Options will be populated via AJAX -->
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-6 col-lg-offset-3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_active" value="1" checked> Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-custom"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-custom-close" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>
