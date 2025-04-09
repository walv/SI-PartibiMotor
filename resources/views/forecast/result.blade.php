@extends('layouts.app')

@section('title', 'Hasil Peramalan')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Hasil Peramalan 
                    @if($method == 'ses')
                        Single Exponential Smoothing
                    @elseif($method == 'des')
                        Double Exponential Smoothing
                    @elseif($method == 'tes')
                        Triple Exponential Smoothing
                    @endif
                </h5>
                <a href="{{ route('forecast.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Informasi Peramalan</h6>
                    <p><strong>Produk:</strong> {{ $product->name }} ({{ $product->brand }})</p>
                    <p><strong>Periode Data Historis:</strong> {{ $period }} bulan</p>
                    <p><strong>Periode Peramalan:</strong> {{ $forecastPeriods }} bulan</p>
                </div>
                <div class="col-md-6">
                    <h6>Parameter</h6>
                    <p><strong>Alpha (α):</strong> {{ $alpha }}</p>
                    @if($method == 'des' || $method == 'tes')
                        <p><strong>Beta (β):</strong> {{ $beta }}</p>
                    @endif
                    @if($method == 'tes')
                        <p><strong>Gamma (γ):</strong> {{ $gamma }}</p>
                    @endif
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Grafik Peramalan</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="forecastChart" height="300"></canvas>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Grafik menampilkan data aktual (hijau), nilai fitted (biru), dan hasil peramalan (merah) untuk {{ $forecastPeriods }} bulan ke depan.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Hasil Peramalan {{ $forecastPeriods }} Bulan Ke Depan</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Periode</th>
                                            <th>Hasil Peramalan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($result['forecast'] as $index => $value)
                                        <tr>
                                            <td>{{ $chartData['labels'][count($chartData['labels']) - $forecastPeriods + $index] }}</td>
                                            <td>{{ round($value, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Nilai peramalan ini dapat digunakan untuk perencanaan stok dan pembelian.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Evaluasi Akurasi Model</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Metrik</th>
                                            <th>Nilai</th>
                                            <th>Interpretasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>MAPE</td>
                                            <td>{{ number_format($metrics['mape'], 2) }}%</td>
                                            <td>
                                                @if($metrics['mape'] < 10)
                                                    <span class="text-success">Sangat Baik</span>
                                                @elseif($metrics['mape'] < 20)
                                                    <span class="text-primary">Baik</span>
                                                @elseif($metrics['mape'] < 30)
                                                    <span class="text-warning">Cukup</span>
                                                @else
                                                    <span class="text-danger">Kurang Baik</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>MAE</td>
                                            <td>{{ number_format($metrics['mae'], 2) }}</td>
                                            <td>Rata-rata kesalahan absolut</td>
                                        </tr>
                                        <tr>
                                            <td>RMSE</td>
                                            <td>{{ number_format($metrics['rmse'], 2) }}</td>
                                            <td>Akar rata-rata kesalahan kuadrat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Semakin kecil nilai metrik, semakin akurat model peramalan. MAPE < 10% menunjukkan akurasi yang sangat baik.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <h6><i class="fas fa-lightbulb me-2"></i> Interpretasi Hasil</h6>
                <p>
                    @if($metrics['mape'] < 20)
                        Model peramalan ini menunjukkan akurasi yang baik untuk produk {{ $product->name }}. 
                        Hasil peramalan dapat digunakan untuk perencanaan stok dan strategi penjualan.
                    @else
                        Model peramalan ini menunjukkan akurasi yang cukup untuk produk {{ $product->name }}. 
                        Pertimbangkan untuk mencoba metode peramalan lain atau menyesuaikan parameter untuk hasil yang lebih baik.
                    @endif
                </p>
                <p class="mb-0">
                    Berdasarkan hasil peramalan, Anda dapat merencanakan pembelian stok untuk {{ $forecastPeriods }} bulan ke depan
                    dan mengantisipasi perubahan permintaan.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const ctx = document.getElementById('forecastChart').getContext('2d');
        const forecastChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: @json($chartData['datasets'])
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Periode'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(2);
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Grafik Peramalan {{ $product->name }}'
                    }
                }
            }
        });
    });
</script>
@endsection
