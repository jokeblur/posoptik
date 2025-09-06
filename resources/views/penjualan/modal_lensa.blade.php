<div class="modal fade" id="modal-lenses" tabindex="-1" role="dialog" aria-labelledby="modal-lenses-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-lenses-label">Pilih Lensa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="lensaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="stok-tab" data-toggle="tab" href="#stok" role="tab" aria-controls="stok" aria-selected="true">
                            <i class="fa fa-cubes"></i> Stok (Ready Stock)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="gosok-tab" data-toggle="tab" href="#gosok" role="tab" aria-controls="gosok" aria-selected="false">
                            <i class="fa fa-edit"></i> Gosok (Input Manual)
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="lensaTabContent">
                    <!-- Stok Tab -->
                    <div class="tab-pane fade show active" id="stok" role="tabpanel" aria-labelledby="stok-tab">
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
                    
                    <!-- Gosok Tab -->
                    <div class="tab-pane fade" id="gosok" role="tabpanel" aria-labelledby="gosok-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5><i class="fa fa-edit"></i> Input Manual Lensa Gosok</h5>
                                    <p class="mb-0">Masukkan detail lensa yang akan di-gosok secara manual. Field yang bertanda <span class="text-danger">*</span> wajib diisi.</p>
                                </div>
                            </div>
                        </div>
                        
                        <form id="form-lensa-gosok">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gosok_merk">Merk Lensa <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="gosok_merk" name="merk" required 
                                               placeholder="Contoh: Essilor, Hoya, Zeiss" 
                                               style="border-radius: 5px; border: 2px solid #ddd;">
                                        <small class="help-block text-muted">Masukkan merk lensa yang akan di-gosok</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gosok_type">Type Lensa</label>
                                        <select class="form-control" id="gosok_type" name="type" style="border-radius: 5px; border: 2px solid #ddd;">
                                            <option value="">Pilih Type Lensa</option>
                                            <option value="Single Vision">Single Vision</option>
                                            <option value="Progressive">Progressive</option>
                                            <option value="Bifocal">Bifocal</option>
                                            <option value="Trifocal">Trifocal</option>
                                            <option value="Reading">Reading</option>
                                            <option value="Computer">Computer</option>
                                        </select>
                                        <small class="help-block text-muted">Pilih type lensa sesuai kebutuhan</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gosok_index">Index</label>
                                        <select class="form-control" id="gosok_index" name="index" style="border-radius: 5px; border: 2px solid #ddd;">
                                            <option value="">Pilih Index</option>
                                            <option value="1.50">1.50</option>
                                            <option value="1.56">1.56</option>
                                            <option value="1.59">1.59</option>
                                            <option value="1.60">1.60</option>
                                            <option value="1.67">1.67</option>
                                            <option value="1.70">1.70</option>
                                            <option value="1.74">1.74</option>
                                        </select>
                                        <small class="help-block text-muted">Index refraksi lensa</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gosok_coating">Coating</label>
                                        <select class="form-control" id="gosok_coating" name="coating" style="border-radius: 5px; border: 2px solid #ddd;">
                                            <option value="">Pilih Coating</option>
                                            <option value="Anti-Reflective">Anti-Reflective</option>
                                            <option value="Blue Cut">Blue Cut</option>
                                            <option value="UV Protection">UV Protection</option>
                                            <option value="Hard Coating">Hard Coating</option>
                                            <option value="Photochromic">Photochromic</option>
                                            <option value="Polarized">Polarized</option>
                                        </select>
                                        <small class="help-block text-muted">Jenis coating lensa</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gosok_cly">CLY (Diameter)</label>
                                        <input type="text" class="form-control" id="gosok_cly" name="cly" 
                                               placeholder="Contoh: 300, 275" 
                                               style="border-radius: 5px; border: 2px solid #ddd;">
                                        <small class="help-block text-muted">Diameter lensa dalam mm</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gosok_harga">Harga Jual <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon">Rp</span>
                                            <input type="number" class="form-control" id="gosok_harga" name="harga" required 
                                                   placeholder="Masukkan harga jual" min="0" step="1000"
                                                   style="border-radius: 0 5px 5px 0; border: 2px solid #ddd;">
                                        </div>
                                        <small class="help-block text-muted">Harga jual lensa gosok</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gosok_quantity">Jumlah <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-cubes"></i></span>
                                            <input type="number" class="form-control" id="gosok_quantity" name="quantity" required 
                                                   placeholder="Jumlah lensa" min="1" value="1"
                                                   style="border-radius: 0 5px 5px 0; border: 2px solid #ddd;">
                                        </div>
                                        <small class="help-block text-muted">Jumlah lensa yang akan di-gosok</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="gosok_catatan">Catatan (ADD)</label>
                                        <textarea class="form-control" id="gosok_catatan" name="catatan" rows="3" 
                                                  placeholder="Catatan tambahan untuk lensa gosok (resep dokter, spesifikasi khusus, dll)"
                                                  style="border-radius: 5px; border: 2px solid #ddd;"></textarea>
                                        <small class="help-block text-muted">Catatan khusus untuk lensa gosok</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-warning btn-block" id="btn-reset-gosok">
                                        <i class="fa fa-refresh"></i> Reset Form
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-success btn-block" id="btn-add-gosok">
                                        <i class="fa fa-plus"></i> Tambahkan ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 