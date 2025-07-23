<!-- Modal Webcam -->
<div class="modal fade" id="modal-webcam" tabindex="-1" role="dialog" aria-labelledby="modalWebcamLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalWebcamLabel">Ambil Foto dari Webcam</h4>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <video id="webcam-video" width="100%" height="auto" autoplay playsinline style="display: block;"></video>
                    <canvas id="webcam-canvas" style="display: none;"></canvas>
                    <img id="photo-preview" src="#" alt="Hasil Foto" style="display: none; width: 100%; height: auto; margin-top: 10px;"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="btn-close-webcam">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-snap-photo">Jepret Foto</button>
                <button type="button" class="btn btn-success" id="btn-use-photo" style="display: none;">Gunakan Foto Ini</button>
            </div>
        </div>
    </div>
</div> 