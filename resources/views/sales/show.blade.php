@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Penjualan</h5>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Informasi Transaksi</h6>
                    <p><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
                    <p><strong>Tanggal:</strong> {{ $sale->date->format('d/m/Y H:i') }}</p>
                    <p><strong>Customer:</strong> {{ $sale->customer_name }}</p>
                    <p><strong>Kasir:</strong> {{ $sale->user->username }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6>Total Transaksi</h6>
                    <h3 class="text-primary">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</h3>
                    <p><strong>Biaya Jasa:</strong> Rp {{ number_format($sale->service_price, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->details as $detail)
                        <tr>
                            <td>{{ $detail->product->name }}</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Produk:</strong></td>
                            <td>Rp {{ number_format($sale->total_price - $sale->service_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Biaya Jasa:</strong></td>
                            <td>Rp {{ number_format($sale->service_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print"></i> Cetak Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
