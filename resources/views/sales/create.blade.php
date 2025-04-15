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
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                @csrf
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

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Daftar Produk</h6>
                    </div>
                    <div class="card-body">
                        <div id="product-container">
                            <!-- Product rows will be added here -->
                        </div>
                        
                        <button type="button" class="btn btn-success mt-2" id="add-product">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="service_price" class="form-label">Biaya Servis (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control @error('service_price') is-invalid @enderror" 
                                   id="service_price" name="service_price" min="0" value="{{ old('service_price', 0) }}">
                        </div>
                        @error('service_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="total_price" class="form-label">Total Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_price" readonly>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variabel untuk menyimpan indeks produk
        let productIndex = 0;
        
        // Template untuk baris produk
        function getProductRowTemplate(index) {
            return `
                <div class="row mb-2 product-row align-items-center">
                    <div class="col-md-5">
                        <select name="products[${index}][id]" class="form-select product-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}" data-stock="{{ $product->stock }}">
                                {{ $product->name }} (Stok: {{ $product->stock }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${index}][quantity]" class="form-control product-quantity" 
                               min="1" max="999" value="1" required placeholder="Jumlah">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control product-price" readonly>
                            <input type="hidden" name="products[${index}][price]" class="product-price-hidden">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-product">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        // Fungsi untuk menambahkan baris produk baru
        function addProductRow() {
            const productContainer = document.getElementById('product-container');
            const rowHtml = getProductRowTemplate(productIndex);
            
            // Buat element div untuk menampung baris produk
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = rowHtml;
            const productRow = tempDiv.firstElementChild;
            
            productContainer.appendChild(productRow);
            
            // Tambahkan event listener untuk select produk
            const select = productRow.querySelector('.product-select');
            select.addEventListener('change', updatePrice);
            
            // Tambahkan event listener untuk quantity
            const quantity = productRow.querySelector('.product-quantity');
            quantity.addEventListener('input', updateSubtotal);
            
            // Tambahkan event listener untuk tombol hapus
            const removeButton = productRow.querySelector('.remove-product');
            removeButton.addEventListener('click', function() {
                productRow.remove();
                calculateTotal();
            });
            
            productIndex++;
            calculateTotal();
        }
        
        // Fungsi untuk mengupdate harga berdasarkan produk yang dipilih
        function updatePrice(e) {
            const select = e.target;
            const option = select.options[select.selectedIndex];
            
            if (select.value) {
                const price = option.dataset.price;
                const stock = parseInt(option.dataset.stock);
                const row = select.closest('.product-row');
                const priceInput = row.querySelector('.product-price');
                const priceHidden = row.querySelector('.product-price-hidden');
                const quantityInput = row.querySelector('.product-quantity');
                
                // Set harga dan maksimum quantity berdasarkan stok
                priceInput.value = formatRupiah(price);
                priceHidden.value = price;
                quantityInput.max = stock;
                
                // Jika quantity melebihi stok, reset ke stok maksimum
                if (parseInt(quantityInput.value) > stock) {
                    quantityInput.value = stock;
                }
            }
            
            updateSubtotal(e);
        }
        
        // Fungsi untuk mengupdate subtotal berdasarkan quantity
        function updateSubtotal(e) {
            calculateTotal();
        }
        
        // Fungsi untuk menghitung total
        function calculateTotal() {
            let total = 0;
            const rows = document.querySelectorAll('.product-row');
            
            rows.forEach(row => {
                const select = row.querySelector('.product-select');
                const quantity = parseInt(row.querySelector('.product-quantity').value) || 0;
                
                if (select.selectedIndex > 0) {
                    const price = parseFloat(select.options[select.selectedIndex].dataset.price);
                    total += price * quantity;
                }
            });
            
            // Tambahkan biaya servis jika ada
            const servicePrice = parseFloat(document.getElementById('service_price').value) || 0;
            total += servicePrice;
            
            document.getElementById('total_price').value = formatRupiah(total);
        }
        
        // Fungsi untuk format rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }
        
        // Event listener untuk tombol tambah produk
        document.getElementById('add-product').addEventListener('click', addProductRow);
        
        // Event listener untuk perubahan biaya servis
        document.getElementById('service_price').addEventListener('input', calculateTotal);
        
        // Tambahkan baris produk pertama saat halaman dimuat
        addProductRow();
    });
</script>
@endpush
