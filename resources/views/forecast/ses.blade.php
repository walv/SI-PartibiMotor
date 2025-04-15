@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h5>Single Exponential Smoothing (SES)</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('forecast.calculate') }}" method="POST">
                @csrf
                <input type="hidden" name="method" value="ses">
                
                <div class="form-group">
                    <label>Pilih Produk</label>
                    <select name="product_id" class="form-control" required>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Penjelasan Alpha -->
                <div class="form-group">
                    <label>Alpha (α)</label>
                    <input type="number" name="alpha" class="form-control" 
                           min="0.01" max="0.99" step="0.01" value="0.3" required>
                    <small class="form-text text-muted">
                        Alpha (α) mengontrol seberapa banyak data terbaru mempengaruhi peramalan. 
                        Pilih nilai yang sesuai dengan pola penjualan Anda:
                    </small>
                    <ul class="mt-2">
                        <li><strong>Alpha rendah (0.1 - 0.3)</strong> - Untuk penjualan yang <strong>stabil</strong> atau tidak banyak fluktuasi.</li>
                        <li><strong>Alpha sedang (0.4 - 0.6)</strong> - Untuk penjualan yang <strong>fluktuatif</strong> namun tidak terlalu drastis.</li>
                        <li><strong>Alpha tinggi (0.7 - 0.9)</strong> - Untuk penjualan yang sangat <strong>fluktuatif</strong> atau sering mengalami perubahan besar.</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label>Jumlah Periode Forecast</label>
                    <input type="number" name="forecast_periods" class="form-control" 
                           min="1" max="12" value="3" required>
                </div>

                <div class="form-group">
                    <label for="start_period">Pilih Periode Awal</label>
                    <input type="month" name="start_period" id="start_period" class="form-control" required>
                    <small class="form-text text-muted">Klik ikon kalender di samping untuk memilih bulan dan tahun</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Hitung Peramalan</button>
            </form>

            @if(isset($result))
            <div class="mt-4">
                <h5>Hasil Peramalan</h5>
                <canvas id="forecastChart"></canvas>
                
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

                <!-- Menampilkan MAPE, MAE, dan RMSE -->
                <div class="mt-4">
                    <h6>Metrik Evaluasi:</h6>
                    <ul>
                        <li><strong>MAPE:</strong> {{ number_format($metrics['mape'], 2) }}%</li>
                        <li><strong>MAE:</strong> {{ number_format($metrics['mae'], 2) }}</li>
                        <li><strong>RMSE:</strong> {{ number_format($metrics['rmse'], 2) }}</li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger mt-3">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(isset($chartData))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('forecastChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Data Aktual',
                data: @json($chartData['actual']),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Hasil Peramalan',
                data: @json($chartData['forecast']),
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        }
    });
</script>
@endif
@endsection
