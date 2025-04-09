@extends('layouts.app')

@section('title', 'Peramalan Penjualan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Peramalan Penjualan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Single Exponential Smoothing</h5>
                                    <p class="card-text">Metode peramalan untuk data tanpa tren dan pola musiman.</p>
                                    <a href="{{ route('forecast.ses') }}" class="btn btn-primary">Pilih Metode Ini</a>
                                </div>
                                <div class="card-footer bg-light">
                                    <small class="text-muted">Cocok untuk data dengan pola stasioner</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Double Exponential Smoothing</h5>
                                    <p class="card-text">Metode peramalan untuk data dengan tren tetapi tanpa pola musiman.</p>
                                    <a href="{{ route('forecast.des') }}" class="btn btn-primary">Pilih Metode Ini</a>
                                </div>
                                <div class="card-footer bg-light">
                                    <small class="text-muted">Cocok untuk data dengan pola tren naik/turun</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Triple Exponential Smoothing</h5>
                                    <p class="card-text">Metode peramalan untuk data dengan tren dan pola musiman.</p>
                                    <a href="{{ route('forecast.tes') }}" class="btn btn-primary">Pilih Metode Ini</a>
                                </div>
                                <div class="card-footer bg-light">
                                    <small class="text-muted">Cocok untuk data dengan pola musiman bulanan/tahunan</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Perbandingan Metode Peramalan</h6>
                            </div>
                            <div class="card-body">
                                <p>Lihat perbandingan akurasi dari berbagai metode peramalan untuk memilih metode terbaik.</p>
                                <a href="{{ route('forecast.comparison') }}" class="btn btn-success">
                                    <i class="fas fa-chart-line me-2"></i> Lihat Perbandingan
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle me-2"></i> Panduan Memilih Metode Peramalan</h6>
                        <ul class="mb-0">
                            <li><strong>Single Exponential Smoothing (SES)</strong> - Gunakan jika data penjualan relatif stabil tanpa tren atau pola musiman yang jelas.</li>
                            <li><strong>Double Exponential Smoothing (DES)</strong> - Gunakan jika data penjualan menunjukkan tren naik atau turun yang konsisten.</li>
                            <li><strong>Triple Exponential Smoothing (TES)</strong> - Gunakan jika data penjualan menunjukkan pola musiman (misalnya penjualan meningkat pada bulan-bulan tertentu setiap tahun).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
