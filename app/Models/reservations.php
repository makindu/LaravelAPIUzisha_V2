<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reservations extends Model
{
    use HasFactory;
    protected $fillable=[
        'service_id',
        'from',
        'to',
        'price',
        'note',
        'status',
        'user_id',
        'customer_id',
        'enterprise_id',
        'done_at',
        'nbr_days',
        'total',
        'caution'
    ];
}
