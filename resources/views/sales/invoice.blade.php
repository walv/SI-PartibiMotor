@extends('layouts.app') 

@section('title', 'Invoice Penjualan') 

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">INVOICE</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h5 class="mb-3">Dari:</h5>
                            <h3 class="text-dark mb-1">PARTI BIMOTOR</h3>
                            <div>Jl. Liga Utara 1 Blok C2 No.58</div>
                            <div>Telp: 021-12345678</div>
                            <div>Email: info@partibimotor.com</div>
                        </div>
                        <div class="col-sm-6">
                            <h5 class="mb-3">Kepada:</h5>
                            <h3 class="text-dark mb-1">{{ $sale->customer_name }}</h3>
                            <div>Invoice: {{ $sale->invoice_number }}</div>
                            <div>Tanggal: {{ date('d/m/Y H:i', strtotime($sale->date)) }}</div>
                            <div>Kasir: {{ $sale->user->name }}</div>
                        </div>
                    </div>
                    
                    <div class="table-responsive-sm">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-right">Harga</th>
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
                            <tfoot>
                                @if($sale->service_price > 0)
                                <tr>
                                    <th colspan="3" class="text-right">Biaya Servis:</th>
                                    <th class="text-right">Rp {{ number_format($sale->service_price, 0, ',', '.') }}</th>
                                </tr>
                                @endif
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mt-4">
                                <p class="mb-2">Terima kasih telah berbelanja di PartiBi motor.</p>
                                <p class="mb-2">Barang yang sudah dibeli tidak dapat dikembalikan.</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mt-4 text-right">
                                <div class="mb-2">Hormat Kami,</div>
                                <br><br><br>
                                <div>PARTIBI MOTOR</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12 text-center">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Invoice
            </button>
            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .btn {
            display: none;
        }
    }
</style>
@endsection
