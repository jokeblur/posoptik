@extends('layouts.master')

@section('title', 'Buat Permintaan Stok Baru')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Permintaan Stok Antar Cabang</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('stock-transfer.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <form action="{{ route('stock-transfer.store') }}" method="POST" id="transferForm">
                @csrf
                <div class="box-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <strong>Gagal menyimpan permintaan transfer:</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from_branch_id">Dari Cabang (Sumber Stok) <span class="text-danger">*</span></label>
                                <select name="from_branch_id" id="from_branch_id" class="form-control" required>
                                    <option value="">Pilih Cabang Sumber</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Cabang yang dimintai stok</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to_branch">Ke Cabang (Tujuan)</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->branch->name }}" readonly>
                                <small class="text-muted">Stok akan masuk ke cabang Anda</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan (Opsional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Masukkan catatan tambahan untuk transfer ini..."></textarea>
                    </div>

                    <hr>

                    <h4>Produk yang diminta</h4>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="product_type">Jenis Produk</label>
                                <select id="product_type" class="form-control">
                                    <option value="">Pilih Jenis</option>
                                    <option value="frame">Frame</option>
                                    <option value="lensa">Lensa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="product_name">Produk</label>
                                <div class="input-group">
                                    <input type="text" id="product_name" class="form-control" placeholder="Belum ada produk dipilih" readonly>
                                    <input type="hidden" id="product_id">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary" id="btn-search-product" disabled>
                                            <i class="fa fa-search"></i> <span id="btn-search-label">Cari Produk</span>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="quantity">Jumlah</label>
                                <input type="number" id="quantity" class="form-control" min="1" value="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-success btn-block" onclick="addProduct()">
                                    <i class="fa fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Stok Cabang Sumber</th>
                                    <th>Jumlah Diminta</th>
                                    <th>Harga Satuan</th>
                                    <th>Total</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Products will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        <strong>Info:</strong> 
                        <ul class="mb-0">
                            <li>Gunakan form ini jika cabang Anda kekurangan stok dan ingin meminta stok dari cabang lain</li>
                            <li>Produk yang ditampilkan adalah stok yang tersedia di <strong>cabang sumber</strong> yang dipilih</li>
                            <li>Permintaan akan menunggu persetujuan admin/super admin</li>
                            <li>Stok akan berkurang dari cabang sumber dan bertambah di cabang Anda setelah disetujui</li>
                        </ul>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fa fa-save"></i> Buat Permintaan Stok
                    </button>
                    <a href="{{ route('stock-transfer.index') }}" class="btn btn-default">
                        <i class="fa fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cari Produk (Frame / Lensa) -->
<div class="modal fade" id="modal-product" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-product-title">Cari Produk</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="modal-product-source"></p>

                <div class="table-responsive" id="frame-table-wrapper" style="display:none;">
                    <table class="table table-bordered table-striped table-hover" id="table-frame-product" style="width:100%">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="table-responsive" id="lensa-table-wrapper" style="display:none;">
                    <table class="table table-bordered table-striped table-hover" id="table-lensa-product" style="width:100%">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Type</th>
                                <th>Index</th>
                                <th>Coating</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedProducts = [];
let currentProduct = null;
let previousBranchId = '';
let productDataTable = null;

const DATATABLE_LANG = { url: window.DATATABLES_LANG_URL };

$(document).ready(function() {
    previousBranchId = $('#from_branch_id').val() || '';

    $('#from_branch_id').change(function() {
        const newBranchId = $(this).val();

        if (selectedProducts.length > 0 && newBranchId !== previousBranchId) {
            if (!confirm('Mengganti cabang sumber akan menghapus daftar produk yang sudah ditambahkan. Lanjutkan?')) {
                $(this).val(previousBranchId);
                return;
            }
            selectedProducts = [];
            renderProductsTable();
            updateSubmitButton();
        }

        previousBranchId = newBranchId;
        resetProductSelection();
        updateSearchButton();
    });

    $('#product_type').change(function() {
        resetProductSelection();
        updateSearchButton();
    });

    $('#btn-search-product').click(function() {
        openProductModal();
    });

    $('#quantity').on('input', function() {
        if (!currentProduct) return;
        const val = parseInt($(this).val()) || 1;
        if (val > currentProduct.max_stock) {
            $(this).val(currentProduct.max_stock);
        }
    });

    // Klik tombol "Pilih" di dalam modal
    $(document).on('click', '.btn-pick-product', function() {
        const $btn = $(this);

        currentProduct = {
            itemable_id: $btn.data('id'),
            itemable_type: $btn.data('type-class'),
            code: $btn.data('code'),
            name: $btn.data('name'),
            max_stock: parseInt($btn.data('stok')) || 0,
            price: parseFloat($btn.data('price')) || 0
        };

        $('#product_id').val(currentProduct.itemable_id);
        $('#product_name').val(currentProduct.code + ' - ' + currentProduct.name + ' (Stok: ' + currentProduct.max_stock + ')');
        $('#quantity').attr('max', currentProduct.max_stock).val(1);

        $('#modal-product').modal('hide');
    });
});

function updateSearchButton() {
    const type = $('#product_type').val();
    const branchId = $('#from_branch_id').val();

    $('#btn-search-label').text(type === 'frame' ? 'Cari Frame' : (type === 'lensa' ? 'Cari Lensa' : 'Cari Produk'));
    $('#btn-search-product').prop('disabled', !type || !branchId);
}

function resetProductSelection() {
    currentProduct = null;
    $('#product_id').val('');
    $('#product_name').val('');
    $('#quantity').val(1).removeAttr('max');
}

function openProductModal() {
    const type = $('#product_type').val();
    const branchId = $('#from_branch_id').val();

    if (!type || !branchId) {
        alert('Silakan pilih cabang sumber dan jenis produk terlebih dahulu');
        return;
    }

    const branchName = $('#from_branch_id option:selected').text();

    $('#modal-product-title').text(type === 'frame' ? 'Cari Frame' : 'Cari Lensa');
    $('#modal-product-source').text('Menampilkan stok dari cabang: ' + branchName);

    $('#frame-table-wrapper').toggle(type === 'frame');
    $('#lensa-table-wrapper').toggle(type === 'lensa');

    $('#modal-product').modal('show');

    loadProductTable(type, branchId);
}

function loadProductTable(type, branchId) {
    const tableId = type === 'frame' ? '#table-frame-product' : '#table-lensa-product';
    const colCount = type === 'frame' ? 6 : 8;
    const $tbody = $(tableId + ' tbody');

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }

    $tbody.html('<tr><td colspan="' + colCount + '" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data...</td></tr>');

    $.get('{{ route("stock-transfer.products") }}', { type: type, branch_id: branchId })
        .done(function(products) {
            $tbody.empty();

            if (!products || products.length === 0) {
                $tbody.html('<tr><td colspan="' + colCount + '" class="text-center">Tidak ada produk dengan stok tersedia di cabang ini</td></tr>');
                return;
            }

            products.forEach(function(product) {
                const price = parseFloat(product.transfer_price) || 0;
                const typeClass = type === 'frame' ? 'App\\Models\\Frame' : 'App\\Models\\Lensa';
                const code = type === 'frame' ? product.kode_frame : product.kode_lensa;
                const name = type === 'frame' ? product.merk_frame : product.merk_lensa;

                const pickBtn = '<button type="button" class="btn btn-xs btn-primary btn-pick-product" '
                    + 'data-id="' + product.id + '" '
                    + 'data-type-class="' + typeClass + '" '
                    + 'data-code="' + $('<div>').text(code).html() + '" '
                    + 'data-name="' + $('<div>').text(name).html() + '" '
                    + 'data-stok="' + product.stok + '" '
                    + 'data-price="' + price + '">'
                    + '<i class="fa fa-check"></i> Pilih</button>';

                let row;
                if (type === 'frame') {
                    row = '<tr>'
                        + '<td>' + code + '</td>'
                        + '<td>' + name + '</td>'
                        + '<td>' + (product.jenis_frame || '-') + '</td>'
                        + '<td>' + product.stok + '</td>'
                        + '<td>Rp ' + price.toLocaleString('id-ID') + '</td>'
                        + '<td>' + pickBtn + '</td>'
                        + '</tr>';
                } else {
                    row = '<tr>'
                        + '<td>' + code + '</td>'
                        + '<td>' + name + '</td>'
                        + '<td>' + (product.type || '-') + '</td>'
                        + '<td>' + (product.index || '-') + '</td>'
                        + '<td>' + (product.coating || '-') + '</td>'
                        + '<td>' + product.stok + '</td>'
                        + '<td>Rp ' + price.toLocaleString('id-ID') + '</td>'
                        + '<td>' + pickBtn + '</td>'
                        + '</tr>';
                }

                $tbody.append(row);
            });

            productDataTable = $(tableId).DataTable({
                language: DATATABLE_LANG,
                pageLength: 10,
                lengthChange: false,
                ordering: false
            });
        })
        .fail(function() {
            $tbody.html('<tr><td colspan="' + colCount + '" class="text-center text-danger">Gagal memuat data produk</td></tr>');
        });
}

function addProduct() {
    const type = $('#product_type').val();
    const quantity = parseInt($('#quantity').val()) || 1;

    if (!type || !currentProduct) {
        alert('Silakan cari dan pilih produk terlebih dahulu');
        return;
    }

    if (quantity < 1) {
        alert('Jumlah minimal adalah 1');
        return;
    }

    if (currentProduct.price <= 0) {
        alert('Harga produk masih 0. Silakan isi harga beli/jual produk terlebih dahulu.');
        return;
    }
    
    if (quantity > currentProduct.max_stock) {
        alert('Jumlah permintaan tidak boleh melebihi stok cabang sumber (' + currentProduct.max_stock + ')');
        return;
    }
    
    // Check if product already added
    const existingIndex = selectedProducts.findIndex(p => p.itemable_id == currentProduct.itemable_id && p.itemable_type === currentProduct.itemable_type);
    if (existingIndex !== -1) {
        alert('Produk ini sudah ditambahkan ke dalam daftar permintaan');
        return;
    }
    
    selectedProducts.push({
        itemable_type: currentProduct.itemable_type,
        itemable_id: currentProduct.itemable_id,
        quantity: quantity,
        code: currentProduct.code,
        name: currentProduct.name,
        max_stock: currentProduct.max_stock,
        price: currentProduct.price,
        total: currentProduct.price * quantity
    });
    
    renderProductsTable();
    updateSubmitButton();
    resetProductSelection();
}

function removeProduct(index) {
    selectedProducts.splice(index, 1);
    renderProductsTable();
    updateSubmitButton();
}

function renderProductsTable() {
    const tbody = $('#productsTable tbody');
    tbody.empty();
    
    selectedProducts.forEach(function(product, index) {
        const row = `
            <tr>
                <td>${product.itemable_type.includes('Frame') ? 'Frame' : 'Lensa'}</td>
                <td>${product.code}</td>
                <td>${product.name}</td>
                <td>${product.max_stock}</td>
                <td>${product.quantity}</td>
                <td>Rp ${product.price.toLocaleString()}</td>
                <td>Rp ${product.total.toLocaleString()}</td>
                <td>
                    <button type="button" class="btn btn-xs btn-danger" onclick="removeProduct(${index})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updateSubmitButton() {
    $('#submitBtn').prop('disabled', selectedProducts.length === 0);
}

// Form validation
$('#transferForm').submit(function(e) {
    const $form = $(this);

    if (selectedProducts.length === 0) {
        e.preventDefault();
        alert('Silakan tambahkan minimal satu produk untuk ditransfer');
        return false;
    }

    // Bersihkan input items sebelumnya agar tidak duplikat saat submit ulang.
    $form.find('input[name^="items["]').remove();
    
    // Add hidden inputs for products
    selectedProducts.forEach(function(product, index) {
        $form.append(`
            <input type="hidden" name="items[${index}][itemable_type]" value="${product.itemable_type}">
            <input type="hidden" name="items[${index}][itemable_id]" value="${product.itemable_id}">
            <input type="hidden" name="items[${index}][quantity]" value="${product.quantity}">
        `);
    });
});
</script>
@endpush
