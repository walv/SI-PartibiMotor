@extends('layouts.app') 

@section('title', 'Detail Penjualan') 

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Penjualan</h5>
                <div>
                    <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-info btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Cetak Invoice
                    </a>
                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus penjualan ini?')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
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
                            <th width="30%">Nomor Invoice</th>
                            <td>{{ $sale->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Transaksi</th>
                            <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Nama Pelanggan</th>
                            <td>{{ $sale->customer_name ?? 'Pelanggan Umum' }}</td>
                        </tr>
                        <tr>
                            <th>Kasir</th>
                            <td>{{ $sale->user->name }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 text-right">
                    <div class="bg-light p-3 rounded">
                        <h5>Ringkasan Pembayaran</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Total Produk:</th>
                                <td>Rp {{ number_format($sale->saleDetails->sum('subtotal'), 0, ',', '.') }}</td>
                            </tr>
                            @if($sale->saleServiceDetails->isNotEmpty())
                            <tr>
                                <th>Total Jasa:</th>
                                <td>Rp {{ number_format($sale->saleServiceDetails->sum('subtotal'), 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="font-weight-bold">
                                <th>Total Pembayaran:</th>
                                <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>Produk</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleDetails as $detail)
                        <tr>
                            <td>{{ $detail->product->name }}</td>
                            <td class="text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $detail->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($sale->saleServiceDetails->isNotEmpty())
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>Jasa</th>
                            <th class="text-right">Harga</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleServiceDetails as $serviceDetail)
                        <tr>
                            <td>{{ $serviceDetail->service->name }}</td>
                            <td class="text-right">Rp {{ number_format($serviceDetail->price, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $serviceDetail->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($serviceDetail->subtotal, 0, ',', '.') }}</td>
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