<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class requestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fund_id',
        'amount',
        'motif',
        'type',
        'request_id',
        'fence_id',
        'invoice_id',
        'enterprise_id'
    ];
}
