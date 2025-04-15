@extends('layouts.app')

@section('title', 'Perbandingan Metode Peramalan')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Perbandingan Metode Peramalan</h5>
                <a href="{{ route('forecast.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Table Perbandingan Akurasi Metode Peramalan -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Perbandingan Akurasi Metode Peramalan</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="comparisonTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Metode Terbaik</th>
                                            <th>MAPE</th>
                                            <th>MAE</th>
                                            <th>RMSE</th>
                                            <th>Terakhir Diperbarui</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($comparisons as $comparison)
                                        <tr class="{{ $comparison->best_method == 'ses' ? 'table-success' : ($comparison->best_method == 'des' ? 'table-warning' : 'table-info') }}">
                                            <td>{{ $comparison->product->name }}</td>
                                            <td>
                                                @if($comparison->best_method == 'ses')
                                                    <span class="badge bg-primary">Single Exponential Smoothing</span>
                                                @elseif($comparison->best_method == 'des')
                                                    <span class="badge bg-success">Double Exponential Smoothing</span>
                                                @elseif($comparison->best_method == 'tes')
                                                    <span class="badge bg-warning">Triple Exponential Smoothing</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($evaluation->mape ?? '-', 2) }}%</td>
                                            <td>{{ number_format($evaluation->mae ?? '-', 2) }}</td>
                                            <td>{{ number_format($evaluation->rmse ?? '-', 2) }}</td>
                                            <td>{{ $comparison->date->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('forecast.' . $comparison->best_method) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-chart-line"></i> Gunakan Metode Ini
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Belum ada data perbandingan metode peramalan.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Tabel ini menampilkan metode peramalan terbaik untuk setiap produk berdasarkan nilai MAPE terendah.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Perbandingan MAPE -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Grafik Perbandingan MAPE</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="mapeChart" height="300"></canvas>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> 
                                Grafik ini membandingkan nilai MAPE (Mean Absolute Percentage Error) dari ketiga metode peramalan.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panduan Interpretasi -->
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-lightbulb me-2"></i> Panduan Interpretasi</h6>
                <ul class="mb-0">
                    <li><strong>MAPE (Mean Absolute Percentage Error)</strong> - Mengukur akurasi dalam bentuk persentase. Nilai < 10% menunjukkan akurasi yang sangat baik.</li>
                    <li><strong>MAE (Mean Absolute Error)</strong> - Mengukur rata-rata kesalahan absolut dalam unit asli data.</li>
                    <li><strong>RMSE (Root Mean Squared Error)</strong> - Memberikan penalti lebih besar pada error yang besar.</li>
                    <li>Metode terbaik adalah metode dengan nilai MAPE terendah.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Prepare data for MAPE comparison chart
        const products = [];
        const sesMape = [];
        const desMape = [];
        const tesMape = [];
        
        @foreach($products as $product)
            @php
                $sesEval = App\Models\ForecastEvaluation::where('product_id', $product->id)
                    ->where('method', 'ses')
                    ->first();
                $desEval = App\Models\ForecastEvaluation::where('product_id', $product->id)
                    ->where('method', 'des')
                    ->first();
                $tesEval = App\Models\ForecastEvaluation::where('product_id', $product->id)
                    ->where('method', 'tes')
                    ->first();
            @endphp
            
            @if($sesEval || $desEval || $tesEval)
                products.push("{{ $product->name }}");
                sesMape.push({{ $sesEval ? $sesEval->mape : 'null' }});
                desMape.push({{ $desEval ? $desEval->mape : 'null' }});
                tesMape.push({{ $tesEval ? $tesEval->mape : 'null' }});
            @endif
        @endforeach
        
        if (products.length > 0) {
            const ctx = document.getElementById('mapeChart').getContext('2d');
            const mapeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: products,
                    datasets: [
                        {
                            label: 'SES MAPE (%)',
                            data: sesMape,
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'DES MAPE (%)',
                            data: desMape,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'TES MAPE (%)',
                            data: tesMape,
                            backgroundColor: 'rgba(255, 159, 64, 0.6)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'MAPE (%)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Produk'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Perbandingan MAPE Antar Metode Peramalan'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toFixed(2) + '%';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
