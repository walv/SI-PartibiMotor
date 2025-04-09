@extends('layouts.app')

@section('title', 'Transaksi Penjualan Baru')

@section('styles')
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="m-0">Transaksi Penjualan Baru</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sales.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="invoice_number" class="form-label">Nomor Invoice</label>
                            <input id="invoice_number" type="text" class="form-control" name="invoice_number" value="{{ $invoice }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nama Customer</label>
                            <input id="customer_name" type="text" class="form-control" name="customer_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="service_price" class="form-label">Biaya Jasa</label>
                            <input id="service_price" type="number" class="form-control" name="service_price" min="0">
                        </div>

                        <div class="mb-3">
                            <label for="products" class="form-label">Produk</label>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="product-list">
                                    @foreach($products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                        <td>
                                            <input type="number" class="form-control" name="products[{{ $product->id }}][quantity]" min="1">
                                            <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                                        </td>
                                        <td id="subtotal-{{ $product->id }}"></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#product-list input[name^="products"]').on('input', function() {
                var quantity = $(this).val();
                var price = $(this).closest('tr').find('td:nth-child(2)').text().replace('Rp ', '').replace(/\./g, '');
                var subtotal = quantity * price;
                $(this).closest('tr').find('td:nth-child(4)').text('Rp ' + formatRupiah(subtotal));

                // Update total price
                var totalPrice = 0;
                $('#product-list tr').each(function() {
                    var subtotal = $(this).find('td:nth-child(4)').text().replace('Rp ', '').replace(/\./g, '');
                    totalPrice += parseInt(subtotal);
                });
                // Display total price
            });

            function formatRupiah(angka) {
                var number_string = angka.replace(/[^,\d]/g, '').toString(),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah;
            }
        });
    </script>
@endsection
