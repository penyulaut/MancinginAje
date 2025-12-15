<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeederSample extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            // Joran (Category ID 1)
            [
                'nama' => 'Joran Pancing Fiberglass 6 Feet',
                'deskripsi' => 'Joran berkualitas tinggi dengan material fiberglass yang tahan lama dan kokoh. Cocok untuk pemula hingga menengah.',
                'harga' => 250000,
                'stok' => 15,
                'gambar' => 'https://via.placeholder.com/300x300?text=Joran+6ft',
                'category_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Joran Pancing Karbon 7 Feet Premium',
                'deskripsi' => 'Joran premium dengan material karbon berkualitas tinggi untuk performa maksimal. Sangat ringan dan fleksibel.',
                'harga' => 450000,
                'stok' => 8,
                'gambar' => 'https://via.placeholder.com/300x300?text=Joran+7ft',
                'category_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Reel (Category ID 2)
            [
                'nama' => 'Reel Pancing Manual Stainless Steel',
                'deskripsi' => 'Reel berkualitas dengan material stainless steel yang anti karat. Cocok untuk pemancing pemula.',
                'harga' => 150000,
                'stok' => 20,
                'gambar' => 'https://via.placeholder.com/300x300?text=Reel+Manual',
                'category_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Reel Elektrik Portable 12V',
                'deskripsi' => 'Reel elektrik yang menggunakan tenaga baterai 12V. Memudahkan pemancingan untuk ikan besar.',
                'harga' => 850000,
                'stok' => 5,
                'gambar' => 'https://via.placeholder.com/300x300?text=Reel+Elektrik',
                'category_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Kail (Category ID 3)
            [
                'nama' => 'Kail Pancing Size 1 per Pack 50pcs',
                'deskripsi' => 'Kail berkualitas tinggi, tahan lama, dan tajam. Cocok untuk berbagai jenis ikan.',
                'harga' => 45000,
                'stok' => 50,
                'gambar' => 'https://via.placeholder.com/300x300?text=Kail+Size+1',
                'category_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Kail Pancing Size 3 per Pack 50pcs',
                'deskripsi' => 'Kail dengan ukuran lebih besar untuk menangkap ikan yang lebih besar.',
                'harga' => 55000,
                'stok' => 40,
                'gambar' => 'https://via.placeholder.com/300x300?text=Kail+Size+3',
                'category_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Umpan (Category ID 4)
            [
                'nama' => 'Umpan Buatan Artificial Shrimp',
                'deskripsi' => 'Umpan buatan berbentuk udang yang realistis. Efektif untuk berbagai jenis ikan predator.',
                'harga' => 35000,
                'stok' => 30,
                'gambar' => 'https://via.placeholder.com/300x300?text=Umpan+Shrimp',
                'category_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Pelet Umpan Ikan Premium 1 KG',
                'deskripsi' => 'Pelet umpan premium dengan nutrisi lengkap untuk menarik berbagai jenis ikan.',
                'harga' => 80000,
                'stok' => 25,
                'gambar' => 'https://via.placeholder.com/300x300?text=Pelet+Umpan',
                'category_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Pakaian (Category ID 5)
            [
                'nama' => 'Jaket Pemancing Waterproof',
                'deskripsi' => 'Jaket tahan air dengan material berkualitas tinggi. Nyaman dipakai untuk aktivitas memancing.',
                'harga' => 350000,
                'stok' => 12,
                'gambar' => 'https://via.placeholder.com/300x300?text=Jaket+Pemancing',
                'category_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Rompi Pelampung Life Jacket',
                'deskripsi' => 'Rompi keselamatan berkualitas untuk aktivitas memancing di air yang dalam.',
                'harga' => 250000,
                'stok' => 18,
                'gambar' => 'https://via.placeholder.com/300x300?text=Rompi+Pelampung',
                'category_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Aksesoris (Category ID 6)
            [
                'nama' => 'Tas Pancing Multi-Pocket Besar',
                'deskripsi' => 'Tas dengan banyak kantong untuk menyimpan perlengkapan pancing dengan rapi dan aman.',
                'harga' => 180000,
                'stok' => 22,
                'gambar' => 'https://via.placeholder.com/300x300?text=Tas+Pancing',
                'category_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Headlamp LED 3W Tahan Lama',
                'deskripsi' => 'Lampu kepala LED untuk memancing di malam hari. Tahan air dan baterai awet.',
                'harga' => 95000,
                'stok' => 35,
                'gambar' => 'https://via.placeholder.com/300x300?text=Headlamp+LED',
                'category_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
