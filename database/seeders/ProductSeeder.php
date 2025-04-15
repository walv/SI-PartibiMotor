<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Data produk untuk bengkel umum
        $products = [
            [
                'name' => 'Kampas Rem Depan',
                'brand' => 'Yamaha', // Merek
                'category_id' => 1,  // ID Kategori Produk
                'cost_price' => 25000, // Harga Modal
                'selling_price' => 35000, // Harga Jual
                'stock' => 100, // Jumlah Stok
            ],
            [
                'name' => 'Kampas Rem Belakang',
                'brand' => 'Shell', // Merek
                'category_id' => 1,  // ID Kategori Produk
                'cost_price' => 22000,
                'selling_price' => 32000,
                'stock' => 50,
            ],
            [
                'name' => 'Rantai Sepeda Motor',
                'brand' => null, // Tanpa merek (Produk KW)
                'category_id' => 2,  // ID Kategori Produk
                'cost_price' => 50000,
                'selling_price' => 65000,
                'stock' => 200,
            ],
            [
                'name' => 'Busi NGK',
                'brand' => 'NGK', // Merek
                'category_id' => 3,  // ID Kategori Produk
                'cost_price' => 15000,
                'selling_price' => 20000,
                'stock' => 120,
            ],
            [
                'name' => 'Cairan Pendingin Radiator',
                'brand' => 'No Brand', // Tanpa merek
                'category_id' => 4,  // ID Kategori Produk
                'cost_price' => 18000,
                'selling_price' => 25000,
                'stock' => 75,
            ],
            [
                'name' => 'Ban Motor',
                'brand' => 'IRC', // Merek
                'category_id' => 5,  // ID Kategori Produk
                'cost_price' => 180000,
                'selling_price' => 250000,
                'stock' => 50,
            ],
        ];

        // Loop untuk menyimpan data produk ke dalam tabel products
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
