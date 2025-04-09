<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Oli',
            'Sparepart Mesin',
            'Sparepart Kelistrikan',
            'Sparepart Rangka',
            'Aksesoris',
            'Ban',
            'Helm',
            'Lain-lain',
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
            ]);
        }
    }
}
