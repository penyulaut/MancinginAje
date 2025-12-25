<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products extends Model
{
    // public $guarded = [];

    protected $table = 'products';
    protected $fillable = ['nama', 'deskripsi', 'harga', 'stok', 'gambar','category_id', 'seller_id', 'berat'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function seller()
    {
        return $this->belongsTo(\App\Models\User::class, 'seller_id');
    }
}
