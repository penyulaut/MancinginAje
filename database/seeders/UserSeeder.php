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
        // Create Admin User
        User::create([
            'name' => 'Admin MancinginAje',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'address' => 'Jl. Admin No. 1, Jakarta',
            'phone' => '081234567890',
        ]);

        // Create Customer User
        User::create([
            'name' => 'Customer MancinginAje',
            'email' => 'customer@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'address' => 'Jl. Customer No. 1, Jakarta',
            'phone' => '081234567891',
        ]);
    }
}