@extends('layouts.app') 

@section('title', 'Detail Penjualan') 

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Penjualan</h5>
                <div>
                    <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-info" target="_blank">
                        <i class="fas fa-print"></i> Cetak Invoice
                    </a>
                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus penjualan ini?')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Invoice</th>
                            <td>{{ $sale->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ date('d/m/Y H:i', strtotime($sale->date)) }}</td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td>{{ $sale->customer_name }}</td>
                        </tr>
                        <tr>
                            <th>Kasir</th>
                            <td>{{ $sale->user->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->name }}</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($sale->saleServiceDetails->isNotEmpty())
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Jasa</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleServiceDetails as $serviceDetail)
                        <tr>
                            <td>{{ $serviceDetail->service->name }}</td>
                            <td>Rp {{ number_format($serviceDetail->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($serviceDetail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
