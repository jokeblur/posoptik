

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
                    <div class="form-group">
                        <label for="nama_dokter" class="col-sm-2 control-label">Nama dokter</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control input-bulat" name="nama_dokter" id="nama_dokter">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="alamat" class="col-sm-2 control-label">Alamat</label>
                        <div class="col-sm-10">
                            <input type="text" name="alamat" class="form-control" id="alamat">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nohp" class="col-sm-2 control-label">Contact Person</label>
                        <div class="col-sm-10">
                            <input type="text" name="nohp" class="form-control" id="nohp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>