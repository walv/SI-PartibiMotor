<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-details {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>SI_PARTIBIMOTOR</h1>
        <h2>Invoice Penjualan</h2>
    </div>

    <div class="invoice-details">
        <p><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
        <p><strong>Tanggal:</strong> {{ $sale->date->format('d/m/Y H:i') }}</p>
        <p><strong>Customer:</strong> {{ $sale->customer_name }}</p>
        <p><strong>Kasir:</strong> {{ $sale->user->username }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $detail)
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
                <td colspan="3" style="text-align: right;"><strong>Total Produk:</strong></td>
                <td>Rp {{ number_format($sale->total_price - $sale->service_price, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Biaya Jasa:</strong></td>
                <td>Rp {{ number_format($sale->service_price, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Terima kasih atas pembelian Anda di SI_PARTIBIMOTOR</p>
    </div>
</body>
</html>
