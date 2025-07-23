

<!-- Modal -->
<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
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
                    <div class="form-group col-sm-6">
                        <label for="nama_pasien" class="col-lg-2 col-lg-offset-1 control-label">Nama</label>
                        <div class="col-lg-6">
                            <input type="text" name="nama_pasien" id="nama_pasien" class="form-control" required autofocus>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    
                    <div class="form-group col-sm-6">
                        <label for="service_type" class="col-lg-2 col-lg-offset-1 control-label">Jenis Layanan</label>
                        <div class="col-lg-6">
                            <select name="service_type" id="service_type" class="form-control" required>
                                <option value="">Pilih Jenis Layanan</option>
                                <option value="BPJS I">BPJS I</option>
                                <option value="BPJS II">BPJS II</option>
                                <option value="BPJS III">BPJS III</option>
                                <option value="UMUM">UMUM</option>
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="nohp" class="col-lg-2 col-lg-offset-1 control-label">Telepon</label>
                        <div class="col-lg-6">
                            <input type="text" name="nohp" id="nohp" class="form-control" required>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="alamat" class="col-lg-2 col-lg-offset-1 control-label">Alamat</label>
                        <div class="col-lg-6">
                            <textarea name="alamat" id="alamat" rows="3" class="form-control"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>

                    <hr>
                    <h5 class="text-center"><b>Resep Kacamata</b></h5>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label class="col-sm-3 control-label">OD</label>
                                <div class="col-sm-3">
                                    <input type="text" name="od_sph" class="form-control" placeholder="SPH">
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" name="od_cyl" class="form-control" placeholder="CYL">
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" name="od_axis" class="form-control" placeholder="AXIS">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">OS</label>
                                <div class="col-sm-3">
                                    <input type="text" name="os_sph" class="form-control" placeholder="SPH">
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" name="os_cyl" class="form-control" placeholder="CYL">
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" name="os_axis" class="form-control" placeholder="AXIS">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="add" class="col-sm-4 control-label">ADD</label>
                                <div class="col-sm-8">
                                    <input type="text" name="add" id="add" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pd" class="col-sm-4 control-label">PD</label>
                                <div class="col-sm-8">
                                    <input type="text" name="pd" id="pd" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="form-group row">
                        <label for="catatan" class="col-lg-2 col-lg-offset-1 control-label">Catatan</label>
                        <div class="col-lg-9">
                            <textarea name="catatan" id="catatan" rows="2" class="form-control"></textarea>
                            <span class="help-block with-errors"></span>
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