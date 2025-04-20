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
                    'date' => now(),
                ]);

                $totalProduct = 0;
                $totalService = 0;

                // Simpan data produk ke tabel `sale_details`
                $products = json_decode($row[2], true); // Kolom 3: JSON Produk
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $productModel = Product::findOrFail($product['id']);

                        SaleDetail::create([
                            'sale_id' => $sale->id,
                            'product_id' => $productModel->id,
                            'quantity' => $product['quantity'],
                            'price' => $productModel->selling_price,
                            'subtotal' => $productModel->selling_price * $product['quantity'],
                        ]);

                        $totalProduct += $productModel->selling_price * $product['quantity'];
                        $productModel->decrement('stock', $product['quantity']);
                    }
                }

                // Simpan data jasa ke tabel `sale_service_details`
$services = json_decode($row[3], true); // Kolom 4: JSON Jasa
if (!empty($services)) {
    foreach ($services as $service) {
        $serviceModel = Service::find($service['id']);

        // Validasi apakah jasa ditemukan
        if (!$serviceModel) {
            throw new \Exception("Jasa dengan ID " . $service['id'] . " tidak ditemukan.");
        }

        SaleServiceDetail::create([
            'sale_id' => $sale->id,
            'service_id' => $serviceModel->id,
            'price' => $service['price'],
            'subtotal' => $service['price'],
        ]);
    

                        $totalService += $service['price'];
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