<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetails extends Model
{
    use HasFactory;
    protected $fillable=[
        'service_id',
        'invoice_id',
        'deposit_id',
        'pos_id',
        'quantity',
        'price',
        'money_id',
        'total',
        'uuid',
        // 'description',
        'sync_status'
    ];
}
