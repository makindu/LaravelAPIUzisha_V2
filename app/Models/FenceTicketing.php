<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FenceTicketing extends Model
{
    use HasFactory;
    protected $fillable =[
        'fence_id',
        'money_id',
        'user_id',
        'amount',
        'ticketing'
    ];
}
