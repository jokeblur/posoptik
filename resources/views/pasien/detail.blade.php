<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modal-detail-title">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-detail-title">Detail Pasien</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nama:</strong> <p id="detail-nama"></p>
                        <strong>Alamat:</strong> <p id="detail-alamat"></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Telepon:</strong> <p id="detail-nohp"></p>
                        <strong>Jenis Layanan:</strong> <p id="detail-service_type"></p>
                    </div>
                </div>
                <hr>
                <h4>Riwayat Resep</h4>
                <div id="detail-prescriptions-container">
                    <!-- Prescription details will be populated here by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div> 