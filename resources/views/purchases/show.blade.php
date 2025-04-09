@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pembelian</h5>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Informasi Transaksi</h6>
                    <p><strong>Invoice:</strong> {{ $purchase->invoice_number }}</p>
                    <p><strong>Tanggal:</strong> {{ $purchase->date->format('d/m/Y H:i') }}</p>
                    <p><strong>Supplier:</strong> {{ $purchase->supplier_name }}</p>
                    <p><strong>User:</strong> {{ $purchase->user->username }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6>Total Transaksi</h6>
                    <h3 class="text-primary">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</h3>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga Beli</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->details as $detail)
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
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4">
                <p class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Transaksi pembelian ini telah menambahkan stok produk secara otomatis.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
