

<!-- Modal -->
<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="" method="post">
            @csrf
            @method('post')

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div style="max-width:700px; margin:auto;">
                        <div style="margin-bottom:16px; display:flex; align-items:center;">
                            <label for="nama_pasien" style="font-weight:bold; min-width:140px; margin-bottom:0;">Nama</label>
                            <input type="text" name="nama_pasien" id="nama_pasien" class="form-control" required autofocus style="flex:1; margin-left:16px;">
                            <span class="help-block with-errors"></span>
                        </div>
                        <div style="margin-bottom:16px; display:flex; align-items:center;">
                            <label for="service_type" style="font-weight:bold; min-width:140px; margin-bottom:0;">Jenis Layanan</label>
                            <select name="service_type" id="service_type" class="form-control" required style="flex:1; margin-left:16px;">
                                <option value="">Pilih Jenis Layanan</option>
                                <option value="BPJS I">BPJS I</option>
                                <option value="BPJS II">BPJS II</option>
                                <option value="BPJS III">BPJS III</option>
                                <option value="UMUM">UMUM</option>
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>
                        <div style="margin-bottom:16px; display:flex; align-items:center;" id="form-no-bpjs">
                            <label for="no_bpjs" style="font-weight:bold; min-width:140px; margin-bottom:0;">No. BPJS</label>
                            <input type="text" name="no_bpjs" id="no_bpjs" class="form-control" value="{{ isset($pasien) ? $pasien->no_bpjs : '' }}" style="flex:1; margin-left:16px;">
                            <span class="help-block with-errors"></span>
                        </div>
                        <div style="margin-bottom:16px; display:flex; align-items:center;">
                            <label for="dokter_id" style="font-weight:bold; min-width:140px; margin-bottom:0;">Dokter</label>
                            <div style="flex:1; margin-left:16px; display:flex; gap:8px; align-items:center;">
                                <select name="dokter_id" id="dokter_id" class="form-control" style="flex:1;">
                                    <option value="">Pilih Dokter</option>
                                    @foreach($dokters as $dokter)
                                        <option value="{{ $dokter->id_dokter }}" {{ (isset($pasien) && isset($pasien->prescriptions) && $pasien->prescriptions->last() && $pasien->prescriptions->last()->dokter_id == $dokter->id_dokter) ? 'selected' : '' }}>{{ $dokter->nama_dokter }}</option>
                                    @endforeach
                                    <option value="manual">Input Manual</option>
                                </select>
                                <input type="text" name="dokter_manual" id="dokter_manual" class="form-control" placeholder="Nama Dokter" style="display:none; flex:1;" maxlength="100">
                            </div>
                            <span class="help-block with-errors"></span>
                        </div>
                        <div style="margin-bottom:16px; display:flex; align-items:center;">
                            <label for="nohp" style="font-weight:bold; min-width:140px; margin-bottom:0;">Telepon</label>
                            <input type="text" name="nohp" id="nohp" class="form-control" required style="flex:1; margin-left:16px;">
                            <span class="help-block with-errors"></span>
                        </div>
                        <div style="margin-bottom:16px; display:flex; align-items:flex-start;">
                            <label for="alamat" style="font-weight:bold; min-width:140px; margin-top:6px;">Alamat</label>
                            <textarea name="alamat" id="alamat" rows="3" class="form-control" style="flex:1; margin-left:16px;"></textarea>
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <hr>
                    <h5 class="text-center" style="margin-bottom:18px;"><b>Resep Kacamata</b></h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div style="margin-bottom:16px;">
                                <label style="font-weight:bold;">OD</label>
                                <div class="row" style="margin-bottom:8px;">
                                    <div class="col-xs-4"><input type="text" name="od_sph" class="form-control" placeholder="SPH"></div>
                                    <div class="col-xs-4"><input type="text" name="od_cyl" class="form-control" placeholder="CYL"></div>
                                    <div class="col-xs-4"><input type="text" name="od_axis" class="form-control" placeholder="AXIS"></div>
                                </div>
                            </div>
                            <div style="margin-bottom:16px;">
                                <label style="font-weight:bold;">OS</label>
                                <div class="row" style="margin-bottom:8px;">
                                    <div class="col-xs-4"><input type="text" name="os_sph" class="form-control" placeholder="SPH"></div>
                                    <div class="col-xs-4"><input type="text" name="os_cyl" class="form-control" placeholder="CYL"></div>
                                    <div class="col-xs-4"><input type="text" name="os_axis" class="form-control" placeholder="AXIS"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="margin-bottom:16px;">
                                <label for="add" style="font-weight:bold;">ADD</label>
                                <input type="text" name="add" id="add" class="form-control" style="margin-bottom:8px;">
                            </div>
                            <div style="margin-bottom:16px;">
                                <label for="pd" style="font-weight:bold;">PD</label>
                                <input type="text" name="pd" id="pd" class="form-control" style="margin-bottom:8px;">
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom:16px; display:flex; align-items:flex-start;">
                        <label for="catatan" style="font-weight:bold; min-width:140px; margin-top:6px;">Catatan</label>
                        <textarea name="catatan" id="catatan" rows="2" class="form-control" style="flex:1; margin-left:16px;"></textarea>
                        <span class="help-block with-errors"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-custom">Simpan</button>
                    <button type="button" class="btn btn-sm btn-success" id="btn-simpan-transaksi">Simpan & Lanjut ke Transaksi</button>
                    <button type="button" class="btn btn-sm btn-custom-close" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
$(function() {
    $('#service_type').on('change', function() {
        var val = $(this).val();
        if(val === 'BPJS I' || val === 'BPJS II' || val === 'BPJS III') {
            $('#form-no-bpjs').show();
            $('#no_bpjs').prop('required', true);
        } else {
            $('#form-no-bpjs').hide();
            $('#no_bpjs').prop('required', false);
        }
    });
    $('#dokter_id').on('change', function() {
        if($(this).val() === 'manual') {
            $('#dokter_manual').show().prop('required', true);
        } else {
            $('#dokter_manual').hide().prop('required', false);
        }
    });
});
</script>
@endpush