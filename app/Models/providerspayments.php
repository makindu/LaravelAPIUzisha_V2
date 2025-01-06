<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class providerspayments extends Model
{
    use HasFactory;
    protected $fillable=[
       'done_by',
       'provider_id',
       'stock_history_id',
       'enterprise_id',
       'status',
       'note',
       'amount',
       'uuid',
       'done_at'
    ];
}
