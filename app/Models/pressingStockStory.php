<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pressingStockStory extends Model
{
    use HasFactory;
    protected $fillable=[
        'deposit_id',
        'service_id',
        'done_by',
        'customer_id',
        'invoice_id',
        'detail_invoice_id',
        'quantity',
        'price',
        'total',
        'sold',
        'note',
        'type',
        'status',
        'uuid',
        'enterprise_id',
        'done_at',
    ];
}
