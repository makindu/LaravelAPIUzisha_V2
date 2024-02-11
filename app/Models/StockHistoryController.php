<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistoryController extends Model
{
    use HasFactory;
    protected $fillable =[
        'depot_id',	
        'service_id',
        'user_id',
        'provider_id',
        'invoice_id',
        'quantity',
        'quantity_before',
        'price',
        'total',
        'expiration_date',
        'document_type',
        'document_name',
        'document_number',
        'attachment',
        'motif',
        'code_bar',
        'note',
        'type',
        'type_approvement',
        'status',
        'uuid',
        'enterprise_id' , 
        'quantity_used',
        'price_used',        
        'operation_used',
        'date_operation',
        'palette',
        'profit',
        'method_used'
    ];
}
