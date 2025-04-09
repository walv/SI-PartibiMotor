<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'username' => 'admin',
            'email' => 'admin@partibimotor.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Cashier user
        User::create([
            'username' => 'kasir',
            'email' => 'kasir@partibimotor.com',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir',
        ]);
    }
}
