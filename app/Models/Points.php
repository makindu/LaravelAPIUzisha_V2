<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Points extends Model
{
    use HasFactory;
    protected $fillable=[
        'customer_id',
        'service_id',
        'amount',
        'amount_used',
        'rate',
        'nb_sales',
        'uuid',
        'sync_status',
        'money_id',
        'enterprise_id',
        'user_id'
    ];
}
