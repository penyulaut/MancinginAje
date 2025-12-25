<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRateCache extends Model
{
    protected $table = 'shipping_rate_caches';

    protected $fillable = [
        'district_id',
        'courier',
        'weight_bucket',
        'data',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];
}
