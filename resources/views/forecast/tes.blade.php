@extends('layouts.app')

@section('title', 'Peramalan - Triple Exponential Smoothing')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Triple Exponential Smoothing (TES)</h5>
                <a href="{{ route('forecast.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('forecast.calculate') }}" method="POST">
                @csrf
                <input type="hidden" name="method" value="tes">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="product_id" class="form-label">Pilih Produk</label>
                        <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->brand }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="period" class="form-label">Jumlah Periode Data Historis</label>
                        <input type="number" class="form-control @error('period') is-invalid @enderror" id="period" name="period" value="{{ old('period', 24) }}" min="12" max="36" required>
                        @error('period')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Jumlah bulan data historis yang akan digunakan (minimal 12 bulan)</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="alpha" class="form-label">Nilai Alpha (α)</label>
                        <input type="number" class="form-control @error('alpha') is-invalid @enderror" id="alpha" name="alpha" value="{{ old('alpha', 0.2) }}" min="0" max="1" step="0.1" required>
                        @error('alpha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Parameter penghalusan level (0-1)</small>
                    </div>
                    <div class="col-md-3">
                        <label for="beta" class="form-label">Nilai Beta (β)</label>
                        <input type="number" class="form-control @error('beta') is-invalid @enderror" id="beta" name="beta" value="{{ old('beta', 0.1) }}" min="0" max="1" step="0.1" required>
                        @error('beta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Parameter penghalusan tren (0-1)</small>
                    </div>
                    <div class="col-md-3">
                        <label for="gamma" class="form-label">Nilai Gamma (γ)</label>
                        <input type="number" class="form-control @error('gamma') is-invalid @enderror" id="gamma" name="gamma" value="{{ old('gamma', 0.1) }}" min="0" max="1" step="0.1" required>
                        @error('gamma')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Parameter penghalusan musiman (0-1)</small>
                    </div>
                    <div class="col-md-3">
                        <label for="forecast_periods" class="form-label">Jumlah Periode Peramalan</label>
                        <input type="number" class="form-control @error('forecast_periods') is-invalid @enderror" id="forecast_periods" name="forecast_periods" value="{{ old('forecast_periods', 6) }}" min="1" max="12" required>
                        @error('forecast_periods')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Jumlah bulan yang akan diramalkan (1-12 bulan)</small>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i> Tentang Triple Exponential Smoothing</h6>
                    <p class="mb-0">Metode ini cocok untuk data dengan tren dan pola musiman. Parameter alpha (α) mengontrol penghalusan level, beta (β) mengontrol penghalusan tren, dan gamma (γ) mengontrol penghalusan musiman. Metode ini membutuhkan minimal 12 bulan data historis untuk menangkap pola musiman tahunan.</p>
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator me-2"></i> Hitung Peramalan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
