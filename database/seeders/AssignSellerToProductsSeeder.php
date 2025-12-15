<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Products;
use App\Models\User;
use Illuminate\Support\Str;

class AssignSellerToProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find any existing seller user
        $seller = User::where('role', 'seller')->first();

        if (!$seller) {
            // create a default seller
            $seller = User::create([
                'name' => 'Default Seller',
                'email' => 'seller@example.com',
                'password' => bcrypt('password'),
                'role' => 'seller',
            ]);
        }

        // Assign seller_id to products that don't have it
        Products::whereNull('seller_id')->orWhere('seller_id', 0)->chunkById(100, function($products) use ($seller) {
            foreach ($products as $p) {
                $p->seller_id = $seller->id;
                $p->save();
            }
        });
    }
}
