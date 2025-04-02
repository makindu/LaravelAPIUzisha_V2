<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class plans extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description',
        'slug',
        'features',
        'billing_cycle',
        'color',
        'price',
        'support_type',
        'currency',
        'tax_rate',
        'stripe_price_id',
        'status',
        'is_popular',
        'others',
    ];

    protected $casts=[
        'features'=>'array',
        'status'=>'boolean',
        'is_popular'=>'boolean',
    ];
}
