@extends('layouts.master')

@section('title', 'Transaksi Penjualan')

@section('content')
<form action="{{ route('penjualan.store') }}" method="POST" id="form-penjualan" enctype="multipart/form-data">
    @csrf
    <div class="row">
    {{-- Right Column - Transaction Details --}}
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border"><h3 class="box-title">Detail Transaksi</h3></div>
                <div class="box-body">
                    <div class="form-group col-md-4">
                        <label for="kode_penjualan">Kode Transaksi</label>
                        <input type="text" class="form-control" name="kode_penjualan" value="{{ 'MLT-' . time() }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal">Tanggal Transaksi</label>
                        <input type="text" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal_siap">Tanggal Siap</label>
                        <input type="date" class="form-control" name="tanggal_siap">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Pasien</label>
                        <div class="input-group">
                            <input type="hidden" name="pasien_id" id="pasien_id">
                            <input type="text" class="form-control" id="pasien_name"  required placeholder="Pilih Pasien">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-pasien">Cari</button>
                            </span>
                        </div>
                    </div>
                     <div class="col-md-12">
                        <div id="pasien-details-container" style="display: none;">
                            <h4>Detail Pasien</h4>
                            <p><strong>Alamat:</strong> <span id="detail-alamat"></span></p>
                            <p><strong>No. HP:</strong> <span id="detail-nohp"></span></p>
                            <p><strong>Jenis Layanan</strong> <span id="detail-jenis_layanan"></span></p>
                            <hr>
                            <h4>Resep Terakhir (<span id="resep-tanggal"></span>)</h4>
                            <table class="table table-bordered text-center" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 25%;">Mata</th>
                                        <th class="text-center" style="width: 25%;">SPH</th>
                                        <th class="text-center" style="width: 25%;">CYL</th>
                                        <th class="text-center" style="width: 25%;">AXIS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>OD (Kanan)</strong></td>
                                        <td id="resep-od-sph"></td>
                                        <td id="resep-od-cyl"></td>
                                        <td id="resep-od-axis"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>OS (Kiri)</strong></td>
                                        <td id="resep-os-sph"></td>
                                        <td id="resep-os-cyl"></td>
                                        <td id="resep-os-axis"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-xs-6">
                                    <p><strong>ADD:</strong> <span id="resep-add"></span></p>
                                </div>
                                <div class="col-xs-6">
                                    <p><strong>PD:</strong> <span id="resep-pd"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
        <div class="col-md-4">
        <div class="box">
                 <div class="box-header with-border"><h3 class="box-title">Pelanggan & Dokter</h3></div>
                 <div class="box-body">
                
                    <div class="form-group">
                        <label for="dokter_id">Dokter</label>
                        <select name="dokter_id" id="dokter_id" class="form-control">
                            <option value="">Pilih Dokter</option>
                             @foreach($dokters as $dokter)
                                <option value="{{ $dokter->id_dokter }}">{{ $dokter->nama_dokter }}</option>
                            @endforeach
                        </select>
                    </div>
                 </div>
            </div>
        </div>
    
    {{-- Left Column - Products & Cart --}}
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-frames">Cari Frame</button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-lenses">Cari Lensa</button>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <!-- <th width="5%">No</th> -->
                                <th>Produk</th>
                                <th width="15%">Jumlah</th>
                                <th width="20%">Harga</th>
                                <th width="20%">Subtotal</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cart-table">
                            {{-- Cart items --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

       
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="diskon">Diskon (Rp)</label>
                                <input type="number" name="diskon" id="diskon" class="form-control" value="0">
                            </div>
                            <div class="form-group">
                                <label for="bayar">Jumlah Bayar (Rp)</label>
                                <input type="number" name="bayar" id="bayar" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <h4 style="font-size: large;">Subtotal: <span id="subtotal-amount">Rp 0</span></h4>
                            <h2 style="font-weight: bold;">Total: <span id="total-amount">Rp 0</span></h2>
                            <h3 id="kekurangan-container" style="font-weight: bold; display: none;">Kekurangan: <span id="kekurangan-amount">Rp 0</span></h3>
                            <h3 id="kembalian-container" style="font-weight: bold; display: none;">Kembalian: <span id="kembalian-amount">Rp 0</span></h3>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group" id="photo-bpjs-container" style="display: none;">
                        <label for="photo_bpjs">Foto Bukti BPJS</label>
                        <div class="input-group">
                            <input type="file" name="photo_bpjs" id="photo_bpjs" class="form-control" accept="image/*" capture="environment">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" id="btn-open-webcam" data-toggle="modal" data-target="#modal-webcam">
                                    <i class="fa fa-camera"></i> Buka Webcam
                                </button>
                            </span>
                        </div>
                        <p class="help-block">Wajib diisi untuk pasien BPJS. Ambil foto langsung atau dari webcam.</p>
                    </div>
                    <input type="hidden" name="total" id="total-input">
                    <input type="hidden" name="kekurangan" id="kekurangan-input">
                    <input type="hidden" name="items" id="items-input">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Simpan Transaksi</button>
                </div>
            </div>
        </div>
    </div>
</form>

@include('penjualan.modal_frame')
@include('penjualan.modal_lensa')
@include('penjualan.modal_pasien')
@include('penjualan.modal_webcam')
@endsection

@push('scripts')
<script>
$(function() {
    // Initialize DataTables
    $('#table-frames').DataTable();
    $('#table-lenses').DataTable();
    $('#table-aksesoris').DataTable();
    $('#table-pasien').DataTable();

    let cart = [];

    // All event listeners for patient & product selection remain the same...

    // Select Pasien
    $(document).on('click', '.select-pasien', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let name = $(this).data('name');
        let url = "{{ route('pasien.details', ['id' => ':id']) }}";
        url = url.replace(':id', id);

        $('#pasien_id').val(id);
        $('#pasien_name').val(name);
        $('#modal-pasien').modal('hide');
        
        // AJAX call to get patient details and prescriptions
        $.get(url)
            .done(function(response) {
                // Tampilkan detail pasien
                $('#detail-alamat').text(response.alamat || '-');
                $('#detail-nohp').text(response.nohp || '-');
                $('#detail-jenis_layanan').text(response.service_type || '-');

                // Logika untuk menampilkan input foto BPJS
                if (response.service_type && response.service_type.toLowerCase().includes('bpjs')) {
                    $('#photo-bpjs-container').slideDown();
                    $('#photo_bpjs').prop('required', true);
                } else {
                    $('#photo-bpjs-container').slideUp();
                    $('#photo_bpjs').prop('required', false);
                }
                
                // Tampilkan resep dengan format baru
                if (response.prescriptions && response.prescriptions.length > 0) {
                    let resep = response.prescriptions[response.prescriptions.length - 1]; // Ambil resep terakhir
                    
                    $('#resep-tanggal').text(resep.tanggal);
                    $('#resep-od-sph').text(resep.od_sph || '-');
                    $('#resep-od-cyl').text(resep.od_cyl || '-');
                    $('#resep-od-axis').text(resep.od_axis || '-');
                    $('#resep-os-sph').text(resep.os_sph || '-');
                    $('#resep-os-cyl').text(resep.os_cyl || '-');
                    $('#resep-os-axis').text(resep.os_axis || '-');
                    $('#resep-add').text(resep.add || '-');
                    $('#resep-pd').text(resep.pd || '-');

                } else {
                    // Jika tidak ada resep, kosongkan semua field
                    $('#resep-tanggal').text('N/A');
                    $('#resep-od-sph, #resep-od-cyl, #resep-od-axis, #resep-os-sph, #resep-os-cyl, #resep-os-axis, #resep-add, #resep-pd').text('-');
                }

                // Tampilkan kontainer detail
                $('#pasien-details-container').slideDown();
            })
            .fail(function() {
                alert('Gagal mengambil detail pasien.');
            });
    });
    
    // Add item to cart from modal
    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        let product = { id: $(this).data('id'), name: $(this).data('name'), price: parseFloat($(this).data('price')), type: $(this).data('type'), quantity: 1 };
        addToCart(product);
        $(this).closest('.modal').modal('hide');
    });

    function addToCart(product) {
        let existingItem = cart.find(item => item.id === product.id && item.type === product.type);
        if (existingItem) { existingItem.quantity++; } else { cart.push(product); }
        $('#bayar').data('user-has-changed', false); // Reset
        renderCartAndTotals();
    }

    function renderCartAndTotals() {
        let cartTable = $('#cart-table');
        cartTable.empty();
        let subtotal = 0;

        if (cart.length === 0) {
            cartTable.append('<tr><td colspan="5" class="text-center">Keranjang kosong</td></tr>');
        } else {
            cart.forEach((item, index) => {
                let itemSubtotal = item.price * item.quantity;
                subtotal += itemSubtotal;
                let row = `<tr><td>${item.name}</td><td><input type="number" class="form-control quantity-input" value="${item.quantity}" data-index="${index}" min="1" style="width: 70px;"></td><td>Rp ${item.price.toLocaleString('id-ID')}</td><td>Rp ${itemSubtotal.toLocaleString('id-ID')}</td><td><button class="btn btn-danger btn-sm remove-item" data-index="${index}">&times;</button></td></tr>`;
                cartTable.append(row);
            });
        }
        
        let diskon = parseFloat($('#diskon').val()) || 0;
        let total = subtotal - diskon;

        // Atur default jumlah bayar sama dengan total, hanya jika belum diubah manual oleh user
        let bayarInput = $('#bayar');
        if (!bayarInput.data('user-has-changed')) {
            bayarInput.val(total);
        }
        
        let bayar = parseFloat(bayarInput.val()) || 0;
        let selisih = bayar - total;

        // Tampilkan kekurangan atau kembalian
        if (selisih < 0) {
            $('#kekurangan-container').show();
            $('#kembalian-container').hide();
            $('#kekurangan-amount').text('Rp ' + Math.abs(selisih).toLocaleString('id-ID'));
            $('#kekurangan-input').val(Math.abs(selisih));
        } else {
            $('#kekurangan-container').hide();
            $('#kembalian-container').show();
            $('#kembalian-amount').text('Rp ' + selisih.toLocaleString('id-ID'));
            $('#kekurangan-input').val(0); // Jika lunas atau ada kembalian, kekurangan adalah 0
        }

        $('#subtotal-amount').text('Rp ' + subtotal.toLocaleString('id-ID'));
        $('#total-amount').text('Rp ' + total.toLocaleString('id-ID'));

        $('#total-input').val(total);
        $('#items-input').val(JSON.stringify(cart));
    }

    // Tandai jika user sudah mengubah input bayar secara manual
    $('#bayar').on('input', function() {
        $(this).data('user-has-changed', true);
    });

    // Event listeners for financial inputs
    $('#diskon, #bayar').on('keyup change', function() {
        renderCartAndTotals();
    });

    // Event listeners for cart item quantity change and removal
    $(document).on('change', '.quantity-input', function() {
        let index = $(this).data('index');
        let newQuantity = parseInt($(this).val());
        if (newQuantity > 0) { cart[index].quantity = newQuantity; }
        renderCartAndTotals();
    });

    $(document).on('click', '.remove-item', function() {
        let index = $(this).data('index');
        cart.splice(index, 1);
        $('#bayar').data('user-has-changed', false); // Reset
        renderCartAndTotals();
    });

    renderCartAndTotals(); // Initial render

    // Simpan manual pasien
    $('#btn-simpan-manual-pasien').on('click', function() {
        var nama = $('#manual-pasien-name').val();
        if (nama.trim() === '') {
            alert('Nama pasien harus diisi!');
            return;
        }
        $('#pasien_id').val('');
        $('#pasien_name').val(nama);
        $('#modal-input-manual-pasien').modal('hide');
        // Sembunyikan detail pasien jika sebelumnya tampil
        $('#pasien-details-container').slideUp();
        // Clear input manual
        $('#manual-pasien-name').val('');
    });

    // Clear manual pasien input saat modal ditutup
    $('#modal-input-manual-pasien').on('hidden.bs.modal', function() {
        $('#manual-pasien-name').val('');
    });

    // New logic for form submission
    $('#form-penjualan').on('submit', function(e) {
        e.preventDefault(); // Stop normal form submission

        // Validasi keranjang tidak kosong
        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Keranjang belanja masih kosong. Tambahkan minimal satu produk.',
            });
            return;
        }

        // Validasi nama pasien
        if (!$('#pasien_name').val().trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Nama pasien harus diisi.',
            });
            return;
        }

        let form = $(this);
        let url = form.attr('action');
        // Gunakan FormData untuk mengirim file
        let data = new FormData(this);

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            processData: false, // Penting untuk FormData
            contentType: false, // Penting untuk FormData
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect_url;
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorMessage,
                });
            }
        });
    });

    // Logika Webcam
    let video = document.getElementById('webcam-video');
    let canvas = document.getElementById('webcam-canvas');
    let photoPreview = document.getElementById('photo-preview');
    let snapButton = document.getElementById('btn-snap-photo');
    let useButton = document.getElementById('btn-use-photo');
    let closeButton = document.getElementById('btn-close-webcam');
    let stream;

    $('#btn-open-webcam').on('click', function() {
        // Reset tampilan
        video.style.display = 'block';
        photoPreview.style.display = 'none';
        snapButton.style.display = 'inline-block';
        useButton.style.display = 'none';

        // Akses kamera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(s) {
                stream = s;
                video.srcObject = stream;
            })
            .catch(function(err) {
                console.error("Error accessing webcam: ", err);
                alert('Tidak dapat mengakses webcam. Pastikan Anda memberikan izin.');
                $('#modal-webcam').modal('hide');
            });
    });

    snapButton.addEventListener('click', function() {
        // Atur ukuran canvas sesuai video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        // Gambar frame saat ini dari video ke canvas
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Tampilkan hasil foto, sembunyikan video
        photoPreview.src = canvas.toDataURL('image/png');
        video.style.display = 'none';
        photoPreview.style.display = 'block';

        // Ubah tombol
        snapButton.style.display = 'none';
        useButton.style.display = 'inline-block';
    });

    useButton.addEventListener('click', function() {
        // Konversi data canvas ke Blob (seperti file)
        canvas.toBlob(function(blob) {
            // Buat file baru dari blob
            let file = new File([blob], "webcam_capture.png", { type: "image/png" });

            // Gunakan DataTransfer untuk memasukkan file ke input
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('photo_bpjs').files = dataTransfer.files;

            // Tutup modal dan hentikan stream
            closeWebcamStream();
            $('#modal-webcam').modal('hide');
        }, 'image/png');
    });

    closeButton.addEventListener('click', function() {
        closeWebcamStream();
    });

    $('#modal-webcam').on('hidden.bs.modal', function () {
        closeWebcamStream();
    });

    function closeWebcamStream() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }
});
</script>
@endpush 