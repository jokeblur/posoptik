<div class="modal fade" id="modal-lenses-gosok" tabindex="-1" role="dialog" aria-labelledby="modal-lenses-gosok-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #e67e22, #f39c12); color: white; padding: 15px;">
                <h4 class="modal-title" id="modal-lenses-gosok-label">
                    <i class="fa fa-edit"></i> Input Lensa Gosok Manual
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div id="gosok-resep-info" class="alert alert-info" style="display: none; margin-bottom: 15px; font-size: 12px;">
                    <i class="fa fa-magic"></i>
                    Index, CLY, Axis, dan ADD otomatis diambil dari resep terakhir pasien yang dipilih.
                </div>
                <form id="form-lensa-gosok-modal">
                    <!-- Row 1: Merk & Type -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_merk_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Merk Lensa <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="gosok_merk_modal" name="merk" required 
                                       placeholder="Contoh: Essilor, Hoya, Zeiss" 
                                       style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                                <small class="help-block text-muted" style="font-size: 11px; margin-top: 2px;">Masukkan merk lensa yang akan di-gosok</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_type_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Type Lensa
                                </label>
                                <select class="form-control" id="gosok_type_modal" name="type" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                                    <option value="">Pilih Type Lensa</option>
                                    <option value="Single Vision">Single Vision</option>
                                    <option value="Progressive">Progressive</option>
                                    <option value="Bifocal">Bifocal</option>
                                    <option value="Trifocal">Trifocal</option>
                                    <option value="Reading">Reading</option>
                                    <option value="Computer">Computer</option>
                                </select>
                                <small class="help-block text-muted" style="font-size: 11px; margin-top: 2px;">Pilih type lensa sesuai kebutuhan</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="checkbox-inline" style="font-weight: bold; color: #2c3e50;">
                                <input type="checkbox" id="gosok_pakai_kanan" checked>
                                Ukuran Kanan (OD)
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="checkbox-inline" style="font-weight: bold; color: #2c3e50;">
                                <input type="checkbox" id="gosok_pakai_kiri" checked>
                                Ukuran Kiri (OS)
                            </label>
                        </div>
                    </div>

                    <!-- Ukuran kanan dan kiri diisi terpisah -->
                    <div class="row">
                        <div class="col-md-6" id="gosok-ukuran-kanan">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_index_kanan_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Index Kanan (OD)
                                </label>
                                <input type="text" class="form-control" id="gosok_index_kanan_modal" 
                                       placeholder="SPH kanan" 
                                       style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                                <small class="help-block text-muted" style="font-size: 11px; margin-top: 2px;">Diambil dari SPH resep kanan</small>
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_cly_kanan_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    CLY Kanan (OD)
                                </label>
                                <input type="text" class="form-control" id="gosok_cly_kanan_modal" placeholder="CYL kanan" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_axis_kanan_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Axis Kanan (OD)
                                </label>
                                <input type="text" class="form-control" id="gosok_axis_kanan_modal" placeholder="Axis kanan" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_add_kanan_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    ADD Kanan (OD)
                                </label>
                                <input type="text" class="form-control" id="gosok_add_kanan_modal" placeholder="ADD kanan" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                            </div>
                        </div>
                        <div class="col-md-6" id="gosok-ukuran-kiri">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_index_kiri_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Index Kiri (OS)
                                </label>
                                <input type="text" class="form-control" id="gosok_index_kiri_modal" placeholder="SPH kiri" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                                <small class="help-block text-muted" style="font-size: 11px; margin-top: 2px;">Diambil dari SPH resep kiri</small>
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_cly_kiri_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    CLY Kiri (OS)
                                </label>
                                <input type="text" class="form-control" id="gosok_cly_kiri_modal" placeholder="CYL kiri" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_axis_kiri_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Axis Kiri (OS)
                                </label>
                                <input type="text" class="form-control" id="gosok_axis_kiri_modal" placeholder="Axis kiri" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_add_kiri_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    ADD Kiri (OS)
                                </label>
                                <input type="text" class="form-control" id="gosok_add_kiri_modal" placeholder="ADD kiri" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_coating_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Coating
                                </label>
                                <select class="form-control" id="gosok_coating_modal" name="coating" style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                                    <option value="">Pilih Coating</option>
                                    <option value="Anti-Reflective">Anti-Reflective</option>
                                    <option value="Blue Cut">Blue Cut</option>
                                    <option value="UV Protection">UV Protection</option>
                                    <option value="Hard Coating">Hard Coating</option>
                                    <option value="Photochromic">Photochromic</option>
                                    <option value="Polarized">Polarized</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 4: Harga & Jumlah -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_harga_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Harga Jual <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon" style="background: #3498db; color: white; border: 1px solid #3498db; font-weight: bold; font-size: 12px; padding: 8px 10px;">Rp</span>
                                    <input type="number" class="form-control" id="gosok_harga_modal" name="harga" required 
                                           placeholder="Masukkan harga jual" min="0" step="1000"
                                           style="border-radius: 0 4px 4px 0; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                                </div>
                                <small class="help-block text-muted" style="font-size: 11px; margin-top: 2px;">Harga jual lensa gosok</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="gosok_quantity_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Jumlah <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-addon" style="background: #e74c3c; color: white; border: 1px solid #e74c3c; font-weight: bold; font-size: 12px; padding: 8px 10px;"><i class="fa fa-cubes"></i></span>
                                    <input type="number" class="form-control" id="gosok_quantity_modal" name="quantity" required 
                                           placeholder="Jumlah lensa" min="1" value="1"
                                           style="border-radius: 0 4px 4px 0; border: 1px solid #ddd; padding: 8px; font-size: 13px;">
                                </div>
                                <small class="help-block text-muted" style="font-size: 11px; margin-top: 2px;">Jumlah lensa yang akan di-gosok</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 5: Catatan -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label for="gosok_catatan_modal" style="font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 13px;">
                                    Catatan
                                </label>
                                <textarea class="form-control" id="gosok_catatan_modal" name="catatan" rows="2" 
                                          placeholder="Catatan tambahan untuk lensa gosok (resep dokter, spesifikasi khusus, dll)"
                                          style="border-radius: 4px; border: 1px solid #ddd; padding: 8px; font-size: 13px; resize: vertical;"></textarea>
                                <small class="help-block text-muted" style="font-size: 11px; margin-top: 2px;">Catatan khusus untuk lensa gosok</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 5: Action Buttons -->
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-warning btn-block" id="btn-reset-gosok-modal" 
                                    style="padding: 10px; font-weight: bold; border-radius: 4px; font-size: 13px;">
                                <i class="fa fa-refresh"></i> Reset Form
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success btn-block" id="btn-add-gosok-modal"
                                    style="padding: 10px; font-weight: bold; border-radius: 4px; font-size: 13px;">
                                <i class="fa fa-plus"></i> Tambahkan ke Keranjang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
