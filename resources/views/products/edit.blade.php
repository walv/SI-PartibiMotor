@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Produk</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="brand" class="form-label">Brand</label>
                        <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" value="{{ old('brand', $product->brand) }}">
                        @error('brand')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                      </div>
                      
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('category_id', $product->category_id) == $category->id) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="cost_price" class="form-label">Harga Beli</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" required>
                        </div>
                        @error('cost_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="selling_price" class="form-label">Harga Jual</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0" required>
                        </div>
                        @error('selling_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="stock" class="form-label">Stok</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                        @error('stock')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
