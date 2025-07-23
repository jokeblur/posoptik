<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lensa & Frame</title>
    <!-- Bootstrap & DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Data Lensa & Frame</h2>
        <!-- Tombol Modal -->
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalLensa">Tambah Lensa</button>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalFrame">Tambah Frame</button>
        <hr>
        <!-- Tabel Lensa & Frame -->
        <table id="tableData" class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis</th>
                    <th>Merk</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lensas as $key => $lensa)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>Lensa</td>
                    <td>{{ $lensa->merk_lensa }}</td>
                    <td>{{ $lensa->harga_beli }}</td>
                    <td>{{ $lensa->harga_jual }}</td>
                </tr>
                @endforeach
                @foreach($frames as $key => $frame)
                <tr>
                    <td>{{ count($lensas) + $key + 1 }}</td>
                    <td>Frame</td>
                    <td>{{ $frame->merk_frame }}</td>
                    <td>{{ $frame->harga_beli }}</td>
                    <td>{{ $frame->harga_jual }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Modal Tambah Lensa -->
    <div id="modalLensa" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Tambah Lensa</h4>
                </div>
                <div class="modal-body">
                    <form id="formLensa">
                        @csrf
                        <div class="form-group">
                            <label>Merk Lensa</label>
                            <input type="text" class="form-control" name="merk_lensa" required>
                        </div>
                        <div class="form-group">
                            <label>Harga Beli</label>
                            <input type="number" class="form-control" name="harga_beli" required>
                        </div>
                        <div class="form-group">
                            <label>Harga Jual</label>
                            <input type="number" class="form-control" name="harga_jual" required>
                        </div>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Tambah Frame -->
    <div id="modalFrame" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Tambah Frame</h4>
                </div>
                <div class="modal-body">
                    <form id="formFrame">
                        @csrf
                        <div class="form-group">
                            <label>Merk Frame</label>
                            <input type="text" class="form-control" name="merk_frame" required>
                        </div>
                        <div class="form-group">
                            <label>Harga Beli</label>
                            <input type="number" class="form-control" name="harga_beli" required>
                        </div>
                        <div class="form-group">
                            <label>Harga Jual</label>
                            <input type="number" class="form-control" name="harga_jual" required>
                        </div>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript untuk AJAX -->
    <script>
        $(document).ready(function () {
            $('#tableData').DataTable();
            // Submit Form Lensa
            $("#formLensa").on("submit", function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('lensa.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        alert(response.success);
                        location.reload();
                    }
                });
            });
            // Submit Form Frame
            $("#formFrame").on("submit", function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('frame.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        alert(response.success);
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>
