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
        // Create or update Admin User (idempotent)
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin MancinginAje',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'address' => 'Jl. Admin No. 1, Jakarta',
                'phone' => '081234567890',
            ]
        );

        // Create or update Customer User (idempotent)
        User::updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Customer MancinginAje',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                // set address to an example located in Jakarta Pusat (compatible with RajaOngkir destinations)
                'address' => 'Jl. Medan Merdeka Barat No.1, Gambir, Jakarta Pusat, DKI Jakarta 10110',
                'phone' => '081234567891',
            ]
        );
    }
}