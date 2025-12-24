<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id', 'label', 'address_line',
        'province_id','province_name','city_id','city_name','district_id','district_name','postal_code','is_default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
