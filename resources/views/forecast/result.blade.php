@extends('layouts.app')

@section('title', 'Hasil Peramalan - SES')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Hasil Peramalan Penjualan dengan Single Exponential Smoothing (SES)</h3>
        <div>
            <a href="{{ route('forecast.ses') }}" class="btn btn-secondary">
                Kembali
            </a>
            <a href="{{ route('forecast.print', $product->id) }}" class="btn btn-primary" target="_blank">
    <i class="fas fa-file-pdf"></i> Cetak PDF
</a>
        </div>
    </div>

    @if(isset($notification))
        <div class="alert alert-warning">
            {{ $notification }}
        </div>
    @endif

    <!-- Informasi Produk dan Parameter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informasi Produk</h5>
                    <p><strong>Nama:</strong> {{ $product->name }}</p>
                    <p><strong>Kategori:</strong> {{ $product->category->name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Parameter Peramalan</h5>
                    <p><strong>Metode:</strong> {{ $method }}</p>
                    <p><strong>Alpha (α):</strong> {{ $parameters['alpha'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Hasil Peramalan -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Grafik Data Aktual vs Hasil Peramalan</h5>
            <canvas id="forecastChart" height="130"></canvas>
        </div>
    </div>

    <!-- Tabel Data Historis dan Fitted -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Data Historis & Hasil Fitted</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Data Aktual</th>
                            <th>Fitted SES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['periods'] as $index => $period)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $result['formatted_periods'][$index] ?? '-' }}</td>
                            <td>{{ $result['actual'][$index] ?? '-' }}</td>
                            <td>
                                @isset($result['fitted'][$index])
                                    {{ number_format($result['fitted'][$index], 2) }}
                                @else
                                    -
                                @endisset
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabel Forecast Periode Mendatang -->
    @if(!empty($result['forecast']))
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Peramalan Periode Mendatang</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Periode</th>
                            <th>Peramalan (Forecast)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $lastDate = \Carbon\Carbon::parse(end($result['periods']));
                        @endphp
                        @foreach($result['forecast'] as $i => $forecastValue)
                            @php
                                $forecastPeriod = $lastDate->copy()->addMonths($i + 1)->format('M Y');
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $forecastPeriod }}</td>
                                <td>{{ number_format($forecastValue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Metrik Evaluasi -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Metrik Evaluasi</h5>
            <div class="table-responsive">
                <table class="table table-bordered" style="max-width: 600px;">
                    <thead class="table-light">
                        <tr>
                            <th>MSE</th>
                            <th>MAE</th>
                            <th>MAPE (%)</th>
                            <th>RMSE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($metrics['mse'], 2) }}</td>
                            <td>{{ number_format($metrics['mae'], 2) }}</td>
                            <td>{{ number_format($metrics['mape'], 2) }}%</td>
                            <td>{{ number_format($metrics['rmse'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card">
    <div class="card-header">
        <h5>Akurasi Prediksi</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Metrik</th>
                    <th>Nilai</th>
                    <th>Deskripsi</th>
                    <th>Interpretasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach (['mae', 'mape', 'rmse'] as $metric)
                <tr>
                    <td>{{ $metricDescriptions[$metric]['nama'] }}</td>
                    <td>{{ number_format($metrics[$metric], 2) }}</td>
                    <td>{{ $metricDescriptions[$metric]['deskripsi'] }}</td>
                    <td>{{ $metricDescriptions[$metric]['interpretasi'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('forecastChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Data Aktual',
                        data: @json($chartData['actual']),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        pointRadius: 3,
                        fill: true,
                    },
                    {
                        label: 'Forecast (Fitted+Forecast)',
                        data: @json($chartData['forecast']),
                        borderColor: '#EF5350',
                        backgroundColor: 'rgba(239, 83, 80, 0.1)',
                        pointRadius: 2,
                        fill: false,
                        borderDash: [6,4],
                    },
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    });
</script>
@endsection