<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class request_served extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'served_by',
        'rate',
        'request_id',
        'money_id',
        'fund_id',
        'motif'
    ];
}
