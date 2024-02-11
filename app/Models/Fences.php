<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fences extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'amount_due',
        'amount_paid',
        'money_id',
        'totalsell',
        'totalcash',
        'totalcredits',
        'totalbonus',
        'totalcautions',
        'totaldebts',
        'depositcautions',
        'totalexpenditures',
        'totalentries',
        'sold',
        'uuid',
        'sync_status',
        'validated',
        'date_concerned',
        'enterprise_id'
    ];
}
