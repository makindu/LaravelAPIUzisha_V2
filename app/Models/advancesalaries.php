<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class advancesalaries extends Model
{
    use HasFactory;
    protected $fillable=[
        'amount',
        'description',
        'agent_id',
        'done_by_id',  
        'enterprise_id',
        'money_id',
        'done_at',
        'uuid',
        'status'
    ];
}
