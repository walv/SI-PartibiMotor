{{-- filepath: resources/views/sales/create.blade.php --}}
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

                {{-- Produk --}}
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

                {{-- Jasa --}}
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Daftar Jasa</h6>
                    </div>
                    <div class="card-body">
                        <div id="service-container">
                            <!-- Service rows will be added here -->
                        </div>
                        
                        <button type="button" class="btn btn-success mt-2" id="add-service">
                            <i class="fas fa-plus"></i> Tambah Jasa
                        </button>
                    </div>
                </div>

                {{-- Total --}}
                <div class="row mb-3">
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
    let productIndex = 0;
    let serviceIndex = 0;

    // Template untuk baris produk
    function getProductRowTemplate(index) {
    return `
        <div class="row mb-2 product-row align-items-center">
            <div class="col-md-5">
                <select name="products[${index}][id]" class="form-select product-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">
                        {{ $product->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control product-price" readonly>
                    <input type="hidden" name="products[${index}][price]" class="product-price-hidden">
                </div>
            </div>
            <div class="col-md-2">
                <input type="number" name="products[${index}][quantity]" class="form-control product-quantity" min="1" value="1" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-product">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
}

    // Template untuk baris jasa
    function getServiceRowTemplate(index) {
        return `
            <div class="row mb-2 service-row align-items-center">
                <div class="col-md-5">
                    <select name="services[${index}][id]" class="form-select service-select" required>
    <option value="">-- Pilih Jasa --</option>
    @foreach($services as $service)
    <option value="{{ $service->id }}" data-price="{{ $service->harga }}">
        {{ $service->name }}
    </option>
    @endforeach
</select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
  <input type="number" name="services[${index}][price]" class="form-control service-price" min="0" step="1000" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-service">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Tambah produk
    document.getElementById('add-product').addEventListener('click', function() {
        const container = document.getElementById('product-container');
        container.insertAdjacentHTML('beforeend', getProductRowTemplate(productIndex));
        productIndex++;
    });

    // Tambah jasa
    document.getElementById('add-service').addEventListener('click', function() {
        const container = document.getElementById('service-container');
        container.insertAdjacentHTML('beforeend', getServiceRowTemplate(serviceIndex));
        serviceIndex++;
    });

    // Update harga produk saat memilih produk
    document.addEventListener('change', function(event) {
    if (event.target.classList.contains('product-select')) {
        const selectedOption = event.target.selectedOptions[0];
        const price = parseFloat(selectedOption.dataset.price); // Ambil harga dari data-price
        const priceField = event.target.closest('.product-row').querySelector('.product-price');
        const priceHiddenField = event.target.closest('.product-row').querySelector('.product-price-hidden');

        // Update harga di field produk
        if (!isNaN(price)) {
            priceField.value = new Intl.NumberFormat('id-ID').format(price);
            priceHiddenField.value = price;
        } else {
            priceField.value = '';
            priceHiddenField.value = '';
        }

        // Recalculate total jika perlu
        calculateTotal();
    }
});
///jasa
document.addEventListener('change', function(event) {
    if (event.target.classList.contains('service-select')) {
        const selectedOption = event.target.selectedOptions[0];
        const price = parseFloat(selectedOption.dataset.price); // Ambil harga dari data-price
        const priceField = event.target.closest('.service-row').querySelector('.service-price');

        // Update harga di field jasa
        if (!isNaN(price)) {
            priceField.value = price; // Isi harga default
        } else {
            priceField.value = '';
        }

        // Recalculate total jika perlu
        calculateTotal();
    }
});
    document.addEventListener('click', function(event) {
    // Hapus produk
    if (event.target.classList.contains('remove-product') || event.target.closest('.remove-product')) {
        const productRow = event.target.closest('.product-row');
        productRow.remove();
        calculateTotal(); // Recalculate total setelah produk dihapus
    }

    // Hapus jasa
    if (event.target.classList.contains('remove-service') || event.target.closest('.remove-service')) {
        const serviceRow = event.target.closest('.service-row');
        serviceRow.remove();
        calculateTotal(); // Recalculate total setelah jasa dihapus
    }
});

    // Hitung total
    document.addEventListener('input', function() {
        calculateTotal();
    });

    function calculateTotal() {
    let total = 0;

    // Hitung total produk
    document.querySelectorAll('.product-row').forEach(row => {
        const price = parseFloat(row.querySelector('.product-price-hidden').value || 0);
        const quantity = parseInt(row.querySelector('.product-quantity').value || 0);
        total += price * quantity;
    });

    // Hitung total jasa
    document.querySelectorAll('.service-row').forEach(row => {
        const price = parseFloat(row.querySelector('.service-price').value || 0); // Ambil harga dari input
        total += price;
    });

    document.getElementById('total_price').value = new Intl.NumberFormat('id-ID').format(total);
}
});
</script>
@endpush