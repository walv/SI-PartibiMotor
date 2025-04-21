<?php

namespace App\Imports;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleServiceDetail;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class SalesImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // Lewati header (baris pertama)
                if ($index === 0) {
                    continue;
                }

                // Simpan data ke tabel `sales`
                $sale = Sale::create([
                    'invoice_number' => $row[0], // Kolom 1: Nomor Invoice
                    'customer_name' => $row[1], // Kolom 2: Nama Pelanggan
                    'total_price' => 0, // Akan dihitung nanti
                    'user_id' => auth()->id(),
                    'date' => $row(2),
                ]);

                $totalProduct = 0;
                $totalService = 0;

                // proses dulu data produk
                for ($i = 2; $i <= 5; $i += 2) { // Kolom 3-6: Produk dan Jumlah
                    if (!empty($row[$i]) && !empty($row[$i + 1])) {
                        $productName = $row[$i];
                        $quantity = $row[$i + 1];
    
                        $productModel = Product::firstOrCreate(
                            ['name' => $productName],
                            ['selling_price' => 0, 'stock' => 100] // Default jika produk baru
                        );

                        SaleDetail::create([
                            'sale_id' => $sale->id,
                            'product_id' => $productModel->id,
                            'quantity' => $quantity,
                            'price' => $productModel->selling_price,
                            'subtotal' => $productModel->selling_price * $quantity,
                        ]);

                        $totalProduct += $productModel->selling_price * $quantity;
                        $productModel->decrement('stock', $quantity);
                    }
                }

                // proses data jasa ke tabel `sale_service_details`
$services = json_decode($row[3], true); // Kolom 4: JSON Jasa
for ($i = 6; $i <= 9; $i += 2) { // Kolom 7-10: Jasa dan Harga
    if (!empty($row[$i]) && !empty($row[$i + 1])) {
        $serviceName = $row[$i];
        $price = $row[$i + 1];

        $serviceModel = Service::firstOrCreate(
            ['name' => $serviceName],
            ['price' => $price] // Default jika jasa baru
        );

        SaleServiceDetail::create([
            'sale_id' => $sale->id,
            'service_id' => $serviceModel->id,
            'price' => $price,
            'subtotal' => $price,
        ]);
    

                        $totalService += $price;
                    }
                }

                // Update total harga di tabel `sales`
                $sale->update([
                    'total_price' => $totalProduct + $totalService,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}