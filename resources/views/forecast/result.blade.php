@extends('layouts.app')

@section('title', 'Hasil Peramalan - SES')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h5>Hasil Peramalan dengan Single Exponential Smoothing (SES)</h5>
        </div>
        <div class="card-body">
            <h6>Peramalan untuk Produk: {{ $product->name }}</h6>

            <!-- Menampilkan hasil peramalan SES -->
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Data Aktual</th>
                        <th>Hasil Peramalan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($result['actual'] as $index => $actual)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $actual }}</td>
                            <td>{{ number_format($result['fitted'][$index], 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach($result['forecast'] as $index => $fc)
                        <tr>
                            <td>Peramalan {{ $index + 1 }}</td>
                            <td>-</td>
                            <td>{{ number_format($fc, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Menampilkan grafik SES -->
            <canvas id="forecastChart"></canvas>
        </div>
    </div>

    <!-- Menampilkan metrik evaluasi peramalan -->
    @if(isset($metrics))
        <div class="mt-4">
            <h5>Metrik Peramalan</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>MSE</th>
                        <th>MAE</th>
                        <th>MAPE</th>
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
    @endif
</div>

@if(isset($chartData))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('forecastChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),  // Menampilkan periode dalam grafik
            datasets: [{
                label: 'Data Aktual',
                data: @json($chartData['actual']),  // Data aktual
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Hasil Peramalan',
                data: @json($chartData['forecast']),  // Hasil peramalan
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        }
    });
</script>
@endif
@endsection
