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
    }
    .total-bar .t-val { font-size: 20px; font-weight: 700; }
    .pay-chip {
        flex: 1; padding: 10px; text-align: center; border-radius: 10px;
        border: 1.5px solid #ddd; background: #fff; font-weight: 700; font-size: 13px;
    }
    .pay-chip.active { background: var(--brand); color: #fff; border-color: var(--brand); }
    .search-result-item {
        padding: 12px; border-bottom: 1px solid #f0f0f0; cursor: pointer;
    }
    .search-result-item:active { background: #faf0f3; }
    .quick-nominal {
        flex: 1; padding: 10px 4px; border-radius: 10px; border: 1.5px solid #ddd;
        background: #fff; font-weight: 700; font-size: 12px; text-align: center;
    }
</style>

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

{{-- ============ PASIEN ============ --}}
<div class="m-card">
    <h4><i class="fa fa-user text-brand"></i> Pasien</h4>
    <label class="m-label">Pilih Pasien Terdaftar</label>
    <select id="pasien_id" class="m-select" style="margin-bottom:8px;">
        <option value="">— Pasien Baru / Manual —</option>
        @foreach($pasienList as $p)
            <option value="{{ $p->id_pasien }}">{{ $p->nama_pasien }}{{ $p->nohp ? ' · ' . $p->nohp : '' }}{{ $p->service_type ? ' [' . $p->service_type . ']' : '' }}</option>
        @endforeach
    </select>
    <div id="pasien-manual-wrap">
        <label class="m-label">Nama Pasien (jika tidak terdaftar)</label>
        <input type="text" id="pasien_name" class="m-input" placeholder="Nama pasien...">
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
    <label class="m-label">Bayar (Rp)</label>
    <input type="number" id="bayar" class="m-input" value="" min="0" placeholder="0" style="margin-bottom:8px; font-size:18px; font-weight:700;">
    <div style="display:flex; gap:6px; margin-bottom:10px;">
        <button type="button" class="quick-nominal" data-nominal="pas">Uang Pas</button>
        <button type="button" class="quick-nominal" data-nominal="50000">50rb</button>
        <button type="button" class="quick-nominal" data-nominal="100000">100rb</button>
        <button type="button" class="quick-nominal" data-nominal="200000">200rb</button>
    </div>
    <div style="display:flex; justify-content:space-between; font-size:13px; padding:6px 0;">
        <span>Kembalian / Kekurangan:</span>
        <strong id="kembalian-label" class="text-brand">Rp 0</strong>
    </div>
</div>

{{-- ============ TOTAL & SIMPAN ============ --}}
<div class="total-bar" style="margin-bottom:12px;">
    <div>
        <div style="font-size:11px; opacity:.85;">TOTAL</div>
        <div class="t-val" id="grand-total">Rp 0</div>
    </div>
    <button type="button" class="btn" id="btn-bayar" style="background:#fff; color:var(--brand); font-weight:700; border-radius:24px; padding:10px 24px;" disabled>
        <i class="fa fa-check"></i> BAYAR
    </button>
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
                <div id="modal-product-list">
                    <div class="m-empty"><i class="fa fa-search"></i>Ketik nama/kode produk untuk mencari</div>
                </div>
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
    <input type="hidden" name="bayar" id="f-bayar">
    <input type="hidden" name="kekurangan" id="f-kekurangan">
    <input type="hidden" name="metode_pembayaran" id="f-metode" value="cash">
    <input type="hidden" name="bank_transfer" id="f-bank">
    <input type="hidden" name="jenis_transaksi" id="f-jenis" value="Stock">
    <input type="hidden" name="pasien_id" id="f-pasien-id">
    <input type="hidden" name="pasien_name" id="f-pasien-name">
</form>
@endsection

@push('scripts')
<script>
let cart = [];
let currentType = 'frame';
let searchTimer = null;

const ROUTES = {
    search: '{{ route("penjualan.search_product") }}',
    lensaStok: '{{ route("penjualan.lensa-stok") }}',
    store: '{{ route("penjualan.store") }}',
    cetakHalf: '{{ url("/penjualan") }}'
};

$(function() {
    // pencarian cepat di halaman utama
    $('#quick-search').on('input', function() {
        const q = $(this).val().trim();
        clearTimeout(searchTimer);
        if (q.length < 2) { $('#quick-search-results').empty(); return; }
        searchTimer = setTimeout(() => quickSearch(q), 350);
    });

    // pencarian di dalam modal
    $('#modal-product-search').on('input', function() {
        const q = $(this).val().trim();
        clearTimeout(searchTimer);
        if (q.length < 2) { renderModalEmpty(); return; }
        searchTimer = setTimeout(() => modalSearch(q), 350);
    });

    $('#pasien_id').on('change', function() {
        $('#pasien-manual-wrap').toggle(!$(this).val());
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
});

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

function openProductModal(type) {
    currentType = type;
    $('#modal-product-title').text('Pilih ' + (type === 'frame' ? 'Frame' : type === 'lensa' ? 'Lensa' : 'Aksesoris'));
    $('#modal-product-search').val('');
    renderModalEmpty();
    $('#modal-product').modal('show');
    setTimeout(() => $('#modal-product-search').focus(), 400);
}

function renderModalEmpty() {
    $('#modal-product-list').html('<div class="m-empty"><i class="fa fa-search"></i>Ketik nama/kode produk untuk mencari</div>');
}

function modalSearch(q) {
    const $box = $('#modal-product-list').html('<div class="m-empty" style="padding:12px;"><i class="fa fa-spinner fa-spin"></i> Mencari...</div>');

    if (currentType === 'lensa') {
        $.get(ROUTES.lensaStok, { search: q, include_out_of_stock: 0 }).done(function(res) {
            const items = (res.data || []).map(l => ({
                id: l.id, name: l.merk_lensa, price: l.harga_jual_lensa, type: 'lensa',
                index: l.index, cly: l.cly, add: l.add,
                _info: (l.kode_lensa || '') + ' · ' + (l.type || '-') + ' · idx ' + (l.index || '-') + ' · stok ' + l.stok + ' · ' + (l.branch_name || '-')
            }));
            renderModalResults($box, items);
        });
    } else {
        $.get(ROUTES.search, { q: q }).done(function(products) {
            const items = products.filter(p => p.type === currentType);
            renderModalResults($box, items);
        });
    }
}

function renderModalResults($box, items) {
    $box.empty();
    if (!items.length) {
        $box.html('<div class="m-empty" style="padding:12px;">Tidak ditemukan</div>');
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
            quantity: 1, price: item.price,
            index: item.index || '', cly: item.cly || '', add: item.add || ''
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
                + '<div class="ci-meta">' + c.type + ' · ' + formatRupiah(c.price) + '</div>'
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

/* ================= TOTAL & PEMBAYARAN ================= */
function subTotal() { return cart.reduce((s, c) => s + (c.price * c.quantity), 0); }
function grandTotal() { return Math.max(0, subTotal() - (Number($('#diskon').val()) || 0)); }

function updateTotals() {
    const gt = grandTotal();
    const bayar = Number($('#bayar').val()) || 0;
    const selisih = bayar - gt;
    $('#grand-total').text(formatRupiah(gt));
    $('#kembalian-label').text((selisih >= 0 ? 'Kembali ' : 'Kurang ') + formatRupiah(Math.abs(selisih)));
    $('#kembalian-label').css('color', selisih >= 0 ? '#27ae60' : '#c0392b');
    $('#btn-bayar').prop('disabled', !(cart.length > 0 && bayar >= gt));
}

/* ================= SUBMIT ================= */
function submitTransaction() {
    const pasienId = $('#pasien_id').val();
    const pasienName = $('#pasien_name').val().trim();

    if (!cart.length) { toast('Keranjang masih kosong', false); return; }
    if (!pasienId && !pasienName) { toast('Isi nama pasien dulu', false); return; }

    const gt = grandTotal();
    const bayar = Number($('#bayar').val()) || 0;

    $('#f-kode').val('MLT-' + Date.now());
    $('#f-items').val(JSON.stringify(cart));
    $('#f-total').val(gt);
    $('#f-diskon').val(Number($('#diskon').val()) || 0);
    $('#f-bayar').val(bayar);
    $('#f-kekurangan').val(gt - bayar);
    $('#f-bank').val($('#bank_transfer').val());
    $('#f-pasien-id').val(pasienId || '');
    $('#f-pasien-name').val(pasienId ? '' : pasienName);
    $('#f-jenis').val('Stock');

    const $btn = $('#btn-bayar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> PROSES...');

    $.ajax({
        url: ROUTES.store,
        method: 'POST',
        data: new FormData($('#form-hidden')[0]),
        processData: false,
        contentType: false
    }).done(function(res) {
        toast('Transaksi berhasil disimpan!');
        if (res.redirect_url) {
            const idMatch = res.redirect_url.match(/penjualan\/(\d+)/);
            setTimeout(function() {
                if (idMatch && confirm('Transaksi tersimpan. Cetak nota sekarang?')) {
                    window.open(ROUTES.cetakHalf + '/' + idMatch[1] + '/cetak-half', '_blank');
                }
                window.location.href = '{{ route("kasir-mobile.riwayat") }}';
            }, 400);
        }
    }).fail(function(xhr) {
        const msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.errors))
            ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON.errors))
            : 'Gagal menyimpan transaksi';
        toast(msg, false);
        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> BAYAR');
    });
}
</script>
@endpush
