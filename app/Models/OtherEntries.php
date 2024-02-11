<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherEntries extends Model
{
    use HasFactory;
    protected $fillable=[
        'pos_id',
        'user_id',
        'money_id',
        'amount',
        'origin',
        'motif',
        'account_id',
        'is_validate',
        'uuid',
        'sync_status',
        'enterprise_id'
    ];
}
