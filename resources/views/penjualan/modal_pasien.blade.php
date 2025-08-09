<div class="modal fade" id="modal-pasien" tabindex="-1" role="dialog" aria-labelledby="modal-pasien-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-pasien-label">Pilih Pasien</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-pasien-modal">
                        <thead>
                            <tr>
                                <th>Nama Pasien</th>
                                <th>Alamat</th>
                                <th>No. HP</th>
                                <th>Jenis Layanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pasiens as $pasien)
                            <tr>
                                <td>{{ $pasien->nama_pasien }}</td>
                                <td>{{ $pasien->alamat }}</td>
                                <td>{{ $pasien->nohp }}</td>
                                <td>{{ $pasien->service_type }}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm select-pasien" 
                                        data-id="{{ $pasien->id_pasien }}" 
                                        data-name="{{ $pasien->nama_pasien }}">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
$(function() {
    $('#table-pasien-modal').DataTable();
});
</script>
@endpush 