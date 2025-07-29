@extends('layouts.app')

@section('title', 'Transaksi Penjualan Baru')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transaksi Penjualan Baru</h5>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="invoice_number" class="form-label">Nomor Invoice</label>
                                    <input type="text" class="form-control @error('invoice_number') is-invalid @enderror"
                                        id="invoice_number" name="invoice_number" value="{{ $invoice }}" readonly>
                                    @error('invoice_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_name" class="form-label">Nama Pelanggan</label>
                                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                        id="customer_name" name="customer_name"
                                        value="{{ old('customer_name', 'Pelanggan') }}"
                                        placeholder="Kosongkan untuk pelanggan umum">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Produk --}}
                            <div class="card mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Daftar Produk</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-product">
                                        <i class="fas fa-plus"></i> Tambah Produk
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="product-container">
                                        <!-- Product rows will be added here -->
                                    </div>
                                </div>
                            </div>

                            {{-- Jasa --}}
                            <div class="card mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Daftar Jasa</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-service">
                                        <i class="fas fa-plus"></i> Tambah Jasa
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="service-container">
                                        <!-- Service rows will be added here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Transaksi --}}
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Ringkasan Transaksi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="description" name="description" rows="2"
                                      placeholder="Contoh: Potongan Harga">{{ old('description') }}</textarea>
                        </div>

                                    <div class="mb-3">
                                        <label for="discount_amount" class="form-label">Potongan Harga (Rp)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="discount_amount"
                                                name="discount_amount" value="0" min="0" oninput="calculateTotal()">
                                        </div>
                                        <div class="alert alert-danger mt-2" id="discount-error" style="display:none">
                                            <i class="fas fa-exclamation-triangle"></i> Diskon melebihi batas margin!
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Total Pembayaran</label>
                                        <h3 id="totalPayment">Rp 0</h3>
                                        <input type="hidden" name="total_amount" id="total_amount" value="0">
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary" id="submitButton">
                                            <i class="fas fa-save"></i> Simpan Transaksi
                                        </button>
                                        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
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
                                    <th>Harga Jual</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary select-product"
                                                data-id="{{ $product->id }}" 
                                                data-name="{{ $product->name }}"
                                                data-price="{{ $product->selling_price }}"
                                                data-cost="{{ $product->cost_price }}"
                                                @if ($product->stock <= 0) disabled @endif>
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

    <!-- Service Selection Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">Pilih Jasa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchService" placeholder="Cari jasa...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="serviceTable">
                            <thead>
                                <tr>
                                    <th>Nama Jasa</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($services as $service)
                                    <tr>
                                        <td>{{ $service->name }}</td>
                                        <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary select-service"
                                                data-id="{{ $service->id }}" 
                                                data-name="{{ $service->name }}" 
                                                data-price="{{ $service->price }}">
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let productCounter = 0;
            let serviceCounter = 0;
            const selectedProducts = new Set();
            const selectedServices = new Set();

            // Buka modal tambah produk
            document.getElementById('add-product').addEventListener('click', function() {
                $('#productModal').modal('show');
            });

            // Buka modal tambah jasa
            document.getElementById('add-service').addEventListener('click', function() {
                $('#serviceModal').modal('show');
            });

            // Pencarian produk
            document.getElementById('searchProduct').addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                document.querySelectorAll('#productTable tbody tr').forEach(row => {
                    const productName = row.cells[0].textContent.toLowerCase();
                    row.style.display = productName.includes(searchValue) ? '' : 'none';
                });
            });

            // Pencarian jasa
            document.getElementById('searchService').addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                document.querySelectorAll('#serviceTable tbody tr').forEach(row => {
                    const serviceName = row.cells[0].textContent.toLowerCase();
                    row.style.display = serviceName.includes(searchValue) ? '' : 'none';
                });
            });

            // Pilih produk dari modal
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('select-product')) {
                    const productId = e.target.dataset.id;
                    const productName = e.target.dataset.name;
                    const productPrice = e.target.dataset.price;
                    const productCost = e.target.dataset.cost;

                    if (selectedProducts.has(productId)) {
                        alert('Produk ini sudah ditambahkan!');
                        return;
                    }

                    addProductRow(productId, productName, productPrice, productCost);
                    selectedProducts.add(productId);
                    $('#productModal').modal('hide');
                }

                // Pilih jasa dari modal
                if (e.target.classList.contains('select-service')) {
                    const serviceId = e.target.dataset.id;
                    const serviceName = e.target.dataset.name;
                    const servicePrice = e.target.dataset.price;

                    if (selectedServices.has(serviceId)) {
                        alert('Jasa ini sudah ditambahkan!');
                        return;
                    }

                    addServiceRow(serviceId, serviceName, servicePrice);
                    selectedServices.add(serviceId);
                    $('#serviceModal').modal('hide');
                }

                // Hapus produk
                if (e.target.classList.contains('remove-product')) {
                    const row = e.target.closest('.product-row');
                    const productId = row.querySelector('input[name*="[id]"]').value;
                    selectedProducts.delete(productId);
                    row.remove();
                    calculateTotal();
                }

                // Hapus jasa
                if (e.target.classList.contains('remove-service')) {
                    const row = e.target.closest('.service-row');
                    const serviceId = row.querySelector('input[name*="[id]"]').value;
                    selectedServices.delete(serviceId);
                    row.remove();
                    calculateTotal();
                }
            });

            // Fungsi tambah baris produk
            function addProductRow(productId, productName, productPrice, productCost) {
                const html = `
                <div class="row mb-2 product-row align-items-center" data-cost="${productCost}">
                    <div class="col-md-5">
                        <input type="hidden" name="products[${productCounter}][id]" value="${productId}">
                        <input type="hidden" name="products[${productCounter}][cost]" value="${productCost}">
                        <p class="mb-0"><strong>${productName}</strong></p>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control product-price" 
                                   value="${Number(productPrice).toLocaleString('id-ID')}" readonly>
                            <input type="hidden" name="products[${productCounter}][price]" value="${productPrice}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${productCounter}][quantity]" 
                               class="form-control product-quantity" min="1" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-product">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>`;

                document.getElementById('product-container').insertAdjacentHTML('beforeend', html);
                productCounter++;
                
                // Pasang event listener ke quantity baru
                const newRow = document.querySelector(`[name="products[${productCounter-1}][id]"]`).closest('.product-row');
                newRow.querySelector('.product-quantity').addEventListener('input', calculateTotal);
                
                calculateTotal();
            }

            // Fungsi tambah baris jasa
            function addServiceRow(serviceId, serviceName, servicePrice) {
                const html = `
                <div class="row mb-2 service-row align-items-center">
                    <div class="col-md-5">
                        <input type="hidden" name="services[${serviceCounter}][id]" value="${serviceId}">
                        <p class="mb-0"><strong>${serviceName}</strong></p>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="services[${serviceCounter}][price]" 
                                   class="form-control service-price" value="${servicePrice}" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-service">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>`;

                document.getElementById('service-container').insertAdjacentHTML('beforeend', html);
                serviceCounter++;
                calculateTotal();
            }

            // Fungsi validasi diskon
            function validateDiscount() {
                let totalMaxDiscount = 0;
                
                document.querySelectorAll('.product-row').forEach(row => {
                    const cost = parseFloat(row.dataset.cost);
                    const price = parseFloat(row.querySelector('input[name*="[price]"]').value);
                    const qty = parseInt(row.querySelector('.product-quantity').value) || 0;
                    totalMaxDiscount += (price - cost) * qty;
                });

                const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
                
                if (discount > totalMaxDiscount) {
                    document.getElementById('discount-error').style.display = 'block';
                    document.getElementById('submitButton').disabled = true;
                    return false;
                } else {
                    document.getElementById('discount-error').style.display = 'none';
                    document.getElementById('submitButton').disabled = false;
                    return true;
                }
            }

            // Fungsi hitung total
            function calculateTotal() {
                let subtotal = 0;
                
                // Hitung produk
                document.querySelectorAll('.product-row').forEach(row => {
                    const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
                    const qty = parseInt(row.querySelector('.product-quantity').value) || 0;
                    subtotal += price * qty;
                });
                
                // Hitung jasa
                document.querySelectorAll('.service-row').forEach(row => {
                    const price = parseFloat(row.querySelector('.service-price').value) || 0;
                    subtotal += price;
                });

                // Validasi diskon
                validateDiscount();
                
                // Hitung diskon
                const discountInput = document.getElementById('discount_amount');
                let discount = parseFloat(discountInput.value) || 0;
                discount = Math.min(discount, subtotal);
                
                // Update tampilan
                document.getElementById('totalPayment').textContent = 'Rp ' + (subtotal - discount).toLocaleString('id-ID');
                document.getElementById('total_amount').value = subtotal - discount;
            }

            // Event listener untuk diskon
            document.getElementById('discount_amount').addEventListener('input', calculateTotal);

            // Validasi form submit
            document.getElementById('saleForm').addEventListener('submit', function(e) {
                if (productCounter === 0 && serviceCounter === 0) {
                    e.preventDefault();
                    alert('Harus ada minimal 1 produk atau jasa!');
                    return;
                }
                
                if (!validateDiscount()) {
                    e.preventDefault();
                    alert('Diskon melebihi margin keuntungan yang diizinkan!');
                    return;
                }
            });

            // Inisialisasi awal
            calculateTotal();
        });
    </script>
@endpush