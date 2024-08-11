<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class enterprisesinvoices extends Model
{
    use HasFactory;

    protected $fillable=[
        'enterprise_id',
        'status',
        'user_id',
        'from',
        'to',
        'type',
        'amount_due',
        'payed',
        'uuid',
        'nbrmonth',
        'unite_price',
        'nbrpersons'
    ];
}
