<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtPayments extends Model
{
    use HasFactory;
    protected $fillable=[
        'done_by_id',
        'debt_id',
        'amount_payed',
        'uuid',
        'sync_status'
    ];
}
