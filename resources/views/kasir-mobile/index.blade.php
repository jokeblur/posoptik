@extends('kasir-mobile.layout')

@section('content')
<style>
    .product-btn {
        width: 100%; border-radius: 12px; padding: 16px 10px; font-weight: 700;
        border: none; color: #fff; font-size: 13px; line-height: 1.4;
    }
    .product-btn i { display: block; font-size: 24px; margin-bottom: 6px; }
    .cart-item {
        display: flex; align-items: center; padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item .ci-info { flex: 1; min-width: 0; }
    .cart-item .ci-name { font-weight: 700; font-size: 13px; }
    .cart-item .ci-meta { font-size: 11px; color: #888; }
    .cart-item .ci-price { font-size: 13px; font-weight: 700; color: var(--brand); }
    .qty-btn {
        width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #ddd;
        background: #fff; font-size: 16px; font-weight: 700;
    }
    .qty-val { width: 34px; text-align: center; font-weight: 700; display: inline-block; }
    .total-bar {
        background: var(--brand); color: #fff; border-radius: 12px;
        padding: 14px; display: flex; justify-content: space-between; align-items: center;
        position: sticky; bottom: calc(84px + env(safe-area-inset-bottom, 0px));
        z-index: 1020; min-height: 84px; box-shadow: 0 3px 14px rgba(0,0,0,.2);
    }
    .total-bar .t-val { font-size: 26px; font-weight: 700; }
    .pay-chip {
        flex: 1; padding: 10px; text-align: center; border-radius: 10px;
        border: 1.5px solid #ddd; background: #fff; font-weight: 700; font-size: 13px;
    }
    .pay-chip.active { background: var(--brand); color: #fff; border-color: var(--brand); }
    .search-result-item {
        padding: 12px; border-bottom: 1px solid #f0f0f0; cursor: pointer;
    }
    .search-result-item:active { background: #faf0f3; }
    .patient-search-results {
        max-height: 240px; overflow-y: auto; border: 1px solid #ddd;
        border-radius: 6px; background: #fff; margin-bottom: 10px;
    }
    .patient-search-results button {
        display: block; width: 100%; padding: 10px 12px; border: 0;
        border-bottom: 1px solid #eee; background: #fff; text-align: left;
    }
    .patient-search-results button:last-child { border-bottom: 0; }
    .quick-nominal {
        flex: 1; padding: 10px 4px; border-radius: 10px; border: 1.5px solid #ddd;
        background: #fff; font-weight: 700; font-size: 12px; text-align: center;
    }
    .bpjs-signature-canvas {
        display: block; width: 100%; height: 140px; border: 1.5px solid #bbb;
        border-radius: 6px; background: #fff; touch-action: none;
    }
</style>

{{-- ============ PASIEN ============ --}}
<div class="m-card">
    <h4><i class="fa fa-user text-brand"></i> Pasien</h4>
    <label class="m-label">Pilih Pasien Terdaftar</label>
    <input type="hidden" id="pasien_id">
    <input type="search" id="pasien-search" class="m-input" placeholder="Ketik nama atau nomor HP pasien..." autocomplete="off" style="margin-bottom:8px;">
    <div id="pasien-search-results" class="patient-search-results" style="display:none;"></div>
    <div id="pasien-manual-wrap">
        <label class="m-label">Nama Pasien (jika tidak terdaftar)</label>
        <input type="text" id="pasien_name" class="m-input" placeholder="Nama pasien...">
        <label class="m-label" style="margin-top:8px;">No. HP</label>
        <input type="text" id="pasien_nohp" class="m-input" placeholder="Nomor HP pasien">
        <label class="m-label" style="margin-top:8px;">Jenis Layanan</label>
        <select id="pasien_service_type" class="m-select">
            <option value="">Pilih jenis layanan</option>
            <option value="UMUM">UMUM</option>
            <option value="BPJS I">BPJS I</option>
            <option value="BPJS II">BPJS II</option>
            <option value="BPJS III">BPJS III</option>
        </select>
        <label class="m-label" style="margin-top:8px;">Alamat</label>
        <textarea id="pasien_alamat" class="m-input" rows="2" placeholder="Alamat pasien"></textarea>
        <div id="pasien-no-bpjs-wrap" style="display:none; margin-top:8px;">
            <label class="m-label">No. BPJS</label>
            <input type="text" id="pasien_no_bpjs" class="m-input" placeholder="Nomor BPJS (opsional)">
        </div>
    </div>
    <div id="pasien-terdaftar-wrap" style="display:none; padding:10px; background:#f7f8f8; border-radius:8px; font-size:13px;">
        <div><strong>Alamat:</strong> <span id="pasien-terdaftar-alamat">-</span></div>
        <div style="margin-top:4px;"><strong>Jenis layanan:</strong> <span id="pasien-terdaftar-service">-</span></div>
        <div style="margin-top:4px;"><strong>No. HP:</strong> <span id="pasien-terdaftar-nohp">-</span></div>
        <div id="pasien-terdaftar-bpjs-wrap" style="display:none; margin-top:4px;"><strong>No. BPJS:</strong> <span id="pasien-terdaftar-bpjs">-</span></div>
    </div>
    <div style="border-top:1px solid #eee; margin-top:12px; padding-top:12px;">
        <label class="m-label" for="dokter_id">Dokter</label>
        <select id="dokter_id" class="m-select" style="margin-bottom:8px;">
            <option value="">Pilih dokter terdaftar (opsional)</option>
            @foreach($dokters as $dokter)
                <option value="{{ $dokter->id_dokter }}">{{ $dokter->nama_dokter }}</option>
            @endforeach
        </select>
        <label class="m-label" for="dokter_manual">Atau input nama dokter</label>
        <input type="text" id="dokter_manual" class="m-input" placeholder="Nama dokter manual (opsional)" autocomplete="off">
    </div>
    <div id="foto-pasien-wrap" style="display:none; margin-top:12px;">
        <label class="m-label">Foto pasien</label>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn btn-default" onclick="document.getElementById('foto_pasien').click()">
                <i class="fa fa-folder-open"></i> Pilih file
            </button>
            <button type="button" class="btn btn-default" onclick="openPatientCamera()">
                <i class="fa fa-camera"></i> Ambil foto
            </button>
        </div>
        <input type="file" id="foto_pasien" accept="image/*" style="display:none;">
        <small id="foto-pasien-filename" style="display:block; color:#777; margin-top:6px;"></small>
        <img id="foto-pasien-preview" alt="Foto pasien" style="display:none; width:100%; max-height:260px; object-fit:contain; margin-top:8px; border:1px solid #ddd;">
    </div>
    <div id="bpjs-capture-wrap" style="display:none; border-top:1px solid #eee; margin-top:12px; padding-top:12px;">
        <h5 style="font-weight:700; margin:0 0 8px;">Tanda Tangan BPJS</h5>
        <label class="m-label">Tanda tangan pasien</label>
        <canvas id="bpjs-signature-canvas" class="bpjs-signature-canvas" width="720" height="260"></canvas>
        <button type="button" id="clear-bpjs-signature" class="btn btn-default btn-block" style="margin-top:8px;">
            <i class="fa fa-eraser"></i> Hapus tanda tangan
        </button>
        <small style="display:block; color:#777; margin-top:6px;">Minta pasien membubuhkan tanda tangan pada area di atas.</small>
    </div>
    <div style="border-top:1px solid #eee; margin-top:12px; padding-top:12px;">
        <h5 style="font-weight:700; margin:0 0 8px;">Resep Pasien <small id="resep-source" style="font-weight:400; color:#888;"></small></h5>
        <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:6px;">
            <div><label class="m-label">OD SPH</label><input type="text" id="rx-od-sph" class="m-input rx-input" placeholder="0.00"></div>
            <div><label class="m-label">OD CYL</label><input type="text" id="rx-od-cyl" class="m-input rx-input" placeholder="0.00"></div>
            <div><label class="m-label">OD AXIS</label><input type="text" id="rx-od-axis" class="m-input rx-input" placeholder="0"></div>
            <div><label class="m-label">OS SPH</label><input type="text" id="rx-os-sph" class="m-input rx-input" placeholder="0.00"></div>
            <div><label class="m-label">OS CYL</label><input type="text" id="rx-os-cyl" class="m-input rx-input" placeholder="0.00"></div>
            <div><label class="m-label">OS AXIS</label><input type="text" id="rx-os-axis" class="m-input rx-input" placeholder="0"></div>
            <div><label class="m-label">ADD kanan</label><input type="text" id="rx-add-kanan" class="m-input rx-input" placeholder="0.00"></div>
            <div><label class="m-label">ADD kiri</label><input type="text" id="rx-add-kiri" class="m-input rx-input" placeholder="0.00"></div>
            <div><label class="m-label">PD</label><input type="text" id="rx-pd" class="m-input" placeholder="PD"></div>
        </div>
        <div id="resep-status" style="font-size:11px; color:#888; margin-top:6px;"></div>
    </div>
</div>

{{-- ============ STEP 1: PILIH PRODUK ============ --}}
<div class="m-card">
    <h4><i class="fa fa-cubes text-brand"></i> Tambah Produk</h4>
    <div class="row" style="margin-left:-6px; margin-right:-6px;">
        <div class="col-xs-4" style="padding:6px;">
            <button type="button" class="product-btn" style="background:#2980b9;" onclick="openProductModal('frame')">
                <i class="fa fa-eye"></i>Frame
            </button>
        </div>
        <div class="col-xs-4" style="padding:6px;">
            <button type="button" class="product-btn" style="background:#8e44ad;" onclick="openProductModal('lensa')">
                <i class="fa fa-circle-o"></i>Lensa
            </button>
        </div>
        <div class="col-xs-4" style="padding:6px;">
            <button type="button" class="product-btn" style="background:#16a085;" onclick="openProductModal('aksesoris')">
                <i class="fa fa-leaf"></i>Aksesoris
            </button>
        </div>
    </div>
    <button type="button" class="btn btn-warning btn-block" style="margin-top:8px;" onclick="openLensGosokModal()">
        <i class="fa fa-pencil"></i> Input Lensa Gosok
    </button>
    <div class="input-group" style="margin-top:8px;">
        <span class="input-group-addon"><i class="fa fa-search"></i></span>
        <input type="text" id="quick-search" class="m-input" placeholder="Cari cepat semua produk..." autocomplete="off">
    </div>
    <div id="quick-search-results"></div>
</div>

{{-- ============ KERANJANG ============ --}}
<div class="m-card">
    <h4><i class="fa fa-shopping-cart text-brand"></i> Keranjang <span class="badge badge-cabang" id="cart-count">0</span></h4>
    <div id="cart-list">
        <div class="m-empty" id="cart-empty"><i class="fa fa-cart-arrow-down"></i>Keranjang masih kosong</div>
    </div>
</div>

{{-- ============ PEMBAYARAN ============ --}}
<div class="m-card">
    <h4><i class="fa fa-money text-brand"></i> Pembayaran</h4>
    <label class="m-label">Metode Pembayaran</label>
    <div style="display:flex; gap:8px; margin-bottom:10px;">
        <button type="button" class="pay-chip active" data-pay="cash">Cash</button>
        <button type="button" class="pay-chip" data-pay="transfer">Transfer</button>
        <button type="button" class="pay-chip" data-pay="qris">QRIS</button>
    </div>
    <div id="bank-wrap" style="display:none; margin-bottom:10px;">
        <label class="m-label">Bank</label>
        <select id="bank_transfer" class="m-select">
            <option value="BCA">BCA</option>
            <option value="BNI">BNI</option>
            <option value="BRI">BRI</option>
            <option value="MANDIRI">MANDIRI</option>
            <option value="BSI">BSI</option>
        </select>
    </div>
    <label class="m-label">Diskon (Rp)</label>
    <input type="number" id="diskon" class="m-input" value="0" min="0" style="margin-bottom:10px;">
    <label class="m-label">Voucher</label>
    <div class="input-group" id="voucher-input-group" style="margin-bottom:10px;">
        <input type="text" id="voucher_input" class="form-control m-input" placeholder="Ketik kode voucher atau scan QR" autocomplete="off" style="text-transform:uppercase;">
        <span class="input-group-btn">
            <button type="button" class="btn btn-primary" id="btn-apply-voucher" style="height:100%;"><i class="fa fa-check"></i> Pakai</button>
            <button type="button" class="btn btn-success" id="btn-scan-voucher" title="Scan QR voucher" style="height:100%;"><i class="fa fa-qrcode"></i></button>
        </span>
    </div>
    <div id="voucher-applied" class="alert alert-success" style="display:none; margin:0 0 10px; padding:10px 12px;">
        <button type="button" class="close" id="btn-remove-voucher" title="Batalkan voucher" style="opacity:.6;">&times;</button>
        <i class="fa fa-ticket"></i> <strong id="voucher-applied-kode"></strong>
        &mdash; <span id="voucher-applied-nominal"></span><br>
        Potongan: <strong id="voucher-applied-potongan">Rp 0</strong>
    </div>
    <div id="voucher-scanner" style="display:none; margin-bottom:10px;">
        <div id="voucher-reader" style="width:100%; max-width:320px;"></div>
        <button type="button" class="btn btn-default btn-xs" id="btn-stop-scan-voucher" style="margin-top:5px;"><i class="fa fa-stop"></i> Stop Kamera</button>
    </div>
    <small class="text-danger" id="voucher-error" style="display:none; margin-bottom:10px;"></small>
    <label class="m-label">Bayar / DP (Rp)</label>
    <input type="number" id="bayar" class="m-input" value="" min="0" placeholder="0" style="margin-bottom:8px; font-size:22px; font-weight:700; min-height:56px;">
    <div style="display:flex; gap:6px; margin-bottom:10px;">
        <button type="button" class="quick-nominal" data-nominal="pas">Uang Pas</button>
        <button type="button" class="quick-nominal" data-nominal="50000">50rb</button>
        <button type="button" class="quick-nominal" data-nominal="100000">100rb</button>
        <button type="button" class="quick-nominal" data-nominal="200000">200rb</button>
    </div>
    <div style="display:flex; justify-content:space-between; font-size:13px; padding:6px 0;">
        <span id="payment-balance-caption">Kembalian / Kekurangan:</span>
        <strong id="kembalian-label" class="text-brand">Rp 0</strong>
    </div>
</div>

{{-- ============ TOTAL & SIMPAN ============ --}}
<div class="total-bar" style="margin-bottom:12px;">
    <div>
        <div id="total-caption" style="font-size:11px; opacity:.85;">TOTAL</div>
        <div class="t-val" id="grand-total">Rp 0</div>
        <div id="total-note" style="font-size:11px; margin-top:3px; opacity:.9;"></div>
    </div>
    <button type="button" class="btn" id="btn-bayar" style="background:#fff; color:var(--brand); font-weight:700; font-size:16px; border-radius:24px; padding:14px 28px; min-height:52px;" disabled>
        <i class="fa fa-check"></i> BAYAR / DP
    </button>
</div>

{{-- ============ MODAL PRATINJAU NOTA ============ --}}
<div class="modal fade" id="modal-pickup-qr" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#27ae60; color:#fff;">
                <h4 class="modal-title"><i class="fa fa-check-circle"></i> Transaksi Berhasil</h4>
            </div>
            <div class="modal-body text-center" style="padding:10px;">
                <p style="margin-bottom:6px;">Kode transaksi: <strong id="pickup-qr-kode">-</strong></p>
                <iframe id="nota-preview-frame" title="Pratinjau Nota" style="width:100%; max-width:400px; height:60vh; border:1px solid #ddd; border-radius:8px; background:#fff;"></iframe>
                <p id="pickup-qr-print-status" class="text-muted" style="font-size:12px; margin:8px 0 0;"></p>
            </div>
            <div class="modal-footer" style="display:flex; gap:8px;">
                <button type="button" class="btn btn-default" id="btn-pickup-qr-batal" style="flex:1; border-radius:24px; font-weight:700;">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btn-pickup-qr-cetak" style="flex:2; border-radius:24px; font-weight:700;">
                    <i class="fa fa-print"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ============ MODAL PRODUK ============ --}}
<div class="modal fade" id="modal-product" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--brand); color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title" id="modal-product-title">Pilih Produk</h4>
            </div>
            <div class="modal-body" style="padding:10px;">
                <div class="input-group" style="margin-bottom:10px;">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" id="modal-product-search" class="m-input" placeholder="Ketik untuk mencari..." autocomplete="off">
                </div>
                <label id="lens-stock-filter-wrap" style="display:none; font-weight:400; margin-bottom:10px;">
                    <input type="checkbox" id="lens-show-all-sizes"> Tampilkan semua ukuran stok di cabang
                </label>
                <div id="modal-product-list">
                    <div class="m-empty"><i class="fa fa-search"></i>Ketik nama/kode produk untuk mencari</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-lensa-gosok" tabindex="-1" role="dialog" aria-labelledby="modal-lensa-gosok-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#e67e22; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title" id="modal-lensa-gosok-title">Input Lensa Gosok</h4>
            </div>
            <div class="modal-body" style="padding:12px;">
                <label class="m-label">Merk lensa</label>
                <input type="text" id="gosok-merk" class="m-input" placeholder="Contoh: Essilor, Hoya" required>
                <label class="m-label" style="margin-top:8px;">Jenis lensa</label>
                <select id="gosok-type" class="m-select">
                    <option value="">Pilih jenis (opsional)</option>
                    <option value="Single Vision">Single Vision</option>
                    <option value="Progressive">Progressive</option>
                    <option value="Bifocal">Bifocal</option>
                    <option value="Trifocal">Trifocal</option>
                    <option value="Reading">Reading</option>
                    <option value="Computer">Computer</option>
                </select>
                <div style="display:flex; gap:16px; margin-top:12px;">
                    <label style="font-weight:600;"><input type="checkbox" id="gosok-use-od" checked> Ukuran kanan (OD)</label>
                    <label style="font-weight:600;"><input type="checkbox" id="gosok-use-os" checked> Ukuran kiri (OS)</label>
                </div>
                <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:8px; margin-top:10px;">
                    <div id="gosok-od-fields">
                        <strong>OD (Kanan)</strong>
                        <label class="m-label" style="margin-top:6px;">SPH</label><input type="text" id="gosok-od-sph" class="m-input" placeholder="SPH kanan">
                        <label class="m-label" style="margin-top:6px;">CYL</label><input type="text" id="gosok-od-cyl" class="m-input" placeholder="CYL kanan">
                        <label class="m-label" style="margin-top:6px;">Axis</label><input type="text" id="gosok-od-axis" class="m-input" placeholder="Axis kanan">
                        <label class="m-label" style="margin-top:6px;">ADD</label><input type="text" id="gosok-od-add" class="m-input" placeholder="ADD kanan">
                    </div>
                    <div id="gosok-os-fields">
                        <strong>OS (Kiri)</strong>
                        <label class="m-label" style="margin-top:6px;">SPH</label><input type="text" id="gosok-os-sph" class="m-input" placeholder="SPH kiri">
                        <label class="m-label" style="margin-top:6px;">CYL</label><input type="text" id="gosok-os-cyl" class="m-input" placeholder="CYL kiri">
                        <label class="m-label" style="margin-top:6px;">Axis</label><input type="text" id="gosok-os-axis" class="m-input" placeholder="Axis kiri">
                        <label class="m-label" style="margin-top:6px;">ADD</label><input type="text" id="gosok-os-add" class="m-input" placeholder="ADD kiri">
                    </div>
                </div>
                <label class="m-label" style="margin-top:8px;">Coating (opsional)</label>
                <input type="text" id="gosok-coating" class="m-input" placeholder="Coating">
                <label class="m-label" style="margin-top:8px;">Harga jual (Rp)</label>
                <input type="number" id="gosok-price" class="m-input" min="1" step="1000" placeholder="Harga lensa">
                <label class="m-label" style="margin-top:8px;">Jumlah</label>
                <input type="number" id="gosok-quantity" class="m-input" min="1" value="1">
                <label class="m-label" style="margin-top:8px;">Catatan (opsional)</label>
                <textarea id="gosok-note" class="m-input" rows="2" placeholder="Catatan tambahan"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="addLensGosokToCart()"><i class="fa fa-plus"></i> Tambah ke keranjang</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-patient-camera" tabindex="-1" role="dialog" aria-labelledby="patient-camera-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--brand); color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title" id="patient-camera-title">Ambil Foto Pasien</h4>
            </div>
            <div class="modal-body" style="padding:12px;">
                <video id="patient-camera-video" autoplay playsinline style="display:block; width:100%; max-height:65vh; background:#111; border-radius:6px; object-fit:contain;"></video>
                <canvas id="patient-camera-canvas" style="display:none;"></canvas>
            </div>
            <div class="modal-footer" style="display:flex; gap:8px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-capture-patient-photo" onclick="capturePatientPhoto()" disabled>
                    <i class="fa fa-camera"></i> Jepret
                </button>
            </div>
        </div>
    </div>
</div>

<form id="form-hidden" style="display:none;">
    <input type="hidden" name="kode_penjualan" id="f-kode">
    <input type="hidden" name="tanggal" id="f-tanggal" value="{{ now()->toDateString() }}">
    <input type="hidden" name="items" id="f-items">
    <input type="hidden" name="total" id="f-total">
    <input type="hidden" name="diskon" id="f-diskon">
    <input type="hidden" name="voucher_kode" id="f-voucher-kode">
    <input type="hidden" name="bayar" id="f-bayar">
    <input type="hidden" name="kekurangan" id="f-kekurangan">
    <input type="hidden" name="metode_pembayaran" id="f-metode" value="cash">
    <input type="hidden" name="bank_transfer" id="f-bank">
    <input type="hidden" name="jenis_transaksi" id="f-jenis" value="Stock">
    <input type="hidden" name="pasien_id" id="f-pasien-id">
    <input type="hidden" name="pasien_name" id="f-pasien-name">
    <input type="hidden" name="buat_pasien_baru" id="f-buat-pasien-baru">
    <input type="hidden" name="nohp" id="f-pasien-nohp">
    <input type="hidden" name="service_type" id="f-pasien-service-type">
    <input type="hidden" name="alamat" id="f-pasien-alamat">
    <input type="hidden" name="no_bpjs" id="f-pasien-no-bpjs">
    <input type="hidden" name="dokter_id" id="f-dokter-id">
    <input type="hidden" name="dokter_manual" id="f-dokter-manual">
    <input type="hidden" name="simpan_resep" id="f-simpan-resep">
    <input type="hidden" name="signature_bpjs" id="f-signature-bpjs">
    <input type="hidden" name="od_sph" id="f-od-sph">
    <input type="hidden" name="od_cyl" id="f-od-cyl">
    <input type="hidden" name="od_axis" id="f-od-axis">
    <input type="hidden" name="os_sph" id="f-os-sph">
    <input type="hidden" name="os_cyl" id="f-os-cyl">
    <input type="hidden" name="os_axis" id="f-os-axis">
    <input type="hidden" name="add_kanan" id="f-add-kanan">
    <input type="hidden" name="add_kiri" id="f-add-kiri">
    <input type="hidden" name="pd" id="f-pd">
</form>
@endsection

@push('scripts')
@include('print-agent._client')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let cart = [];
let currentType = 'frame';
let searchTimer = null;
let patientSearchTimer = null;
let loadedPrescriptionSignature = '';
let selectedPatientPhoto = null;
let patientPhotoPreviewUrl = null;
let patientCameraStream = null;

const ROUTES = {
    search: '{{ route("penjualan.search_product") }}',
    lensaStok: '{{ route("penjualan.lensa-stok") }}',
    pasienDetails: '{{ url("/pasien") }}',
    store: '{{ route("penjualan.store") }}',
    pasienSearch: '{{ route("kasir-mobile.pasien-search") }}',
    voucherCheck: '{{ route("voucher.check") }}',
    cetakHalf: '{{ url("/penjualan") }}'
};

$(function() {
    initMobileSignature();

    $('#foto_pasien').on('change', function() {
        const file = this.files && this.files[0];
        if (!file) return;

        setPatientPhoto(file, file.name);
    });

    $('#modal-patient-camera').on('hidden.bs.modal', stopPatientCamera);

    $('#clear-bpjs-signature').on('click', clearMobileSignature);

    // pencarian cepat di halaman utama
    $('#quick-search').on('input', function() {
        const q = $(this).val().trim();
        clearTimeout(searchTimer);
        if (q.length < 2) { $('#quick-search-results').empty(); return; }
        searchTimer = setTimeout(() => quickSearch(q), 350);
    });

    $('#pasien-search').on('focus', function() {
        if (!$(this).val().trim()) searchPatients('');
    }).on('input', function() {
        const query = $(this).val().trim();
        const selectedName = $(this).data('selected-name');
        if (selectedName && query !== selectedName) {
            $(this).removeData('selected-name');
            $('#pasien_id').val('').trigger('change');
        }
        clearTimeout(patientSearchTimer);
        if (query.length < 2) {
            $('#pasien-search-results').hide().empty();
            return;
        }
        patientSearchTimer = setTimeout(() => searchPatients(query), 300);
    });

    $('#dokter_id').on('change', function() {
        if ($(this).val()) $('#dokter_manual').val('');
    });

    $('#dokter_manual').on('input', function() {
        if ($(this).val().trim()) $('#dokter_id').val('');
    });

    // pencarian di dalam modal
    $('#modal-product-search').on('input', function() {
        const q = $(this).val().trim();
        clearTimeout(searchTimer);
        if (q.length < 2) {
            if (currentType === 'lensa' && q.length === 0) {
                modalSearch('');
            } else {
                renderModalEmpty();
            }
            return;
        }
        searchTimer = setTimeout(() => modalSearch(q), 350);
    });

    $('#lens-show-all-sizes').on('change', function() {
        if (currentType === 'lensa') modalSearch($('#modal-product-search').val().trim());
    });

    $('#gosok-use-od, #gosok-use-os').on('change', function() {
        $('#gosok-od-fields').toggle($('#gosok-use-od').is(':checked'));
        $('#gosok-os-fields').toggle($('#gosok-use-os').is(':checked'));
    });

    $('.rx-input').on('input', function() {
        const q = $('#modal-product-search').val().trim();
        if (currentType === 'lensa' && q.length >= 2) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => modalSearch(q), 250);
        }
    });

    $('#pasien_id').on('change', function() {
        const pasienId = $(this).val();
        $('#pasien-manual-wrap').toggle(!pasienId);
        $('#pasien-terdaftar-wrap').hide();
        $('#foto_pasien').val('');
        selectedPatientPhoto = null;
        $('#foto-pasien-filename').text('');
        if (patientPhotoPreviewUrl) URL.revokeObjectURL(patientPhotoPreviewUrl);
        patientPhotoPreviewUrl = null;
        $('#foto-pasien-preview').hide().attr('src', '');
        toggleBpjsCapture(false);
        if (!pasienId) {
            $('#pasien-terdaftar-service').text('-');
            clearPrescription();
            loadedPrescriptionSignature = '';
            toggleBpjsCapture(isBpjsService($('#pasien_service_type').val()));
            $('#resep-source').text('Pasien baru');
            $('#resep-status').text('Resep yang diisi akan disimpan bersama data pasien.');
            updateTotals();
            return;
        }

        $('#pasien-terdaftar-service').text('-');
        updateTotals();
        clearPrescription();
        $('#resep-source').text('Memuat resep...');
        $.get(ROUTES.pasienDetails + '/' + encodeURIComponent(pasienId) + '/details')
            .done(function(patient) {
                $('#pasien-terdaftar-alamat').text(patient.alamat || '-');
                $('#pasien-terdaftar-service').text(patient.service_type || '-');
                $('#pasien-terdaftar-nohp').text(patient.nohp || '-');
                $('#pasien-terdaftar-bpjs').text(patient.no_bpjs || '-');
                $('#pasien-terdaftar-wrap').show();
                updateTotals();
                if (patient.foto_pasien) {
                    $('#foto-pasien-preview')
                        .attr('src', ROUTES.pasienDetails + '/' + encodeURIComponent(pasienId) + '/foto')
                        .show();
                }
                toggleBpjsCapture(isBpjsService(patient.service_type));
                const prescription = patient.prescriptions && patient.prescriptions.length
                    ? patient.prescriptions[0]
                    : null;
                fillPrescription(prescription || {});
                loadedPrescriptionSignature = prescriptionFormSignature();
                $('#resep-source').text(prescription ? 'Resep terbaru pasien terpilih' : 'Belum ada resep tersimpan');
                $('#resep-status').text(prescription ? 'Pencarian lensa mengikuti resep pasien terpilih.' : 'Isi resep untuk memfilter pencarian lensa.');
            })
            .fail(function() {
                toggleBpjsCapture(false);
                clearPrescription();
                $('#resep-source').text('Resep tidak dapat dimuat');
                $('#resep-status').text('Periksa koneksi, lalu pilih pasien kembali.');
                updateTotals();
            });
    });

    $('#pasien_service_type').on('change', function() {
        if (!$('#pasien_id').val()) {
            toggleBpjsCapture(isBpjsService($(this).val()));
        }
        updateTotals();
    });

    $('.pay-chip').on('click', function() {
        $('.pay-chip').removeClass('active');
        $(this).addClass('active');
        $('#f-metode').val($(this).data('pay'));
        $('#bank-wrap').toggle($(this).data('pay') === 'transfer');
    });

    $('.quick-nominal').on('click', function() {
        const n = $(this).data('nominal');
        $('#bayar').val(n === 'pas' ? grandTotal() : n).trigger('input');
    });

    $('#diskon, #bayar').on('input', updateTotals);
    $('#btn-bayar').on('click', submitTransaction);

    $('#btn-apply-voucher').on('click', function() {
        applyVoucher($('#voucher_input').val());
    });
    $('#voucher_input').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyVoucher($(this).val());
        }
    });
    $('#btn-scan-voucher').on('click', startVoucherScan);
    $('#btn-stop-scan-voucher').on('click', stopVoucherScan);
    $('#btn-remove-voucher').on('click', function() {
        setAppliedVoucher(null);
    });
});

function setPatientPhoto(file, filename) {
    selectedPatientPhoto = file;
    if (patientPhotoPreviewUrl) URL.revokeObjectURL(patientPhotoPreviewUrl);
    patientPhotoPreviewUrl = URL.createObjectURL(file);
    $('#foto-pasien-filename').text(filename);
    $('#foto-pasien-preview').attr('src', patientPhotoPreviewUrl).show();
}

async function openPatientCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        toast('Kamera perlu izin browser dan koneksi HTTPS. Buka halaman melalui HTTPS, lalu izinkan akses kamera.', false);
        return;
    }

    $('#btn-capture-patient-photo').prop('disabled', true);
    $('#modal-patient-camera').modal('show');

    try {
        patientCameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        });
        const video = document.getElementById('patient-camera-video');
        video.srcObject = patientCameraStream;
        await video.play();
        $('#btn-capture-patient-photo').prop('disabled', false);
    } catch (error) {
        stopPatientCamera();
        $('#modal-patient-camera').modal('hide');
        toast('Kamera tidak bisa dibuka. Periksa izin kamera dan pastikan halaman menggunakan HTTPS.', false);
    }
}

function stopPatientCamera() {
    if (patientCameraStream) {
        patientCameraStream.getTracks().forEach(function(track) { track.stop(); });
        patientCameraStream = null;
    }
    const video = document.getElementById('patient-camera-video');
    if (video) video.srcObject = null;
}

function capturePatientPhoto() {
    const video = document.getElementById('patient-camera-video');
    const canvas = document.getElementById('patient-camera-canvas');
    if (!video.videoWidth || !video.videoHeight) {
        toast('Kamera belum siap. Coba lagi sebentar.', false);
        return;
    }

    const maxDimension = 1280;
    const scale = Math.min(1, maxDimension / Math.max(video.videoWidth, video.videoHeight));
    canvas.width = Math.round(video.videoWidth * scale);
    canvas.height = Math.round(video.videoHeight * scale);
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(function(blob) {
        if (!blob) {
            toast('Foto gagal diambil. Silakan coba lagi.', false);
            return;
        }
        const filename = 'foto-pasien-' + Date.now() + '.jpg';
        setPatientPhoto(new File([blob], filename, { type: 'image/jpeg' }), filename);
        $('#modal-patient-camera').modal('hide');
    }, 'image/jpeg', 0.75);
}

function isBpjsService(serviceType) {
    return /^bpjs/i.test(String(serviceType || '').trim());
}

function toggleBpjsCapture(show) {
    $('#pasien-no-bpjs-wrap, #pasien-terdaftar-bpjs-wrap, #foto-pasien-wrap, #bpjs-capture-wrap').toggle(show);
    if (!show) {
        $('#pasien_no_bpjs, #foto_pasien').val('');
        selectedPatientPhoto = null;
        $('#foto-pasien-filename').text('');
        if (patientPhotoPreviewUrl) URL.revokeObjectURL(patientPhotoPreviewUrl);
        patientPhotoPreviewUrl = null;
        $('#foto-pasien-preview').hide().attr('src', '');
        clearBpjsCapture();
    }
}

function clearBpjsCapture() {
    clearMobileSignature();
}

function initMobileSignature() {
    const canvas = document.getElementById('bpjs-signature-canvas');
    if (!canvas) return;

    const context = canvas.getContext('2d');
    let drawing = false;
    context.lineWidth = 4;
    context.lineCap = 'round';
    context.lineJoin = 'round';
    context.strokeStyle = '#17202a';

    const point = function(event) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (event.clientX - rect.left) * canvas.width / rect.width,
            y: (event.clientY - rect.top) * canvas.height / rect.height
        };
    };

    canvas.addEventListener('pointerdown', function(event) {
        event.preventDefault();
        drawing = true;
        canvas.setPointerCapture(event.pointerId);
        const position = point(event);
        context.beginPath();
        context.moveTo(position.x, position.y);
        context.lineTo(position.x + 0.1, position.y + 0.1);
        context.stroke();
    });

    canvas.addEventListener('pointermove', function(event) {
        if (!drawing) return;
        event.preventDefault();
        const position = point(event);
        context.lineTo(position.x, position.y);
        context.stroke();
    });

    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function(name) {
        canvas.addEventListener(name, function() {
            if (drawing) $('#f-signature-bpjs').val(canvas.toDataURL('image/png'));
            drawing = false;
            context.beginPath();
        });
    });

    clearMobileSignature();
}

function clearMobileSignature() {
    const canvas = document.getElementById('bpjs-signature-canvas');
    if (canvas) {
        const context = canvas.getContext('2d');
        context.clearRect(0, 0, canvas.width, canvas.height);
    }
    $('#f-signature-bpjs').val('');
}

/* ================= PENCARIAN PRODUK ================= */
function quickSearch(q) {
    $.get(ROUTES.search, { q: q }).done(function(products) {
        const $box = $('#quick-search-results').empty();
        if (!products.length) {
            $box.html('<div class="m-empty" style="padding:12px;">Tidak ditemukan</div>');
            return;
        }
        products.forEach(p => $box.append(resultItemHtml(p)));
    });
}

function prescriptionSearchParams() {
    return {
        od_sph: $('#rx-od-sph').val(), od_cyl: $('#rx-od-cyl').val(), od_axis: $('#rx-od-axis').val(),
        os_sph: $('#rx-os-sph').val(), os_cyl: $('#rx-os-cyl').val(), os_axis: $('#rx-os-axis').val(),
        add_kanan: $('#rx-add-kanan').val(), add_kiri: $('#rx-add-kiri').val()
    };
}

function fillPrescription(prescription) {
    $('#rx-od-sph').val(prescription.od_sph || '');
    $('#rx-od-cyl').val(prescription.od_cyl || '');
    $('#rx-od-axis').val(prescription.od_axis || '');
    $('#rx-os-sph').val(prescription.os_sph || '');
    $('#rx-os-cyl').val(prescription.os_cyl || '');
    $('#rx-os-axis').val(prescription.os_axis || '');
    $('#rx-add-kanan').val(prescription.add_kanan || prescription.add || '');
    $('#rx-add-kiri').val(prescription.add_kiri || prescription.add || '');
    $('#rx-pd').val(prescription.pd || '');
}

function clearPrescription() {
    fillPrescription({});
}

function prescriptionFormSignature() {
    return JSON.stringify([
        '#rx-od-sph', '#rx-od-cyl', '#rx-od-axis', '#rx-os-sph', '#rx-os-cyl',
        '#rx-os-axis', '#rx-add-kanan', '#rx-add-kiri', '#rx-pd'
    ].map(selector => $(selector).val().trim()));
}

function openProductModal(type) {
    currentType = type;
    $('#modal-product-title').text('Pilih ' + (type === 'frame' ? 'Frame' : type === 'lensa' ? 'Lensa' : 'Aksesoris'));
    $('#modal-product-search').val('');
    $('#lens-stock-filter-wrap').toggle(type === 'lensa');
    $('#lens-show-all-sizes').prop('checked', false);
    $('#modal-product').modal('show');
    if (type === 'lensa') {
        modalSearch('');
    } else {
        renderModalEmpty();
    }
    setTimeout(() => $('#modal-product-search').focus(), 400);
}

function openLensGosokModal() {
    const fields = {
        'gosok-od-sph': '#rx-od-sph', 'gosok-od-cyl': '#rx-od-cyl',
        'gosok-od-axis': '#rx-od-axis', 'gosok-od-add': '#rx-add-kanan',
        'gosok-os-sph': '#rx-os-sph', 'gosok-os-cyl': '#rx-os-cyl',
        'gosok-os-axis': '#rx-os-axis', 'gosok-os-add': '#rx-add-kiri'
    };
    Object.keys(fields).forEach(function(target) {
        $('#' + target).val($(fields[target]).val());
    });
    $('#gosok-merk, #gosok-price, #gosok-note').val('');
    $('#gosok-type, #gosok-coating').val('');
    $('#gosok-quantity').val(1);
    $('#gosok-use-od, #gosok-use-os').prop('checked', true).trigger('change');
    $('#modal-lensa-gosok').modal('show');
}

function addLensGosokToCart() {
    const merk = $('#gosok-merk').val().trim();
    const price = Number($('#gosok-price').val()) || 0;
    const quantity = Number($('#gosok-quantity').val()) || 0;
    const useOd = $('#gosok-use-od').is(':checked');
    const useOs = $('#gosok-use-os').is(':checked');
    if (!merk || price <= 0 || quantity < 1) {
        toast('Isi merk, harga, dan jumlah lensa gosok dengan benar.', false);
        return;
    }
    if (!useOd && !useOs) {
        toast('Pilih ukuran kanan (OD), kiri (OS), atau keduanya.', false);
        return;
    }

    const eyeValue = function(eye, field) {
        return eye + ': ' + ($('#gosok-' + eye.toLowerCase() + '-' + field).val().trim() || '-');
    };
    const formatEyeValues = function(field) {
        const values = [];
        if (useOd) values.push(eyeValue('OD', field));
        if (useOs) values.push(eyeValue('OS', field));
        return values.join(' ');
    };
    const item = {
        id: 'gosok_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
        type: 'lensa_gosok',
        name: 'Lensa Gosok - ' + merk,
        merk: merk,
        lensaType: $('#gosok-type').val() || '-',
        index: formatEyeValues('sph'),
        cly: formatEyeValues('cyl'),
        axis: formatEyeValues('axis'),
        add: formatEyeValues('add'),
        coating: $('#gosok-coating').val() || '-',
        catatan: $('#gosok-note').val().trim(),
        price: price,
        quantity: quantity
    };

    addToCart(item);
    $('#modal-lensa-gosok').modal('hide');
    toast('Lensa gosok ditambahkan ke keranjang');
}

function renderModalEmpty() {
    $('#modal-product-list').html('<div class="m-empty"><i class="fa fa-search"></i>Ketik nama/kode produk untuk mencari</div>');
}

function modalSearch(q) {
    const $box = $('#modal-product-list').html('<div class="m-empty" style="padding:12px;"><i class="fa fa-spinner fa-spin"></i> Mencari...</div>');

    if (currentType === 'lensa') {
        $.get(ROUTES.lensaStok, Object.assign({
            search: q,
            include_out_of_stock: 0,
            show_all_lens_sizes: $('#lens-show-all-sizes').is(':checked') ? 1 : 0
        }, prescriptionSearchParams())).done(function(res) {
            const items = (res.data || []).map(l => ({
                id: l.id, name: l.merk_lensa, price: l.harga_jual_lensa, type: 'lensa',
                index: l.index, cly: l.cly, add: l.add,
                _info: (l.kode_lensa || '') + ' · ' + (l.type || '-') + ' · idx ' + (l.index || '-') + ' · stok ' + l.stok + ' · ' + (l.branch_name || '-')
            }));
            renderModalResults($box, items);
        }).fail(function() {
            $box.html('<div class="m-empty" style="padding:12px;">Gagal memuat stok lensa. Periksa koneksi lalu coba lagi.</div>');
        });
    } else {
        $.get(ROUTES.search, { q: q }).done(function(products) {
            const items = products.filter(p => p.type === currentType).map(p => Object.assign({}, p, {
                _info: currentType === 'frame'
                    ? (p.code || '-') + ' · stok ' + (p.stock ?? 0)
                    : (p.code || p.type.toUpperCase())
            }));
            renderModalResults($box, items);
        });
    }
}

function renderModalResults($box, items) {
    $box.empty();
    if (!items.length) {
        const message = currentType === 'lensa'
            ? 'Tidak ada stok cocok di cabang ini. Coba tampilkan semua ukuran stok atau input lensa gosok.'
            : 'Tidak ditemukan';
        $box.html('<div class="m-empty" style="padding:12px;">' + message + '</div>');
        return;
    }
    items.forEach(p => $box.append(resultItemHtml(p)));
}

function resultItemHtml(p) {
    const info = p._info || (p.type.toUpperCase() + (p.index ? ' · idx ' + p.index : ''));
    return '<div class="search-result-item" data-item=\'' + JSON.stringify({
        id: p.id, name: p.name, type: p.type, price: Number(p.price) || 0,
        index: p.index || '', cly: p.cly || '', add: p.add || ''
    }).replace(/'/g, '&#39;') + '\'>'
        + '<div style="display:flex; justify-content:space-between; align-items:center;">'
        + '<div style="min-width:0; flex:1;">'
        + '<div style="font-weight:700; font-size:14px;">' + $('<div>').text(p.name).html() + '</div>'
        + '<div style="font-size:11px; color:#888;">' + $('<div>').text(info).html() + '</div>'
        + '</div>'
        + '<div style="font-weight:700; color:var(--brand); white-space:nowrap; margin-left:10px;">' + formatRupiah(p.price) + '</div>'
        + '</div></div>';
}

$(document).on('click', '.search-result-item', function() {
    const item = $(this).data('item');
    addToCart(item);
    $('#modal-product').modal('hide');
    $('#quick-search').val('');
    $('#quick-search-results').empty();
    toast(item.name + ' ditambahkan');
});

/* ================= KERANJANG ================= */
function addToCart(item) {
    const existing = cart.find(c => c.id === item.id && c.type === item.type);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({
            id: item.id, name: item.name, type: item.type,
            quantity: Number(item.quantity) || 1, price: item.price,
            index: item.index || '', cly: item.cly || '', axis: item.axis || '', add: item.add || '',
            merk: item.merk || '', lensaType: item.lensaType || '', coating: item.coating || '', catatan: item.catatan || ''
        });
    }
    renderCart();
}

function changeQty(i, delta) {
    cart[i].quantity = Math.max(1, cart[i].quantity + delta);
    renderCart();
}

function removeItem(i) {
    cart.splice(i, 1);
    renderCart();
}

function renderCart() {
    const $list = $('#cart-list').empty();
    $('#cart-count').text(cart.reduce((s, c) => s + c.quantity, 0));

    if (!cart.length) {
        $list.html('<div class="m-empty"><i class="fa fa-cart-arrow-down"></i>Keranjang masih kosong</div>');
    } else {
        cart.forEach(function(c, i) {
            $list.append(
                '<div class="cart-item">'
                + '<div class="ci-info">'
                + '<div class="ci-name">' + $('<div>').text(c.name).html() + '</div>'
                + '<div class="ci-meta">' + (c.type === 'lensa_gosok' ? 'Lensa Gosok' : c.type) + ' · ' + formatRupiah(c.price) + '</div>'
                + '</div>'
                + '<div style="display:flex; align-items:center; gap:4px;">'
                + '<button type="button" class="qty-btn" onclick="changeQty(' + i + ',-1)">−</button>'
                + '<span class="qty-val">' + c.quantity + '</span>'
                + '<button type="button" class="qty-btn" onclick="changeQty(' + i + ',1)">+</button>'
                + '</div>'
                + '<div class="ci-price" style="min-width:80px; text-align:right;">' + formatRupiah(c.price * c.quantity) + '</div>'
                + '<button type="button" class="qty-btn" style="border-color:#e74c3c; color:#e74c3c; margin-left:6px;" onclick="removeItem(' + i + ')"><i class="fa fa-trash"></i></button>'
                + '</div>'
            );
        });
    }
    updateTotals();
}

/* ================= VOUCHER ================= */
// Potongan final tetap dihitung ulang di server; ini hanya untuk tampilan.
let appliedVoucher = null;
let voucherQrScanner = null;
let voucherScanning = false;

function hitungPotonganVoucher(totalBelanja, diskonManual) {
    if (!appliedVoucher) return 0;

    totalBelanja = Math.max(0, Number(totalBelanja) || 0);
    // Voucher uang memotong dari sisa saldonya; voucher diskon dari persen total belanja.
    const potongan = appliedVoucher.jenis_nominal === 'diskon'
        ? Math.round(totalBelanja * Math.min(100, Math.max(0, Number(appliedVoucher.nominal) || 0)) / 100)
        : Math.max(0, Number(appliedVoucher.saldo) || 0);

    return Math.min(potongan, Math.max(0, totalBelanja - Math.max(0, Number(diskonManual) || 0)));
}

function showVoucherError(message) {
    $('#voucher-error').text(message).toggle(!!message);
}

function setAppliedVoucher(voucher) {
    appliedVoucher = voucher;
    $('#f-voucher-kode').val(voucher ? voucher.kode : '');
    $('#voucher-input-group').toggle(!voucher);
    $('#voucher-applied').toggle(!!voucher);
    if (voucher) {
        $('#voucher-applied-kode').text(voucher.kode);
        $('#voucher-applied-nominal').text(voucher.jenis_nominal === 'diskon'
            ? voucher.nominal_label + ' dari total belanja'
            : 'Saldo ' + voucher.saldo_label);
        $('#voucher_input').val('');
    }
    showVoucherError('');
    $('#bayar').data('user-has-changed', false);
    updateTotals();
}

function applyVoucher(kode) {
    kode = String(kode || '').trim().toUpperCase();
    if (!kode) {
        showVoucherError('Masukkan kode voucher terlebih dahulu.');
        return;
    }

    const $btn = $('#btn-apply-voucher').prop('disabled', true);
    showVoucherError('');

    $.getJSON(ROUTES.voucherCheck, { kode: kode })
        .done(function(data) {
            if (!data.status || !data.status.valid) {
                let message = 'Voucher ' + kode + ' tidak bisa dipakai: ' + (data.status ? data.status.label : 'tidak valid') + '.';
                if (data.dipakai_di) {
                    message += ' Dipakai di transaksi ' + data.dipakai_di.kode_penjualan + ' (' + data.dipakai_di.tanggal + ').';
                }
                showVoucherError(message);
                return;
            }
            setAppliedVoucher(data.voucher);
        })
        .fail(function(xhr) {
            showVoucherError((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengecek voucher.');
        })
        .always(function() {
            $btn.prop('disabled', false);
        });
}

async function stopVoucherScan() {
    if (voucherQrScanner && voucherScanning) {
        try {
            await voucherQrScanner.stop();
            voucherQrScanner.clear();
        } catch (e) {
            console.warn('Gagal menghentikan kamera voucher', e);
        }
    }
    voucherScanning = false;
    $('#voucher-scanner').hide();
}

async function startVoucherScan() {
    if (typeof Html5Qrcode === 'undefined') {
        showVoucherError('Library scanner QR gagal dimuat. Ketik kode voucher secara manual.');
        return;
    }
    if (voucherScanning) return;

    showVoucherError('');
    $('#voucher-scanner').show();
    voucherQrScanner = voucherQrScanner || new Html5Qrcode('voucher-reader');

    try {
        await voucherQrScanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 200, height: 200 } },
            function(decodedText) {
                stopVoucherScan();
                applyVoucher(decodedText);
            },
            function() {}
        );
        voucherScanning = true;
    } catch (err) {
        $('#voucher-scanner').hide();
        showVoucherError('Kamera tidak bisa dibuka. Pastikan izin kamera diberikan dan halaman dibuka lewat HTTPS/localhost.');
        console.error(err);
    }
}

/* ================= TOTAL & PEMBAYARAN ================= */
function subTotal() { return cart.reduce((s, c) => s + (c.price * c.quantity), 0); }

function selectedServiceType() {
    return $('#pasien_id').val()
        ? $('#pasien-terdaftar-service').text().trim()
        : $('#pasien_service_type').val();
}

function bpjsTotals() {
    const plafonByService = { 'BPJS I': 330000, 'BPJS II': 220000, 'BPJS III': 165000 };
    const plafon = plafonByService[selectedServiceType()] || 0;
    const eligibleTotal = cart
        .filter(item => ['frame', 'lensa', 'lensa_gosok'].includes(item.type))
        .reduce((sum, item) => sum + (Number(item.price) || 0) * item.quantity, 0);
    const accessoriesTotal = cart
        .filter(item => item.type === 'aksesoris')
        .reduce((sum, item) => sum + (Number(item.price) || 0) * item.quantity, 0);
    const additional = Math.max(0, eligibleTotal - plafon);
    const diskonManual = Number($('#diskon').val()) || 0;
    const potonganVoucher = hitungPotonganVoucher(additional + accessoriesTotal, diskonManual);
    const discount = diskonManual + potonganVoucher;
    const discountOnAdditional = Math.min(discount, additional);
    const remainingDiscount = Math.max(0, discount - discountOnAdditional);
    const payable = Math.max(0, additional - discountOnAdditional + accessoriesTotal - remainingDiscount);

    return { plafon, additional, payable, potonganVoucher };
}

function grandTotal() {
    if (isBpjsService(selectedServiceType())) return bpjsTotals().payable;
    const diskonManual = Number($('#diskon').val()) || 0;
    const potonganVoucher = hitungPotonganVoucher(subTotal(), diskonManual);
    return Math.max(0, subTotal() - diskonManual - potonganVoucher);
}

function updateTotals() {
    const gt = grandTotal();
    const bayar = Number($('#bayar').val()) || 0;
    const selisih = bayar - gt;
    const sisaPembayaran = Math.max(0, gt - bayar);
    $('#grand-total').text(formatRupiah(gt));
    let potonganVoucher = 0;
    if (isBpjsService(selectedServiceType())) {
        const totals = bpjsTotals();
        potonganVoucher = totals.potonganVoucher;
        $('#total-caption').text('TAGIHAN TAMBAHAN BPJS');
        $('#total-note').html('Biaya dasar ' + formatRupiah(totals.plafon) + ' ditanggung BPJS<br>Tambahan: ' + formatRupiah(totals.additional));
        $('#payment-balance-caption').text('Sisa tagihan / kembalian:');
    } else {
        potonganVoucher = hitungPotonganVoucher(subTotal(), Number($('#diskon').val()) || 0);
        $('#total-caption').text('TOTAL UMUM');
        $('#total-note').text('');
        $('#payment-balance-caption').text(bayar > 0 && selisih < 0 ? 'Sisa tagihan setelah DP:' : 'Kembalian / kekurangan:');
    }
    $('#voucher-applied-potongan').text(formatRupiah(potonganVoucher));
    if (bayar > 0 && sisaPembayaran > 0) {
        $('#total-note').append('<br><strong style="font-size:15px;">Sisa pembayaran: ' + formatRupiah(sisaPembayaran) + '</strong>');
    }
    $('#kembalian-label').text((selisih >= 0 ? 'Kembali ' : 'Kurang ') + formatRupiah(Math.abs(selisih)));
    $('#kembalian-label').css('color', selisih >= 0 ? '#27ae60' : '#c0392b');
    $('#btn-bayar').prop('disabled', !(cart.length > 0 && (gt === 0 || bayar > 0)));
}

/* ================= SUBMIT ================= */
function submitTransaction() {
    const pasienId = $('#pasien_id').val();
    const pasienName = $('#pasien_name').val().trim();

    if (!cart.length) { toast('Keranjang masih kosong', false); return; }
    if (!pasienId && !pasienName) { toast('Isi nama pasien dulu', false); return; }
    if (!pasienId && !$('#pasien_nohp').val().trim()) { toast('Isi nomor HP pasien dulu', false); return; }
    if (!pasienId && !$('#pasien_service_type').val()) { toast('Pilih jenis layanan pasien dulu', false); return; }

    const gt = grandTotal();
    const bayar = Number($('#bayar').val()) || 0;
    if (gt > 0 && bayar <= 0) {
        toast('Masukkan nominal pembayaran atau DP terlebih dahulu.', false);
        return;
    }

    $('#f-kode').val('MLT-' + Date.now());
    $('#f-items').val(JSON.stringify(cart));
    $('#f-total').val(gt);
    $('#f-diskon').val(Number($('#diskon').val()) || 0);
    $('#f-bayar').val(bayar);
    $('#f-kekurangan').val(gt - bayar);
    $('#f-bank').val($('#bank_transfer').val());
    $('#f-pasien-id').val(pasienId || '');
    $('#f-pasien-name').val(pasienId ? '' : pasienName);
    $('#f-buat-pasien-baru').val(pasienId ? '' : '1');
    $('#f-pasien-nohp').val(pasienId ? '' : $('#pasien_nohp').val());
    $('#f-pasien-service-type').val(pasienId ? '' : $('#pasien_service_type').val());
    $('#f-pasien-alamat').val(pasienId ? '' : $('#pasien_alamat').val());
    $('#f-pasien-no-bpjs').val(pasienId ? '' : $('#pasien_no_bpjs').val());
    $('#f-dokter-id').val($('#dokter_id').val());
    $('#f-dokter-manual').val($('#dokter_manual').val().trim());
    $('#f-simpan-resep').val(!pasienId || prescriptionFormSignature() !== loadedPrescriptionSignature ? '1' : '');
    $('#f-od-sph').val($('#rx-od-sph').val());
    $('#f-od-cyl').val($('#rx-od-cyl').val());
    $('#f-od-axis').val($('#rx-od-axis').val());
    $('#f-os-sph').val($('#rx-os-sph').val());
    $('#f-os-cyl').val($('#rx-os-cyl').val());
    $('#f-os-axis').val($('#rx-os-axis').val());
    $('#f-add-kanan').val($('#rx-add-kanan').val());
    $('#f-add-kiri').val($('#rx-add-kiri').val());
    $('#f-pd').val($('#rx-pd').val());
    $('#f-jenis').val(cart.some(item => item.type === 'lensa_gosok') ? 'Gosok' : 'Stock');

    const $btn = $('#btn-bayar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> PROSES...');
    const formData = new FormData($('#form-hidden')[0]);
    if (selectedPatientPhoto) {
        formData.append('foto_pasien', selectedPatientPhoto);
        if (isBpjsService(selectedServiceType())) {
            formData.append('photo_bpjs', selectedPatientPhoto);
        }
    }

    $.ajax({
        url: ROUTES.store,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false
    }).done(function(res) {
        toast('Transaksi berhasil disimpan!');
        const idMatch = res.redirect_url ? res.redirect_url.match(/penjualan\/(\d+)/) : null;
        const penjualanId = idMatch ? idMatch[1] : null;

        if (penjualanId) {
            showPickupQrModal(res, penjualanId);
        } else {
            setTimeout(function() {
                window.location.href = '{{ route("kasir-mobile.riwayat") }}';
            }, 400);
        }
    }).fail(function(xhr) {
        const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.errors))
            ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON.errors))
            : 'Gagal menyimpan transaksi';
        toast(msg, false);
        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> BAYAR / DP');
    });
}

/* ================= PRATINJAU NOTA ================= */
function showPickupQrModal(res, penjualanId) {
    const keRiwayat = function () { window.location.href = '{{ route("kasir-mobile.riwayat") }}'; };
    const $cetak = $('#btn-pickup-qr-cetak');

    $('#pickup-qr-kode').text(res.kode_penjualan || res.barcode || '-');
    $('#nota-preview-frame').attr('src', ROUTES.cetakHalf + '/' + penjualanId + '/cetak-half?embed=1');
    $('#pickup-qr-print-status').text('');
    $cetak.prop('disabled', false).html('<i class="fa fa-print"></i> Cetak');
    $('#modal-pickup-qr').modal('show');

    $('#btn-pickup-qr-batal').off('click').on('click', function() {
        $('#modal-pickup-qr').modal('hide');
        keRiwayat();
    });

    $cetak.off('click').on('click', function() {
        $cetak.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');
        kirimPrintPc(penjualanId, 'half').finally(function () {
            $('#pickup-qr-print-status').html('<i class="fa fa-info-circle"></i> Nota dikirim ke printer PC.');
            setTimeout(keRiwayat, 1500);
        });
    });
}

function searchPatients(query) {
    $.get(ROUTES.pasienSearch, { q: query }).done(function(patients) {
        const $results = $('#pasien-search-results').empty();
        if (!patients.length) {
            $results.append($('<div>').text('Pasien tidak ditemukan. Gunakan input manual jika pasien baru.').css({ padding: '10px 12px', color: '#777' }));
            $results.show();
            return;
        }

        patients.forEach(function(patient) {
            const $option = $('<button type="button">');
            $('<strong>').text(patient.nama_pasien).appendTo($option);
            $('<div>').text([patient.nohp, patient.service_type].filter(Boolean).join(' · ')).css({ fontSize: '12px', color: '#777' }).appendTo($option);
            $option.on('click', function() {
                $('#pasien-search').val(patient.nama_pasien).data('selected-name', patient.nama_pasien);
                $('#pasien-search-results').hide().empty();
                $('#pasien_id').val(patient.id_pasien).trigger('change');
            });
            $results.append($option);
        });
        $results.show();
    }).fail(function() {
        $('#pasien-search-results').empty().append(
            $('<div>').text('Pencarian pasien gagal. Periksa koneksi lalu coba lagi.').css({ padding: '10px 12px', color: '#c0392b' })
        ).show();
    });
}
</script>
@endpush
