@extends('layouts.app')

@section('title', 'Daftar Penjualan')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Penjualan</h5>
                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Transaksi Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('sales.index') }}" method="GET" class="mb-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari invoice atau pelanggan" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Cari</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Kasir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_number }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($sale->date)) }}</td>
                            <td>{{ $sale->customer_name }}</td>
                            <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                            <td>{{ $sale->user->username ?? '-' }}</td>
                            <td>
                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('sales.invoice', $sale->id) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data penjualan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sales->hasPages())
<div class="mt-3 d-flex justify-content-center">
    {!! $sales->appends(request()->except('page'))->onEachSide(1)->links('pagination::bootstrap-4')->with('class', 'pagination pagination-sm justify-content-center') !!}
</div>
@endif
        </div>
    </div>
</div>
@endsection
