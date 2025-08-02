@extends('layouts.app') 

@section('title', 'Detail Pembelian') 

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pembelian #{{ $purchase->invoice_number }}</h5>
                <div>
                    <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus pembelian ini? Stok produk akan dikurangi.')">
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
            <!-- SECTION 1: INFORMASI UTAMA -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Transaksi</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%">Tanggal Pencatatan Pembelian </th>
                                    <td>{{ date('d/m/Y H:i', strtotime($purchase->date)) }}</td>
                                </tr>
                                <tr>
                                    <th>Dicatat Oleh </th>
                                    <td>{{ $purchase->user->name }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-truck"></i> Informasi Supplier</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%">Nama Supplier</th>
                                    <td><strong>{{ $purchase->supplier_name }}</strong></td>
                                </tr>
                                @if($purchase->notes)
                                <tr>
                                    <th>Catatan</th>
                                    <td class="text-muted">
                                        <i class="fas fa-sticky-note"></i> {{ $purchase->notes }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 2: DETAIL PRODUK -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-boxes"></i> Daftar Produk</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Produk</th>
                                    <th width="15%">Harga Beli</th>
                                    <th width="10%">Qty</th>
                                    <th width="15%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseDetails as $index => $detail)
                                <tr>
                                    <td>{{ $index+1 }}</td>
                                    <td>{{ $detail->product->name }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $detail->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 3: BUKTI STRUK (JIKA ADA) -->
            @if($purchase->photo_struk)
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-receipt"></i> Bukti Pembelian</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $purchase->photo_struk) }}" 
                             alt="Struk Pembelian" 
                             class="img-fluid rounded border"
                             style="max-height: 300px;">
                    </div>
                    <a href="{{ asset('storage/' . $purchase->photo_struk) }}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-primary">
                       <i class="fas fa-expand"></i> Lihat Fullscreen
                    </a>
                    <a href="{{ asset('storage/' . $purchase->photo_struk) }}" 
                       download 
                       class="btn btn-sm btn-outline-secondary">
                       <i class="fas fa-download"></i> Unduh
                    </a>
                </div>
            </div>
            @endif
            
            <!-- SECTION 4: INFORMASI STOK -->
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle"></i> 
                <strong>Pembaruan Stok:</strong> Transaksi ini telah menambahkan stok ke inventory secara otomatis.
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }
    .table-borderless th {
        font-weight: 500;
    }
</style>
@endsection