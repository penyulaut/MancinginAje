<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'nama' => 'Joran',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Reel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Kail',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Umpan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pakaian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Aksesoris',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
