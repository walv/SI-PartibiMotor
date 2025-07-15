@extends('layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Laporan Pembelian</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.purchases') }}" method="GET" class="row g-3">
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
                    <a href="{{ route('reports.purchases') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Pembelian</h6>
                            <h2 class="mb-0">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</h2>
                        </div>
                        <i class="fas fa-shopping-bag fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Jumlah Transaksi</h6>
                            <h2 class="mb-0">{{ $totalTransactions }}</h2>
                        </div>
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Grafik Pembelian Harian</h5>
                </div>
                <div class="card-body">
                    <canvas id="purchasesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Produk Terbanyak Dibeli</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $product)
                                <tr>
                                    <td>{{ $product->product->name }}</td>
                                    <td>{{ $product->total_quantity }}</td>
                                    <td>Rp {{ number_format($product->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Transaksi Pembelian</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Petugas</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->invoice_number }}</td>
                            <td>{{ date('d/m/Y', strtotime($purchase->date)) }}</td>
                            <td>{{ $purchase->supplier_name }}</td>
                            <td>{{ $purchase->user->username }}</td>
                            <td>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data pembelian</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 <div class="float-end">
    <div class="btn-group">
        <a href="{{ route('reports.purchases.export', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <button class="btn btn-primary btn-sm ms-2" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Prepare data for chart
        const dates = @json(array_keys($chartData));
        const purchasesData = @json(array_values($chartData));
        
        const ctx = document.getElementById('purchasesChart').getContext('2d');
        const purchasesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Pembelian Harian',
                    data: purchasesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
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
                                return 'Pembelian: Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
