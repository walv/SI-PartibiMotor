<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Facades\Log;

class SalesExport implements FromCollection, WithMapping, WithHeadings, WithCustomStartCell
{
    /**
    * Mengambil data untuk export
    * @return \Illuminate\Support\Collection
    */
    protected $month;
    protected $year;
    protected $totalSales = 0;
    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }
    public function collection()
    {
        return Sale::with(['saleDetails.product', 'saleServiceDetails.service'])
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->select('id', 'invoice_number', 'customer_name', 'total_price', 'date')
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
        $productNames = $sale->saleDetails->map(function ($detail) {
            return $detail->product->name . ' (' . $detail->quantity . ')';
        })->join(', ');

        // Log data jasa
        Log::info('Jasa:', $sale->services->toArray());

        // Gabungkan nama jasa
        $serviceNames = $sale->saleServiceDetails->map(function ($service) {
            return $service->service->name . ' (' . $service->price . ')';
        })->join(', ');

        // Tambahin total penjualan ke variabel totalSales
        $this->totalSales += $sale->total_price;
        
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
    public function footer(): array
    {
        return [
            '', // Kosong untuk ID
            '', // Kosong untuk Nomor Invoice
            'Total Keseluruhan', // Label untuk total
            $this->totalSales, // Total keseluruhan penjualan
            '', // Kosong untuk Tanggal Transaksi
            '', // Kosong untuk Nama Produk
            '', // Kosong untuk Nama Jasa
        ];
    }
    public function startCell(): string
    {
        return 'A1'; // Mulai dari sel A1
    }

    public function collectionWithFooter()
    {
        $data = $this->collection()->toArray();
        $data[] = $this->footer(); // Tambahkan baris total di akhir
        return collect($data);
    }
}