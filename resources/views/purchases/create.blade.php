@extends('layouts.app')

@section('title', 'Transaksi Pembelian Baru')

@section('styles')
<style>
    .product-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .product-item:last-child {
        border-bottom: none;
    }
    .summary-section {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Transaksi Pembelian Baru</h5>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Informasi Transaksi</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="invoice_number" class="form-label">Nomor Invoice</label>
                                        <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" 
                                            id="invoice_number" name="invoice_number" 
                                            value="{{ old('invoice_number', $invoice) }}" readonly>
                                        @error('invoice_number')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="supplier_name" class="form-label">Nama Supplier</label>
                                        <input type="text" class="form-control @error('supplier_name') is-invalid @enderror" 
                                            id="supplier_name" name="supplier_name" 
                                            value="{{ old('supplier_name') }}" required>
                                        @error('supplier_name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                            rows="2">{{ old('notes') }}</textarea>
                                        @error('notes')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="photo_struk" class="form-label">Foto Struk (Opsional)</label>
                                        <input type="file" name="photo_struk" id="photo_struk" 
                                            class="form-control @error('photo_struk') is-invalid @enderror">
                                        <small class="text-muted">Format: JPG/PNG, maksimal 2MB</small>
                                        @error('photo_struk')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Produk</h6>
                                <button type="button" class="btn btn-sm btn-primary" id="addProductBtn">
                                    <i class="fas fa-plus"></i> Tambah Produk
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="productContainer">
                                    <!-- Product items will be added here -->
                                </div>
                                
                                @if($errors->has('products'))
                                <div class="alert alert-danger mt-3">
                                    {{ $errors->first('products') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card summary-section">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Ringkasan Transaksi</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Total Pembayaran</label>
                                    <h3 id="totalPayment" class="text-primary">Rp 0</h3>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">Simpan Transaksi</button>
                                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Batal</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Pilih Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchProduct" placeholder="Cari produk...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="productTable">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Brand</th>
                                <th>Kategori</th>
                                <th>Harga Beli Terakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->brand }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td>Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-product" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->name }}" 
                                        data-price="{{ $product->cost_price }}">
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
@endsection

@section('scripts')
<script>
    let productCounter = 0;
    const products = @json($products);
    const selectedProducts = new Set();

    $(document).ready(function() {
        // Add product button
        $('#addProductBtn').click(function() {
            $('#productModal').modal('show');
        });

        // Search product
        $('#searchProduct').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#productTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Select product
        $('.select-product').click(function() {
            const productId = $(this).data('id');
            const productName = $(this).data('name');
            const productPrice = $(this).data('price');

            if (selectedProducts.has(productId)) {
                alert('Produk ini sudah ditambahkan!');
                return;
            }

            addProductToForm(productId, productName, productPrice);
            selectedProducts.add(productId);
            $('#productModal').modal('hide');
        });

        // Initial calculation
        calculateTotal();
    });

    function addProductToForm(productId, productName, productPrice) {
        const html = `
            <div class="product-item" id="product-${productCounter}">
                <div class="row">
                    <div class="col-md-4">
                        <input type="hidden" name="products[${productCounter}][id]" value="${productId}">
                        <p class="mb-1"><strong>${productName}</strong></p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah</label>
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity(${productCounter})">-</button>
                            <input type="number" class="form-control text-center product-quantity" 
                                name="products[${productCounter}][quantity]" 
                                value="1" 
                                min="1" 
                                data-counter="${productCounter}"
                                onchange="updateSubtotal(${productCounter}); calculateTotal();">
                            <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity(${productCounter})">+</button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Harga Beli</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control product-price" 
                                name="products[${productCounter}][price]" 
                                value="${productPrice}" 
                                min="0" 
                                data-counter="${productCounter}"
                                onchange="updateSubtotal(${productCounter}); calculateTotal();">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Subtotal</label>
                        <p class="subtotal" id="subtotal-${productCounter}">Rp ${formatNumber(productPrice)}</p>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeProduct(${productCounter}, ${productId})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#productContainer').append(html);
        productCounter++;
        calculateTotal();
    }

    function updateSubtotal(counter) {
        const quantityInput = $(`input[name="products[${counter}][quantity]"]`);
        const priceInput = $(`input[name="products[${counter}][price]"]`);
        const quantity = parseInt(quantityInput.val());
        const price = parseFloat(priceInput.val());
        const subtotal = quantity * price;
        
        $(`#subtotal-${counter}`).text(`Rp ${formatNumber(subtotal)}`);
    }

    function increaseQuantity(counter) {
        const quantityInput = $(`input[name="products[${counter}][quantity]"]`);
        let quantity = parseInt(quantityInput.val());
        
        quantity++;
        quantityInput.val(quantity);
        updateSubtotal(counter);
        calculateTotal();
    }

    function decreaseQuantity(counter) {
        const quantityInput = $(`input[name="products[${counter}][quantity]"]`);
        let quantity = parseInt(quantityInput.val());
        
        if (quantity > 1) {
            quantity--;
            quantityInput.val(quantity);
            updateSubtotal(counter);
            calculateTotal();
        }
    }

    function removeProduct(counter, productId) {
        $(`#product-${counter}`).remove();
        selectedProducts.delete(productId);
        calculateTotal();
    }

    function calculateTotal() {
        let totalPayment = 0;
        
        $('.product-item').each(function() {
            const counter = $(this).find('.product-quantity').data('counter');
            const quantity = parseInt($(`input[name="products[${counter}][quantity]"]`).val());
            const price = parseFloat($(`input[name="products[${counter}][price]"]`).val());
            totalPayment += quantity * price;
        });
        
        $('#totalPayment').text(`Rp ${formatNumber(totalPayment)}`);
    }

    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    document.getElementById('purchaseForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('photo_struk');
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size / 1024 / 1024;
            if (fileSize > 2) {
                e.preventDefault();
                alert('Ukuran file maksimal 2MB');
                return false;
            }
            
            const validTypes = ['image/jpeg', 'image/png'];
            if (!validTypes.includes(fileInput.files[0].type)) {
                e.preventDefault();
                alert('Format file harus JPG/PNG');
                return false;
            }
        }
    });
</script>
@endsection