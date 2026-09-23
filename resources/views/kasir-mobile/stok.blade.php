@extends('kasir-mobile.layout')

@section('content')
<div class="m-card">
    <h4><i class="fa fa-search text-brand"></i> Cari Stok Barang</h4>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-search"></i></span>
        <input type="text" id="stok-search" class="m-input" placeholder="Nama / kode frame, lensa, aksesoris..." autocomplete="off">
    </div>
</div>

<div id="stok-list">
    <div class="m-card"><div class="m-empty"><i class="fa fa-cubes"></i>Memuat data stok...</div></div>
</div>
@endsection

@push('scripts')
<script>
let stokTimer = null;

$(function() {
    loadStok('');
    $('#stok-search').on('input', function() {
        clearTimeout(stokTimer);
        const q = $(this).val().trim();
        stokTimer = setTimeout(() => loadStok(q), 350);
    });
});

function loadStok(q) {
    $.get('{{ route("kasir-mobile.stok-data") }}', { q: q }).done(function(items) {
        const $box = $('#stok-list').empty();
        if (!items.length) {
            $box.html('<div class="m-card"><div class="m-empty"><i class="fa fa-inbox"></i>Barang tidak ditemukan</div></div>');
            return;
        }
        items.forEach(function(item) {
            const stokLabel = item.stok > 0
                ? '<span class="label label-success">Stok ' + item.stok + '</span>'
                : '<span class="label label-danger">Habis</span>';
            $box.append(
                '<div class="m-card" style="padding:12px 14px;">'
                + '<div style="display:flex; justify-content:space-between; align-items:flex-start;">'
                + '<div style="min-width:0; flex:1;">'
                + '<div style="font-weight:700; font-size:14px;">' + $('<div>').text(item.nama).html() + '</div>'
                + '<div style="font-size:11px; color:#888;">' + $('<div>').text(item.kode + ' · ' + item.info).html() + '</div>'
                + '<div style="font-size:11px; margin-top:3px;">'
                + '<span class="label label-primary">' + $('<div>').text(item.cabang).html() + '</span> '
                + '<span class="label label-default">' + item.tipe + '</span> ' + stokLabel
                + '</div></div>'
                + '<div style="font-weight:700; color:var(--brand); white-space:nowrap; margin-left:10px;">' + item.harga + '</div>'
                + '</div></div>'
            );
        });
    }).fail(function() {
        $('#stok-list').html('<div class="m-card"><div class="m-empty"><i class="fa fa-exclamation-triangle"></i>Gagal memuat data stok</div></div>');
    });
}
</script>
@endpush
