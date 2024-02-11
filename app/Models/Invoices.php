<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    use HasFactory;
    protected $fillable=[
        'edited_by_id',
        'customer_id',
        'total',
        'total_ht',
        'totalespeces',
        'totalcreditcard',
        'totalmobilemoney',
        'money_id',
        'type_facture',
        'amount_paid',
        'payment_mode',
        'ref_payment',
        'discount',
        'vat_percent',
        'vat_amount',
        'back',
        'is_validate_discount',
        'enterprise_id',
        'note',
        'servant_id',
        'table_id',
        'sync_status',
        'status',
        'uuid',
        'total_received',
        'netToPay',
        'date_operation'       
    ];
}
