<div class="modal fade" id="modal-lenses" tabindex="-1" role="dialog" aria-labelledby="modal-lenses-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-lenses-label">Pilih Lensa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="padding: 0;">
                    <div style="padding: 12px;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" id="search-lensa-stok" placeholder="Cari lensa...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="btn-group pull-right">
                                    <button type="button" class="btn btn-sm btn-info" id="refresh-lensa-stok">
                                        <i class="fa fa-refresh"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="table-lenses-stok" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                        <th>Type</th>
                                        <th>Index</th>
                                        <th>Coating</th>
                                        <th>CLY</th>
                                        <th>Catatan (ADD)</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                                    <!-- Data akan dimuat via AJAX -->
                        </tbody>
                    </table>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div> 