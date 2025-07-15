@extends('layouts.app')

@section('title', 'Transaksi Penjualan Baru')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Transaksi Penjualan Baru</h5>
        </div>
        <div class="card-body">
            @if(session('error'))
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
                                       id="customer_name" name="customer_name" value="{{ old('customer_name', 'Pelanggan') }}"
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
                                    <label class="form-label">Total Pembayaran</label>
                                    <h3 id="totalPayment">Rp 0</h3>
                                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
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
                            @foreach($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-product" 
                                        data-id="{{ $product->id }}" 
                                        data-name="{{ $product->name }}" 
                                        data-price="{{ $product->selling_price }}"
                                        @if($product->stock <= 0) disabled @endif>
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
                            @foreach($services as $service)
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

                if (selectedProducts.has(productId)) {
                    alert('Produk ini sudah ditambahkan!');
                    return;
                }

                addProductRow(productId, productName, productPrice);
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
        function addProductRow(productId, productName, productPrice) {
            const html = `
                <div class="row mb-2 product-row align-items-center">
                    <div class="col-md-5">
                        <input type="hidden" name="products[${productCounter}][id]" value="${productId}">
                        <p class="mb-0"><strong>${productName}</strong></p>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control product-price" value="${parseFloat(productPrice).toLocaleString('id-ID')}" readonly>
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
                </div>
            `;

            document.getElementById('product-container').insertAdjacentHTML('beforeend', html);
            productCounter++;
            
            // Hitung total setelah menambahkan produk
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
                </div>
            `;

            document.getElementById('service-container').insertAdjacentHTML('beforeend', html);
            serviceCounter++;
            calculateTotal();
        }

        // Hitung total transaksi (produk + jasa)
        function calculateTotal() {
            let total = 0;
            
            // Hitung produk
            document.querySelectorAll('.product-row').forEach(row => {
                const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
                const quantity = parseInt(row.querySelector('.product-quantity').value) || 0;
                total += price * quantity;
            });

            // Hitung jasa
            document.querySelectorAll('.service-row').forEach(row => {
                const price = parseFloat(row.querySelector('.service-price').value) || 0;
                total += price;
            });

            document.getElementById('totalPayment').textContent = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('total_amount').value = total;
        }

        // Event listener untuk perubahan quantity produk
        document.getElementById('product-container').addEventListener('input', function(e) {
            if (e.target.classList.contains('product-quantity')) {
                calculateTotal();
            }
        });

        // Event listener untuk perubahan harga jasa
        document.getElementById('service-container').addEventListener('input', function(e) {
            if (e.target.classList.contains('service-price')) {
                calculateTotal();
            }
        });

      
    });
</script>
@endpush