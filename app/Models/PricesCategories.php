<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricesCategories extends Model
{
    use HasFactory;
    protected $fillable=[
        'service_id',
        'label',
        'price',
        'money_id',
        'principal'
    ];
}
