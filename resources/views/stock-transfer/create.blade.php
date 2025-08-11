@extends('layouts.master')

@section('title', 'Buat Transfer Stok Baru')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Form Transfer Stok Antar Cabang</h3>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from_branch">Dari Cabang</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->branch->name }}" readonly>
                                <input type="hidden" name="from_branch_id" value="{{ auth()->user()->branch_id }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to_branch_id">Ke Cabang <span class="text-danger">*</span></label>
                                <select name="to_branch_id" id="to_branch_id" class="form-control" required>
                                    <option value="">Pilih Cabang Tujuan</option>
                                    @foreach($branches as $branch)
                                        @if($branch->id != auth()->user()->branch_id)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan (Opsional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Masukkan catatan tambahan untuk transfer ini..."></textarea>
                    </div>

                    <hr>

                    <h4>Produk yang akan ditransfer</h4>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product_type">Jenis Produk</label>
                                <select id="product_type" class="form-control">
                                    <option value="">Pilih Jenis</option>
                                    <option value="frame">Frame</option>
                                    <option value="lensa">Lensa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product_id">Produk</label>
                                <select id="product_id" class="form-control">
                                    <option value="">Pilih Produk</option>
                                </select>
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
                                    <th>Stok Tersedia</th>
                                    <th>Jumlah Transfer</th>
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
                            <li>Hanya produk dengan stok > 0 yang dapat ditransfer</li>
                            <li>Transfer akan menunggu persetujuan admin/super admin</li>
                            <li>Stok akan berkurang dari cabang asal dan bertambah di cabang tujuan setelah disetujui dan diselesaikan</li>
                        </ul>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fa fa-save"></i> Buat Permintaan Transfer
                    </button>
                    <a href="{{ route('stock-transfer.index') }}" class="btn btn-default">
                        <i class="fa fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedProducts = [];

$(document).ready(function() {
    $('#product_type').change(function() {
        const type = $(this).val();
        if (type) {
            loadProducts(type);
        } else {
            $('#product_id').html('<option value="">Pilih Produk</option>');
        }
    });

    $('#product_id').change(function() {
        updateQuantityMax();
    });

    $('#quantity').on('input', function() {
        updateQuantityMax();
    });
});

function loadProducts(type) {
    $.get('{{ route("stock-transfer.products") }}', { type: type })
        .done(function(products) {
            let options = '<option value="">Pilih Produk</option>';
            products.forEach(function(product) {
                const code = product.kode_frame || product.kode_lensa;
                const name = product.merk_frame || product.merk_lensa;
                options += `<option value="${product.id}" data-stok="${product.stok}" data-price="${product.harga_beli_frame || product.harga_beli_lensa}">${code} - ${name}</option>`;
            });
            $('#product_id').html(options);
        })
        .fail(function() {
            alert('Gagal memuat data produk');
        });
}

function updateQuantityMax() {
    const selectedOption = $('#product_id option:selected');
    const maxStock = parseInt(selectedOption.data('stok')) || 0;
    const currentQuantity = parseInt($('#quantity').val()) || 1;
    
    if (currentQuantity > maxStock) {
        $('#quantity').val(maxStock);
    }
    
    $('#quantity').attr('max', maxStock);
}

function addProduct() {
    const type = $('#product_type').val();
    const productId = $('#product_id').val();
    const quantity = parseInt($('#quantity').val()) || 1;
    
    if (!type || !productId || quantity < 1) {
        alert('Silakan pilih produk dan masukkan jumlah yang valid');
        return;
    }
    
    const selectedOption = $('#product_id option:selected');
    const code = selectedOption.text().split(' - ')[0];
    const name = selectedOption.text().split(' - ')[1];
    const maxStock = parseInt(selectedOption.data('stok'));
    const price = parseFloat(selectedOption.data('price')) || 0;
    
    if (quantity > maxStock) {
        alert(`Jumlah transfer tidak boleh melebihi stok tersedia (${maxStock})`);
        return;
    }
    
    // Check if product already added
    const existingIndex = selectedProducts.findIndex(p => p.itemable_id === productId && p.itemable_type === `App\\Models\\${type.charAt(0).toUpperCase() + type.slice(1)}`);
    if (existingIndex !== -1) {
        alert('Produk ini sudah ditambahkan ke dalam daftar transfer');
        return;
    }
    
    const product = {
        itemable_type: `App\\Models\\${type.charAt(0).toUpperCase() + type.slice(1)}`,
        itemable_id: productId,
        quantity: quantity,
        code: code,
        name: name,
        max_stock: maxStock,
        price: price,
        total: price * quantity
    };
    
    selectedProducts.push(product);
    renderProductsTable();
    updateSubmitButton();
    
    // Reset form
    $('#product_type').val('');
    $('#product_id').html('<option value="">Pilih Produk</option>');
    $('#quantity').val(1);
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
    if (selectedProducts.length === 0) {
        e.preventDefault();
        alert('Silakan tambahkan minimal satu produk untuk ditransfer');
        return false;
    }
    
    // Add hidden inputs for products
    selectedProducts.forEach(function(product, index) {
        $(this).append(`
            <input type="hidden" name="items[${index}][itemable_type]" value="${product.itemable_type}">
            <input type="hidden" name="items[${index}][itemable_id]" value="${product.itemable_id}">
            <input type="hidden" name="items[${index}][quantity]" value="${product.quantity}">
        `);
    });
});
</script>
@endpush
