<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detailinvoicesubservices extends Model
{
    use HasFactory;

    protected $fillable=[
        'service_id',
        'detail_invoice_id',
        'invoice_id',
        'quantity',
        'price',
        'total',
        'note'
    ];
}
