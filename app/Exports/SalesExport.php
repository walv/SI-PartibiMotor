<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Log;

class SalesExport implements FromCollection, WithMapping, WithHeadings
{
    /**
    * Mengambil data untuk export
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Sale::with(['products.product', 'services.service'])
            ->select('id', 'invoice_number', 'customer_name', 'total_price', 'created_at')
            ->get();
    }

    /**
    * Menentukan header tabel di file Excel
    * @return array
    */
    public function headings(): array
    {
        return [
            'ID',
            'Nomor Invoice',
            'Nama Pelanggan',
            'Total Harga',
            'Tanggal Transaksi',
            'Nama Produk',
            'Nama Jasa',
        ];
    }

    /**
    * Memetakan data untuk setiap baris di file Excel
    * @param $sale
    * @return array
    */
    public function map($sale): array
    {
        // Log data produk
        Log::info('Produk:', $sale->products->toArray());

        // Gabungkan nama produk
        $productNames = $sale->products->map(function ($product) {
            return $product->product->name . ' (' . $product->quantity . ')';
        })->join(', ');

        // Log data jasa
        Log::info('Jasa:', $sale->services->toArray());

        // Gabungkan nama jasa
        $serviceNames = $sale->services->map(function ($service) {
            return $service->service->name . ' (' . $service->price . ')';
        })->join(', ');

        return [
            $sale->id,
            $sale->invoice_number,
            $sale->customer_name,
            $sale->total_price,
            $sale->created_at->format('Y-m-d H:i:s'), // Format tanggal
            $productNames, // Nama produk
            $serviceNames, // Nama jasa
        ];
    }
}