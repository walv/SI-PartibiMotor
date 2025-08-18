<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Data penjualan per bulan dari Mei 2024 hingga Juli 2025
            $salesData = [
                '202405' => [56, 11, 19, 28],    // Mei 2024
                '202406' => [72, 16, 17, 26],     // Juni 2024
                '202407' => [48, 16, 22, 24],     // Juli 2024
                '202408' => [81, 12, 11, 21],     // Agustus 2024
                '202409' => [53, 10, 10, 21],     // September 2024
                '202410' => [63, 11, 13, 23],     // Oktober 2024
                '202411' => [54, 5, 11, 17],      // November 2024
                '202412' => [47, 15, 7, 15],      // Desember 2024
                '202501' => [56, 8, 8, 20],       // Januari 2025
                '202502' => [65, 4, 6, 11],       // Februari 2025
                '202503' => [85, 16, 15, 27],    // Maret 2025
                '202504' => [46, 16, 8, 20],      // April 2025
                '202505' => [56, 16, 11, 16],     // Mei 2025
                '202506' => [39, 12, 11, 20],     // Juni 2025
                '202507' => [67, 5, 0, 24],       // Juli 2025
            ];

            $productPrices = [
                201 => 45000, // MPX 2
                267 => 70000, // PIKOLI
                268 => 45000, // FEDERAL
                203 => 45000, // YAMALUBE
            ];

            foreach ($salesData as $period => $quantities) {
                $year = substr($period, 0, 4);
                $month = substr($period, 4, 2);
                $date = Carbon::create($year, $month)->endOfMonth();
                $periodFormatted = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $invoiceNumber = 'INV-' . $date->format('Ymd') . rand(100000, 999999);

                $totalPrice = 0;
                $productData = [201, 267, 268, 203];
                foreach ($productData as $index => $productId) {
                    $totalPrice += $quantities[$index] * $productPrices[$productId];
                }

                // Insert ke tabel sales
                $saleId = DB::table('sales')->insertGetId([
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => 'Customer Lama',
                    'total_price' => $totalPrice,
                    'user_id' => 1,
                    'date' => $date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert ke sale_details & sales_aggregates
                foreach ($productData as $index => $productId) {
                    $quantity = $quantities[$index];
                    $price = $productPrices[$productId];
                    $subtotal = $quantity * $price;

                    DB::table('sale_details')->insert([
                        'sale_id' => $saleId,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('sales_aggregates')->updateOrInsert(
                        [
                            'product_id' => $productId,
                            'period' => $periodFormatted,
                        ],
                        [
                            'total_sales' => $quantity,
                            'total_price' => $subtotal,
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error seeding sales data: " . $e->getMessage();
        }
    }
    }

