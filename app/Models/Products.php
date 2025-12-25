<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = ['nama', 'deskripsi', 'harga', 'stok', 'gambar', 'category_id', 'seller_id', 'berat', 'status', 'images'];

    protected $casts = [
        'images' => 'array',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function seller()
    {
        return $this->belongsTo(\App\Models\User::class, 'seller_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stok', '<', $threshold)->where('stok', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stok', '<=', 0);
    }

    public function getAllImages()
    {
        $images = [];
        if ($this->gambar) {
            $images[] = $this->gambar;
        }
        if ($this->images && is_array($this->images)) {
            $images = array_merge($images, $this->images);
        }
        return $images;
    }
}

