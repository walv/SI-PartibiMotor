@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Laporan Keuangan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.financial') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('reports.financial') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Pendapatan</h6>
                            <h2 class="mb-0">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h2>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Pengeluaran</h6>
                            <h2 class="mb-0">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h2>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Keuntungan</h6>
                            <h2 class="mb-0">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h2>
                        </div>
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Margin Keuntungan</h6>
                            <h2 class="mb-0">{{ number_format($profitMargin, 2) }}%</h2>
                        </div>
                        <i class="fas fa-percentage fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Grafik Keuangan</h5>
        </div>
        <div class="card-body">
            <canvas id="financialChart" height="300"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Keuangan Harian</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Pendapatan</th>
                            <th>Pengeluaran</th>
                            <th>Keuntungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($financialData as $data)
                        <tr>
                            <td>{{ date('d/m/Y', strtotime($data['date'])) }}</td>
                            <td class="text-success">Rp {{ number_format($data['income'], 0, ',', '.') }}</td>
                            <td class="text-danger">Rp {{ number_format($data['expense'], 0, ',', '.') }}</td>
                            <td class="{{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($data['profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="float-end">
        <a href="{{ route('reports.financial.export', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Prepare data for chart
        const dates = @json($chartData['dates']);
        const incomeData = @json($chartData['income']);
        const expenseData = @json($chartData['expense']);
        const profitData = @json($chartData['profit']);
        
        const ctx = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: incomeData,
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pengeluaran',
                        data: expenseData,
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Keuntungan',
                        data: profitData,
                        type: 'line',
                        backgroundColor: 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
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
                                label += 'Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection