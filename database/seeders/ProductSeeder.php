<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $oliCategoryId = Category::where('name', 'Oli')->first()->id;
        $mesinCategoryId = Category::where('name', 'Sparepart Mesin')->first()->id;
        $kelistrikanCategoryId = Category::where('name', 'Sparepart Kelistrikan')->first()->id;
        $rangkaCategoryId = Category::where('name', 'Sparepart Rangka')->first()->id;
        $banCategoryId = Category::where('name', 'Ban')->first()->id;

        // Sample products
        $products = [
            [
                'name' => 'Oli Yamalube 1L',
                'brand' => 'Yamaha',
                'description' => 'Oli mesin motor Yamaha 4-tak 1 Liter',
                'category_id' => $oliCategoryId,
                'cost_price' => 45000,
                'selling_price' => 55000,
                'stock' => 20,
            ],
            [
                'name' => 'Oli Shell Advance AX7 1L',
                'brand' => 'Shell',
                'description' => 'Oli mesin motor 4-tak 1 Liter',
                'category_id' => $oliCategoryId,
                'cost_price' => 60000,
                'selling_price' => 75000,
                'stock' => 15,
            ],
            [
                'name' => 'Busi NGK Iridium',
                'brand' => null,
                'description' => 'Busi motor NGK tipe Iridium',
                'category_id' => $kelistrikanCategoryId,
                'cost_price' => 85000,
                'selling_price' => 100000,
                'stock' => 30,
            ],
            [
                'name' => 'Rantai Motor DID Gold',
                'brand' => 'DID',
                'description' => 'Rantai motor DID tipe Gold 428H',
                'category_id' => $rangkaCategoryId,
                'cost_price' => 180000,
                'selling_price' => 220000,
                'stock' => 10,
            ],
            [
                'name' => 'Ban Luar Corsa R46',
                'brand' => 'Corsa',
                'description' => 'Ban luar motor Corsa ukuran 80/90-17',
                'category_id' => $banCategoryId,
                'cost_price' => 160000,
                'selling_price' => 195000,
                'stock' => 8,
            ],
            [
                'name' => 'Kampas Rem Depan Yamaha',
                'brand' => null,
                'description' => 'Kampas rem depan untuk motor Yamaha',
                'category_id' => $rangkaCategoryId,
                'cost_price' => 35000,
                'selling_price' => 50000,
                'stock' => 25,
            ],
            [
                'name' => 'Piston Kit Yamaha',
                'brand' => 'Yamaha',
                'description' => 'Piston kit untuk motor Yamaha',
                'category_id' => $mesinCategoryId,
                'cost_price' => 250000,
                'selling_price' => 320000,
                'stock' => 5,
            ],
            [
                'name' => 'CDI BRT Racing',
                'brand' => 'BRT',
                'description' => 'CDI Racing untuk performa motor',
                'category_id' => $kelistrikanCategoryId,
                'cost_price' => 300000,
                'selling_price' => 375000,
                'stock' => 7,
            ],
            [
                'name' => 'Filter Udara Racing',
                'brand' => null,
                'description' => 'Filter udara racing untuk performa motor',
                'category_id' => $mesinCategoryId,
                'cost_price' => 75000,
                'selling_price' => 95000,
                'stock' => 12,
            ],
            [
                'name' => 'Ban Dalam IRC',
                'brand' => 'IRC',
                'description' => 'Ban dalam motor IRC ukuran 17',
                'category_id' => $banCategoryId,
                'cost_price' => 40000,
                'selling_price' => 55000,
                'stock' => 20,
            ],
        ];
        

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
