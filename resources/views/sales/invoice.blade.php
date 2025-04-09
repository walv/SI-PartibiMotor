@extends('layouts.app')

@section('title', 'Invoice Penjualan')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="m-0">Invoice Penjualan</h4>
                </div>
                <div class="card-body">
                    <h5>Invoice Number: {{ $sale->invoice_number }}</h5>
                    <h5>Tanggal: {{ $sale->date }}</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->details as $detail)
                            <tr>
                                <td>{{ $detail->product->name }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p>Total: Rp {{ number_format($sale->total_price, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
