<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [];
        $brands = ['Yamaha', 'Honda', 'Suzuki', 'Kawasaki', 'No Brand'];
        $categories = [1, 2, 3, 4, 5];

        // Generate 25 produk untuk masing-masing huruf A, B, C, D
        foreach (range(1, 25) as $i) {
            $products[] = [
                'name' => "Aksesoris Motor $i",
                'brand' => $brands[array_rand($brands)],
                'category_id' => $categories[array_rand($categories)],
                'cost_price' => rand(10000, 50000),
                'selling_price' => rand(60000, 150000),
                'stock' => rand(10, 100),
            ];
            $products[] = [
                'name' => "Baut Motor $i",
                'brand' => $brands[array_rand($brands)],
                'category_id' => $categories[array_rand($categories)],
                'cost_price' => rand(1000, 5000),
                'selling_price' => rand(6000, 20000),
                'stock' => rand(10, 100),
            ];
            $products[] = [
                'name' => "Cairan Pembersih $i",
                'brand' => $brands[array_rand($brands)],
                'category_id' => $categories[array_rand($categories)],
                'cost_price' => rand(5000, 20000),
                'selling_price' => rand(25000, 50000),
                'stock' => rand(10, 100),
            ];
            $products[] = [
                'name' => "Disk Brake $i",
                'brand' => $brands[array_rand($brands)],
                'category_id' => $categories[array_rand($categories)],
                'cost_price' => rand(20000, 80000),
                'selling_price' => rand(90000, 200000),
                'stock' => rand(10, 100),
            ];
        }

        // Simpan ke database
        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}