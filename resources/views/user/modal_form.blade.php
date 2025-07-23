<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog" role="document">
        <form action="" method="post" class="form-horizontal">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="col-md-3 control-label">Nama</label>
                        <div class="col-md-8">
                            <input type="text" name="name" id="name" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-md-3 control-label">Email</label>
                        <div class="col-md-8">
                            <input type="email" name="email" id="email" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="password" class="col-md-3 control-label">Password</label>
                        <div class="col-md-8">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="role" class="col-md-3 control-label">Role</label>
                        <div class="col-md-8">
                            <select name="role" id="role" class="form-control" required>
                                <option value="">Pilih Role</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="branch_id" class="col-md-3 control-label">Cabang</label>
                        <div class="col-md-8">
                            <select name="branch_id" id="branch_id" class="form-control" required>
                                <option value="">Pilih Cabang</option>
                                @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-flat">Simpan</button>
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div> 