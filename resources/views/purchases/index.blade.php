@extends('layouts.app')

@section('title', 'Daftar Pembelian')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pembelian</h5>
                <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Transaksi Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- // // FORM PENCARIAN DAN FILTER --}}
            <form action="{{ route('purchases.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari invoice atau supplier..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Total</th>
                            <th>User</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->invoice_number }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($purchase->date)) }}</td>
                            <td>{{ $purchase->supplier_name }}</td>
                            <td>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                            <td>{{ $purchase->user->username }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok produk akan dikurangi.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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

            <div class="mt-3">
                {{ $purchases->appends(request()->query())->links() }}
                {{-- // // Menjaga agar filter tetap aktif saat berpindah halaman --}}
            </div>
        </div>
    </div>
</div>
@endsection
