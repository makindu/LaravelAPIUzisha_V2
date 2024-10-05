<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wekamemberaccounts extends Model
{
    use HasFactory;
    protected $fillable=[
        'sold',
        'description',
        'type',
        'account_status',
        'money_id',
        'user_id',
        'account_number',
        'enterprise_id',
    ];
}
