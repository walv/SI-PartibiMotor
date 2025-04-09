@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Produk</h5>
                <div>
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nama Produk</th>
                            <td>{{ $product->name }}</td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>{{ $product->category->name }}</td>
                        </tr>
                        <tr>
                            <th>Brand</th>
                            <td>{{ $product->brand ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Harga Beli</th>
                            <td>Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Harga Jual</th>
                            <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Stok</th>
                            <td>{{ $product->stock }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Deskripsi</h6>
                        </div>
                        <div class="card-body">
                            {{ $product->description ?: 'Tidak ada deskripsi' }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Riwayat Pergerakan Stok</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Jumlah</th>
                                            <th>Stok Sebelum</th>
                                            <th>Stok Sesudah</th>
                                            <th>Referensi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($product->inventoryMovements()->latest('date')->take(10)->get() as $movement)
                                        <tr>
                                            <td>{{ date('d/m/Y H:i', strtotime($movement->date)) }}</td>
                                            <td>
                                                @if($movement->movement_type == 'in')
                                                    <span class="badge bg-success">Masuk</span>
                                                @else
                                                    <span class="badge bg-danger">Keluar</span>
                                                @endif
                                            </td>
                                            <td>{{ $movement->quantity }}</td>
                                            <td>{{ $movement->stock_before }}</td>
                                            <td>{{ $movement->stock_after }}</td>
                                            <td>{{ $movement->reference }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data pergerakan stok</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
