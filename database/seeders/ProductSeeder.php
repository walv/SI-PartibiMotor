<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'id' => 201,
                'category_id' => 1, // Oli
                'name' => 'MPX 2',
                'brand' => 'MPX',
                'description' => 'Oli MPX 2 untuk motor matic',
                'cost_price' => 40000,
                'selling_price' => 45000,
                'stock' => 100
            ],
            [
                'id' => 267,
                'category_id' => 1, // Oli
                'name' => 'Pikoli Racing',
                'brand' => 'Pikoli',
                'description' => 'Oli Pikoli untuk performa tinggi',
                'cost_price' => 60000,
                'selling_price' => 70000,
                'stock' => 100
            ],
            [
                'id' => 268,
                'category_id' => 1, // Oli
                'name' => 'Federal Matic',
                'brand' => 'Federal',
                'description' => 'Oli Federal matic terbaik',
                'cost_price' => 40000,
                'selling_price' => 45000,
                'stock' => 100
            ],
            [
                'id' => 203,
                'category_id' => 1, // Oli
                'name' => 'Yamalube Matic',
                'brand' => 'Yamalube',
                'description' => 'Oli resmi Yamalube untuk motor Yamaha',
                'cost_price' => 40000,
                'selling_price' => 45000,
                'stock' => 100
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['id' => $product['id']],
                array_merge($product, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
