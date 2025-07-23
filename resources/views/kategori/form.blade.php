

<!-- Modal -->
<div class="modal fade" id="modal-form" tabindex="-1" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-3"></h1>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_kategori" class="col-sm-2 control-label">Kategori</label>
                        <div class="col-sm-10">
                            <input class="form-control" name="nama_kategori" id="nama_kategori" required autofocus>
                            <span class="help-block with-errors"></span>
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