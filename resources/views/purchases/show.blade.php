@extends('layouts.app') 

@section('title', 'Detail Pembelian') 

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pembelian</h5>
                <div>
                    <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pembelian ini?')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
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
                            <td>{{ $purchase->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ date('d/m/Y H:i', strtotime($purchase->date)) }}</td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>{{ $purchase->supplier_name }}</td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td>{{ $purchase->user->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga Beli</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->purchaseDetails as $detail)
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
                            <th colspan="3" class="text-right">Total:</th>
                            <th>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle"></i> Transaksi pembelian ini telah menambahkan stok produk secara otomatis.
            </div>
        </div>
    </div>
</div>
@endsection
