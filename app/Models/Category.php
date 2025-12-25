<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['nama', 'slug'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->nama);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('nama') && !$category->isDirty('slug')) {
                $category->slug = static::generateUniqueSlug($category->nama, $category->id);
            }
        });
    }

    public static function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        $query = static::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count++;
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    public function products()
    {
        return $this->hasMany(Products::class);
    }

    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    public function hasProducts()
    {
        return $this->products()->exists();
    }
}

