<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debts extends Model
{
    use HasFactory;
    protected $fillable=[
        'created_by_id',
        'customer_id',
        'invoice_id',
        'status',
        'amount',
        'sold',
        'maturity',
        'uuid',
        'sync_status'
    ];
}
