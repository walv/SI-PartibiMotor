<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;

class DummyForecastingSeeder extends Seeder
{
    public function run()
    {
        // Mendapatkan semua produk yang ada di database
        $products = Product::all();

        // Tentukan tanggal mulai (misalnya dari Januari 2022)
        $startDate = Carbon::parse('2022-01-01');

        // Tentukan rentang waktu (misalnya 12 bulan, dari Januari hingga Desember 2022)
        $endDate = Carbon::parse('2022-12-31');

        // Loop untuk tiap bulan untuk membuat transaksi penjualan
        for ($i = 0; $i < 12; $i++) {
            $date = $startDate->copy()->addMonths($i);

            // Misalnya untuk tiap bulan, kita buat 10 transaksi acak
            for ($j = 0; $j < 10; $j++) {
                $sale = Sale::create([
                    'invoice_number' => 'INV-' . $date->format('YmdHis') . rand(100, 999),
                    'date' => $date,
                    'customer_name' => 'Pelanggan ' . fake()->name(),
                    'total_price' => 0,
                    'user_id' => 1, // Bisa diubah sesuai dengan ID admin yang ada
                ]);

                $totalPrice = 0;

                // Loop untuk tiap produk yang akan dijual
                foreach ($products as $product) {
                    // Penjualan acak antara 2 sampai 5 produk
                    $quantity = rand(2, 5);
                    $subtotal = $product->selling_price * $quantity;
                    $totalPrice += $subtotal;

                    // Menambahkan detail transaksi penjualan
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->selling_price,
                        'subtotal' => $subtotal,
                    ]);
                }

                // Mengupdate total harga transaksi penjualan
                $sale->total_price = $totalPrice;
                $sale->save();

                // Bisa juga menambahkan kode untuk peramalan di sini jika diperlukan
            }
        }

        // Menambahkan Sales Aggregates untuk forecasting
        $this->createSalesAggregates($products);
    }

    /**
     * Fungsi untuk membuat Sales Aggregates
     */
    public function createSalesAggregates($products)
    {
        $startDate = Carbon::parse('2022-01-01');
        $endDate = Carbon::parse('2022-12-31');

        for ($i = 0; $i < 12; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $period = $date->format('Y-m'); // Format per bulan

            foreach ($products as $product) {
                // Menghitung penjualan bulanan (untuk keperluan forecasting)
                $totalSales = SaleDetail::whereHas('sale', function ($query) use ($date) {
                    $query->whereYear('date', $date->year)
                          ->whereMonth('date', $date->month);
                })
                ->where('product_id', $product->id)
                ->sum('quantity');

                $totalPrice = SaleDetail::whereHas('sale', function ($query) use ($date) {
                    $query->whereYear('date', $date->year)
                          ->whereMonth('date', $date->month);
                })
                ->where('product_id', $product->id)
                ->sum('subtotal');

                // Menyimpan data sales aggregate untuk bulan tersebut
                \App\Models\SalesAggregate::create([
                    'product_id' => $product->id,
                    'period' => $period,
                    'total_sales' => $totalSales,
                    'total_price' => $totalPrice,
                ]);
            }
        }
    }
}
