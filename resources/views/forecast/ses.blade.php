@extends('layouts.app')

@section('title', 'Form Peramalan SES')

@section('content')
<div class="container">
    <h3>Peramalan Penjualan dengan Single Exponential Smoothing (SES)</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form produk (GET untuk reload periode) --}}
    <form action="{{ route('forecast.ses') }}" method="GET" id="productForm" class="mb-4">
        <div class="form-group">
            <label for="product_id">Pilih Produk</label>
            <select name="product_id" id="product_id" class="form-control @error('product_id') is-invalid @enderror" onchange="document.getElementById('productForm').submit()">
                <option value="">-- Pilih Produk --</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" {{ (old('product_id', $selectedProductId ?? '') == $product->id) ? 'selected' : '' }}>
                    {{ $product->name }} ({{ $product->category->name ?? 'Kategori' }})
                </option>
                @endforeach
            </select>
            @error('product_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Pilih produk yang ingin diramalkan.</small>
        </div>
    </form>

    {{-- Form utama peramalan --}}
    <form action="{{ route('forecast.calculate') }}" method="POST" id="sesForm">
        @csrf
        <input type="hidden" name="method" value="ses">
        <input type="hidden" name="product_id" value="{{ old('product_id', $selectedProductId ?? '') }}">

        {{-- Pilih Periode Awal --}}
        <div class="form-group">
            <label for="start_period">Periode Awal Data Historis</label>
            <select name="start_period" id="start_period" class="form-control @error('start_period') is-invalid @enderror" {{ empty($availablePeriods) ? 'disabled' : '' }}>
                <option value="">-- Pilih Periode Awal --</option>
                @foreach($availablePeriods as $period)
                    <option value="{{ $period }}" {{ old('start_period') == $period ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($period.'-01')->format('M Y') }}
                    </option>
                @endforeach
            </select>
            @error('start_period')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if(empty($availablePeriods) && (old('product_id') || $selectedProductId))
                <small class="text-danger">Tidak ada data penjualan untuk produk ini.</small>
            @endif
            <small class="form-text text-muted">Pilih periode awal data historis yang akan digunakan untuk peramalan.</small>
        </div>

        {{-- Pilih Periode Akhir --}}
        <div class="form-group">
            <label for="end_period">Periode Akhir Data Historis</label>
            <select name="end_period" id="end_period" class="form-control @error('end_period') is-invalid @enderror" {{ empty($availablePeriods) ? 'disabled' : '' }}>
                <option value="">-- Pilih Periode Akhir --</option>
                @foreach($availablePeriods as $period)
                    <option value="{{ $period }}" {{ old('end_period', end($availablePeriods)) == $period ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($period.'-01')->format('M Y') }}
                    </option>
                @endforeach
            </select>
            @error('end_period')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Pilih periode akhir data historis yang akan digunakan untuk peramalan (≥ periode awal).</small>
        </div>

        {{-- Pilihan Mode Alpha --}}
        <div class="form-group mt-3">
            <label>Mode Alpha</label><br>
            @php
                $alphaModeOld = old('alpha_mode', 'auto');
            @endphp
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="alpha_mode" id="alpha_auto" value="auto" {{ $alphaModeOld == 'auto' ? 'checked' : '' }}>
                <label class="form-check-label" for="alpha_auto">Alpha Terbaik Otomatis</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="alpha_mode" id="alpha_manual_radio" value="manual" {{ $alphaModeOld == 'manual' ? 'checked' : '' }}>
                <label class="form-check-label" for="alpha_manual_radio">Alpha Manual</label>
            </div>
            @error('alpha_mode')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        {{-- Input Nilai Alpha Manual --}}
        <div class="form-group mt-2">
            <label for="alpha_manual">Masukkan Nilai Alpha (0.01 - 0.99)</label>
            <input 
                type="number" 
                step="0.01" min="0.01" max="0.99" 
                name="alpha_manual" id="alpha_manual"
                class="form-control @error('alpha_manual') is-invalid @enderror"
                value="{{ old('alpha_manual') }}"
                {{ $alphaModeOld != 'manual' ? 'disabled' : '' }}
            >
            @error('alpha_manual')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Nilai alpha mengatur sensitivitas model terhadap data terbaru. Jika bingung, pilih mode otomatis.</small>
        </div>

        {{-- Jumlah Periode Forecast --}}
        <div class="form-group mt-4">
            <label for="forecast_periods">Jumlah Periode Ramalan ke Depan</label>
            <input type="number" name="forecast_periods" id="forecast_periods" class="form-control @error('forecast_periods') is-invalid @enderror" min="1" max="24" value="{{ old('forecast_periods', 3) }}">
            @error('forecast_periods')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Masukkan jumlah bulan yang ingin diramalkan ke depan (maksimal 24 bulan).</small>
        </div>

        {{-- Tombol Submit --}}
        <button type="submit" class="btn btn-primary mt-3">Hitung Peramalan</button>
    </form>
</div>

<script>
    function updateAlphaInput() {
        const manualRadio = document.getElementById('alpha_manual_radio');
        const alphaInput = document.getElementById('alpha_manual');
        if (manualRadio.checked) {
            alphaInput.disabled = false;
        } else {
            alphaInput.disabled = true;
            alphaInput.value = '';
        }
    }

    // Inisialisasi kondisi input alpha manual saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function() {
        updateAlphaInput();
        document.getElementById('alpha_auto').addEventListener('change', updateAlphaInput);
        document.getElementById('alpha_manual_radio').addEventListener('change', updateAlphaInput);
    });
</script>
@endsection
