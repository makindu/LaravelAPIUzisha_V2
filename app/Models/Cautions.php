<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cautions extends Model
{
    use HasFactory;
    protected $fillable=[
        'customer_id',
        'user_id',
        'amount',
        'money_id',
        'amount_used',
        'enterprise_id',
        'uuid',
        'sync_status'
    ];
}
