<!-- Modal Detail Pasien -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Pasien</h4>
                <div>
                    <a href="#" id="btn-cetak-resep" class="btn btn-warning btn-sm" target="_blank" style="margin-right: 10px;">
                        <i class="fa fa-print"></i> Cetak Resep
                    </a>
                    <a href="#" id="btn-cetak-resep-a4" class="btn btn-info btn-sm" target="_blank" style="margin-right: 10px;">
                        <i class="fa fa-print"></i> Cetak A4
                    </a>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
            </div>
            <div class="modal-body">
                <div style="margin-bottom:20px; border:1px solid #e0e0e0; border-radius:6px; padding:24px 32px; background:#fafbfc;">
                    <div style="max-width:500px; margin:auto;">
                        <table style="width:100%; font-size:16px; border-collapse:collapse;">
                            <tr>
                                <td style="padding:4px 8px; font-weight:bold;">Nama</td>
                                <td style="padding:4px 8px;">: <span id="detail-nama"></span></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 8px; font-weight:bold;">Alamat</td>
                                <td style="padding:4px 8px;">: <span id="detail-alamat"></span></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 8px; font-weight:bold;">No. HP</td>
                                <td style="padding:4px 8px;">: <span id="detail-nohp"></span></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 8px; font-weight:bold;">Jenis Layanan</td>
                                <td style="padding:4px 8px;">: <span id="detail-service_type"></span></td>
                            </tr>
                            <tr id="row-no-bpjs">
                                <td style="padding:4px 8px; font-weight:bold;">No. BPJS</td>
                                <td style="padding:4px 8px;">: <span id="detail-no-bpjs"></span></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 8px; font-weight:bold;">Dokter</td>
                                <td style="padding:4px 8px;">: <span id="detail-dokter"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h4 class="box-title"><i class="fa fa-stethoscope"></i> Riwayat Resep</h4>
                    </div>
                    <div class="box-body">
                        <div id="detail-prescriptions-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- JS: isi $('#detail-no-bpjs').text(response.no_bpjs || '-') dan $('#detail-dokter').text(latestPrescription.dokter_nama || '-') --> 