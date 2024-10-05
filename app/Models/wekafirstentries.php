<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wekafirstentries extends Model
{
    use HasFactory;
    protected $fillable=[
        'amount',
        'description',
        'done_by_id',
        'member_id',
        'collector_id',
        'money_id',
        'enterprise_id',
        'done_at',
        'sync_status',
        'uuid',
        'cashed',
        'cashed_by',
        'cashed_at',
        'fund',
    ];
}
