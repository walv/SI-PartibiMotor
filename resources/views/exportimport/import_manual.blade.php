@extends('layouts.app')

@section('title', 'Import Manual Data Penjualan')

@section('content')
<div class="container">
    <h3>Import Manual Data Penjualan</h3>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('sales.import.manual.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="invoice_number">Nomor Invoice</label>
            <input type="text" name="invoice_number" id="invoice_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="customer_name">Nama Pelanggan</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control">
        </div>

        <div class="mb-3">
            <label for="date">Tanggal Transaksi</label>
            <input type="date" name="date" id="date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Produk</label>
            <div id="product-container">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <select name="products[0][id]" class="form-control">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="products[0][quantity]" class="form-control" placeholder="Jumlah">
                    </div>
                </div>
            </div>
            <button type="button" id="add-product" class="btn btn-secondary">Tambah Produk</button>
        </div>

        <div class="mb-3">
            <label>Jasa</label>
            <div id="service-container">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <select name="services[0][id]" class="form-control">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="services[0][price]" class="form-control" placeholder="Harga">
                    </div>
                </div>
            </div>
            <button type="button" id="add-service" class="btn btn-secondary">Tambah Jasa</button>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection