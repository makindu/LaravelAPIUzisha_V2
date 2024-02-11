<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenditures extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'money_id',
        'ticket_office_id',
        'amount',
        'motif',
        'account_id',
        'is_validate',
        'uuid',
        'sync_status',
        'enterprise_id'
    ];
}
