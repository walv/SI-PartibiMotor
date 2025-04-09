@extends('layouts.app')

@section('title', 'Daftar Penjualan')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="m-0">Daftar Penjualan</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>{{ $sale->date }}</td>
                                <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-primary">Detail</a>
                                    <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-sm btn-secondary">Invoice</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
