@php
use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Forecast - {{ $product->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin-bottom: 5px; }
        .header p { margin-top: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-bottom: 30px; }
        .chart-container { text-align: center; margin: 20px 0; }
        .chart-container img { max-width: 100%; height: auto; }
        .note {
    font-size: 0.8em;
    color: #666;
    margin-top: 5px;
    font-style: italic;
}
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Peramalan Penjualan</h1>
        <p>Dibuat pada: {{ $createdAt->format('d F Y H:i') }}</p>
    </div>

    <div class="section">
        <h3>Informasi Produk</h3>
        <table>
            <tr>
                <th>Nama Produk</th>
                <td>{{ $product->name }}</td>
            </tr>
            <tr>
                <th>Kategori</th>
                <td>{{ $product->category->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Parameter Alpha (α)</th>
                <td>{{ $alpha }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Grafik Peramalan</h3>
        <div class="chart-container">
            <img src="{{ $chartImage }}" alt="Grafik Peramalan">
        </div>
    </div>

    <div class="section">
        <h3>Data Historis & Fitted</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Periode</th>
                    <th>Aktual</th>
                    <th>Fitted</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fittedData as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($item->period)->format('M Y') }}</td>
                    <td>{{ number_format($item->actual) }}</td>
                    <td>{{ number_format($item->forecast, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(count($forecastData) > 0)
    <div class="section">
        <h3>Hasil Peramalan Mendatang</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Periode</th>
                    <th>Forecast</th>
                </tr>
            </thead>
            <tbody>
                @foreach($forecastData as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ Carbon::parse($item->period)->format('M Y') }}</td>
                    <td>{{ number_format($item->forecast, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @if(isset($metrics))
<div class="section">
    <h3>Metrik Evaluasi Peramalan</h3>
    <table>
        <thead>
            <tr>
                <th>MSE</th>
                <th>MAE</th>
                <th>MAPE (%)</th>
                <th>RMSE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $metrics['mse'] }}</td>
                <td>{{ $metrics['mae'] }}</td>
                <td>{{ $metrics['mape'] }}</td>
                <td>{{ $metrics['rmse'] }}</td>
            </tr>
        </tbody>
    </table>
    <p class="note">* MSE: Mean Squared Error, MAE: Mean Absolute Error, MAPE: Mean Absolute Percentage Error, RMSE: Root Mean Squared Error</p>
</div>
<h6>Akurasi Prediksi:</h6>
<table border="1" cellpadding="5">
    <tr>
        <th width="25%">Metrik</th>
        <th width="20%">Nilai</th>
        <th width="55%">Deskripsi</th>
    </tr>
    @foreach (['mae', 'mape', 'rmse'] as $metric)
    <tr>
        <td>{{ $metricDescriptions[$metric]['nama'] }}</td>
        <td>{{ number_format($metrics[$metric], 2) }}</td>
        <td>
            {{ $metricDescriptions[$metric]['deskripsi'] }}<br>
            <small><strong>Interpretasi:</strong> {{ $metricDescriptions[$metric]['interpretasi'] }}</small>
        </td>
    </tr>
    @endforeach
</table>
@endif
</body>
</html>