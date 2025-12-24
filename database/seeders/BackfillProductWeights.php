<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackfillProductWeights extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapping = [
            'Joran Pancing Fiberglass 6 Feet' => 450,
            'Joran Pancing Karbon 7 Feet Premium' => 350,
            'Reel Pancing Manual Stainless Steel' => 400,
            'Reel Elektrik Portable 12V' => 1500,
            'Kail Pancing Size 1 per Pack 50pcs' => 120,
            'Kail Pancing Size 3 per Pack 50pcs' => 140,
            'Umpan Buatan Artificial Shrimp' => 80,
            'Pelet Umpan Ikan Premium 1 KG' => 1000,
            'Jaket Pemancing Waterproof' => 800,
            'Rompi Pelampung Life Jacket' => 600,
            'Tas Pancing Multi-Pocket Besar' => 700,
            'Headlamp LED 3W Tahan Lama' => 150,
        ];

        foreach ($mapping as $name => $weight) {
            DB::table('products')
                ->where('nama', $name)
                ->update(['berat' => $weight]);
        }

        // As a safety, set any remaining null/zero berat to a reasonable default (100g)
        DB::table('products')
            ->whereNull('berat')
            ->orWhere('berat', 0)
            ->update(['berat' => 100]);
    }
}
