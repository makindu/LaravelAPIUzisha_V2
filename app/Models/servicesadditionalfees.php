<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class servicesadditionalfees extends Model
{
    use HasFactory;
    protected $fillable=[
       'name',
       'service_id',
        'price',
       'tax_exempt',
        'calculation',
       'user_id',
       'enterprise_id'
    ];
}
