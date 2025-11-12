<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'total_harga',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(\App\Models\Order_items::class, 'order_id');
    }
}
