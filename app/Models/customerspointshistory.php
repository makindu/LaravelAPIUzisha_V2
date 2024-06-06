<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customerspointshistory extends Model
{
    use HasFactory;
    protected $fillable=[
        'customer_id',
        'invoice_id',
        'service_id',
        'quantity',
        'point',
        'type',
        'value',
        'used',
        'done_at'
    ];
}
