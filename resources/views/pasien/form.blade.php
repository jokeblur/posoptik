

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
                        <div id="group-mode-resep-baru" style="margin-bottom:16px; padding:10px; background:#fff3cd; border:1px solid #ffc107; border-radius:4px;">
                            <label style="font-weight:bold; margin-bottom:0; cursor:pointer;">
                                <input type="checkbox" id="mode_resep_baru" style="margin-right:8px;">
                                Pasien Bikin Resep di Optik Melati (resep diisi manual oleh RO)
                            </label>
                        </div>
                        <div style="margin-bottom:16px; display:flex; align-items:center;">
                            <label for="nama_pasien" style="font-weight:bold; min-width:140px; margin-bottom:0;">Nama</label>
                            <input type="text" name="nama_pasien" id="nama_pasien" class="form-control" required autofocus style="flex:1; margin-left:16px;">
                            <span class="help-block with-errors"></span>
                        </div>
                        <div id="group-resep-baru" style="display:none;">
                            <div style="margin-bottom:16px; display:flex; align-items:center;">
                                <label for="umur" style="font-weight:bold; min-width:140px; margin-bottom:0;">Umur</label>
                                <input type="text" name="umur" id="umur" class="form-control" style="flex:1; margin-left:16px;">
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>
                        <div id="group-lengkap">
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
                        <div id="group-resep-baru-2" style="display:none;">
                            <div style="margin-bottom:16px; display:flex; align-items:flex-start;">
                                <label for="anamnesa" style="font-weight:bold; min-width:140px; margin-top:6px;">Anamnesa</label>
                                <textarea name="anamnesa" id="anamnesa" rows="2" class="form-control" style="flex:1; margin-left:16px;"></textarea>
                                <span class="help-block with-errors"></span>
                            </div>
                            <div style="margin-bottom:16px; display:flex; align-items:center;">
                                <label for="tanggal_periksa" style="font-weight:bold; min-width:140px; margin-bottom:0;">Tanggal Periksa</label>
                                <input type="date" name="tanggal_periksa" id="tanggal_periksa" class="form-control" style="flex:1; margin-left:16px;">
                                <span class="help-block with-errors"></span>
                            </div>
                        </div>
                    </div>
                    <hr id="divider-resep-lengkap">
                    <div id="group-resep-lengkap">
                    <h5 class="text-center" style="margin-bottom:18px;"><b>Resep Kacamata</b></h5>
                    <div class="row">
                        <div class="col-md-12">
                            <!-- OD Row -->
                            <div style="margin-bottom:16px;">
                                <div class="row" style="align-items: flex-end;">
                                    <div class="col-xs-1">
                                        <label style="font-weight:bold; display:block; margin-bottom:5px;">OD</label>
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="od_sph" class="form-control" placeholder="SPH" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="od_cyl" class="form-control" placeholder="CYL" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="od_axis" class="form-control" placeholder="AXIS" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="add_kanan" id="add_kanan" class="form-control" placeholder="ADD" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="pd_kanan" id="pd_kanan" class="form-control" placeholder="PD" style="font-size:12px;">
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <!-- OS Row -->
                            <div style="margin-bottom:16px;">
                                <div class="row" style="align-items: flex-end;">
                                    <div class="col-xs-1">
                                        <label style="font-weight:bold; display:block; margin-bottom:5px;">OS</label>
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="os_sph" class="form-control" placeholder="SPH" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="os_cyl" class="form-control" placeholder="CYL" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="os_axis" class="form-control" placeholder="AXIS" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="add_kiri" id="add_kiri" class="form-control" placeholder="ADD" style="font-size:12px;">
                                    </div>
                                    <div class="col-xs-2">
                                        <input type="text" name="pd_kiri" id="pd_kiri" class="form-control" placeholder="PD" style="font-size:12px;">
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom:16px; display:flex; align-items:flex-start;">
                        <label for="catatan" style="font-weight:bold; min-width:140px; margin-top:6px;">Catatan</label>
                        <textarea name="catatan" id="catatan" rows="2" class="form-control" style="flex:1; margin-left:16px;"></textarea>
                        <span class="help-block with-errors"></span>
                    </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-custom">Simpan</button>
                    <button type="button" class="btn btn-sm btn-success" id="btn-simpan-transaksi">Simpan & Lanjut ke Transaksi</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-simpan-cetak-ro" style="display:none;">Simpan & Cetak Kartu RO</button>
                    <button type="button" class="btn btn-sm btn-custom-close" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
$(function() {
    function applyResepBaruMode(isBaru) {
        if (isBaru) {
            $('#group-resep-baru, #group-resep-baru-2').show();
            $('#group-lengkap, #group-resep-lengkap, #divider-resep-lengkap').hide();
            $('#service_type').prop('required', false);
            $('#no_bpjs').prop('required', false);
            $('#btn-simpan-cetak-ro').show();
            $('#btn-simpan-transaksi').hide();
        } else {
            $('#group-resep-baru, #group-resep-baru-2').hide();
            $('#group-lengkap, #group-resep-lengkap, #divider-resep-lengkap').show();
            $('#service_type').prop('required', true);
            $('#btn-simpan-cetak-ro').hide();
            $('#btn-simpan-transaksi').show();
        }
    }

    $('#mode_resep_baru').on('change', function() {
        applyResepBaruMode($(this).is(':checked'));
    });

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

    $(document).on('click', '#btn-simpan-cetak-ro', function() {
        let form = $('#modal-form form');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }
        $.post(form.attr('action'), form.serialize())
            .done((response) => {
                $('#modal-form').modal('hide');
                if (typeof table !== 'undefined') {
                    table.ajax.reload();
                }
                if (response.id_pasien) {
                    window.open('{{ url("/pasien") }}/' + response.id_pasien + '/cetak-status-refraksi', '_blank');
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .fail((errors) => {
                let message = 'Tidak dapat menyimpan data';
                if (errors.responseJSON && errors.responseJSON.message) {
                    message = errors.responseJSON.message;
                }
                Swal.fire('Error!', message, 'error');
            });
    });
});
</script>
@endpush