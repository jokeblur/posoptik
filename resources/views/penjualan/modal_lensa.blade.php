<div class="modal fade" id="modal-lenses" tabindex="-1" role="dialog" aria-labelledby="modal-lenses-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-lenses-label">Pilih Lensa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <!-- Tab Navigation -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="lensaTabs" role="tablist" style="margin-bottom: 0; border-bottom: 2px solid #3c8dbc;">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="stok-tab" data-toggle="tab" href="#stok" role="tab" aria-controls="stok" aria-selected="true" 
                               style="background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; border: none; border-radius: 0; font-weight: bold; padding: 12px 20px; margin-right: 2px;">
                                <i class="fa fa-cubes"></i> <strong>STOK READY</strong>
                                <br><small style="font-size: 11px; opacity: 0.9;">Lensa Siap Pakai</small>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="gosok-tab" data-toggle="tab" href="#gosok" role="tab" aria-controls="gosok" aria-selected="false"
                               style="background: linear-gradient(135deg, #e67e22, #f39c12); color: white; border: none; border-radius: 0; font-weight: bold; padding: 12px 20px; margin-right: 2px;">
                                <i class="fa fa-edit"></i> <strong>GOSOK MANUAL</strong>
                                <br><small style="font-size: 11px; opacity: 0.9;">Input Kustom</small>
                            </a>
                        </li>
                    </ul>
                </div>
                
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
                    <div class="tab-pane fade" id="gosok" role="tabpanel" aria-labelledby="gosok-tab" style="padding: 8px 20px 20px 20px;">
                        <form id="form-lensa-gosok">
                            <!-- Row 1: Merk & Type -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <label for="gosok_merk" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            Merk Lensa <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="gosok_merk" name="merk" required 
                                               placeholder="Contoh: Essilor, Hoya, Zeiss" 
                                               style="border-radius: 4px; border: 1px solid #ddd; padding: 6px; font-size: 12px;">
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Masukkan merk lensa yang akan di-gosok</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <label for="gosok_type" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            Type Lensa
                                        </label>
                                        <select class="form-control" id="gosok_type" name="type" style="border-radius: 4px; border: 1px solid #ddd; padding: 6px; font-size: 12px;">
                                            <option value="">Pilih Type Lensa</option>
                                            <option value="Single Vision">Single Vision</option>
                                            <option value="Progressive">Progressive</option>
                                            <option value="Bifocal">Bifocal</option>
                                            <option value="Trifocal">Trifocal</option>
                                            <option value="Reading">Reading</option>
                                            <option value="Computer">Computer</option>
                                        </select>
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Pilih type lensa sesuai kebutuhan</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Row 2: Index, Coating, CLY -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <label for="gosok_index" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            Index
                                        </label>
                                        <select class="form-control" id="gosok_index" name="index" style="border-radius: 4px; border: 1px solid #ddd; padding: 6px; font-size: 12px;">
                                            <option value="">Pilih Index</option>
                                            <option value="1.50">1.50</option>
                                            <option value="1.56">1.56</option>
                                            <option value="1.59">1.59</option>
                                            <option value="1.60">1.60</option>
                                            <option value="1.67">1.67</option>
                                            <option value="1.70">1.70</option>
                                            <option value="1.74">1.74</option>
                                        </select>
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Index refraksi lensa</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <label for="gosok_coating" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            Coating
                                        </label>
                                        <select class="form-control" id="gosok_coating" name="coating" style="border-radius: 4px; border: 1px solid #ddd; padding: 6px; font-size: 12px;">
                                            <option value="">Pilih Coating</option>
                                            <option value="Anti-Reflective">Anti-Reflective</option>
                                            <option value="Blue Cut">Blue Cut</option>
                                            <option value="UV Protection">UV Protection</option>
                                            <option value="Hard Coating">Hard Coating</option>
                                            <option value="Photochromic">Photochromic</option>
                                            <option value="Polarized">Polarized</option>
                                        </select>
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Jenis coating lensa</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <label for="gosok_cly" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            CLY (Diameter)
                                        </label>
                                        <input type="text" class="form-control" id="gosok_cly" name="cly" 
                                               placeholder="Contoh: 300, 275" 
                                               style="border-radius: 4px; border: 1px solid #ddd; padding: 6px; font-size: 12px;">
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Diameter lensa dalam mm</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Row 3: Harga & Jumlah -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <label for="gosok_harga" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            Harga Jual <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon" style="background: #3498db; color: white; border: 1px solid #3498db; font-weight: bold; font-size: 11px; padding: 6px 8px;">Rp</span>
                                            <input type="number" class="form-control" id="gosok_harga" name="harga" required 
                                                   placeholder="Masukkan harga jual" min="0" step="1000"
                                                   style="border-radius: 0 4px 4px 0; border: 1px solid #ddd; padding: 6px; font-size: 12px;">
                                        </div>
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Harga jual lensa gosok</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin-bottom: 8px;">
                                        <label for="gosok_quantity" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            Jumlah <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-addon" style="background: #e74c3c; color: white; border: 1px solid #e74c3c; font-weight: bold; font-size: 11px; padding: 6px 8px;"><i class="fa fa-cubes"></i></span>
                                            <input type="number" class="form-control" id="gosok_quantity" name="quantity" required 
                                                   placeholder="Jumlah lensa" min="1" value="1"
                                                   style="border-radius: 0 4px 4px 0; border: 1px solid #ddd; padding: 6px; font-size: 12px;">
                                        </div>
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Jumlah lensa yang akan di-gosok</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Row 4: Catatan -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="gosok_catatan" style="font-weight: bold; color: #2c3e50; margin-bottom: 3px; font-size: 12px;">
                                            Catatan (ADD)
                                        </label>
                                        <textarea class="form-control" id="gosok_catatan" name="catatan" rows="2" 
                                                  placeholder="Catatan tambahan untuk lensa gosok (resep dokter, spesifikasi khusus, dll)"
                                                  style="border-radius: 4px; border: 1px solid #ddd; padding: 6px; font-size: 12px; resize: vertical;"></textarea>
                                        <small class="help-block text-muted" style="font-size: 10px; margin-top: 1px;">Catatan khusus untuk lensa gosok</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Row 5: Action Buttons -->
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-warning btn-block" id="btn-reset-gosok" 
                                            style="padding: 8px; font-weight: bold; border-radius: 4px; font-size: 12px;">
                                        <i class="fa fa-refresh"></i> Reset Form
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-success btn-block" id="btn-add-gosok"
                                            style="padding: 8px; font-weight: bold; border-radius: 4px; font-size: 12px;">
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