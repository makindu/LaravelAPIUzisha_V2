<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wekaAccountsTransactions extends Model
{
    use HasFactory;
    protected $fillable=[
    'amount',
    'sold_before',
    'sold_after',
    'type',
    'motif',
    'user_id',
    'member_account_id',
    'member_id',
    'enterprise_id',
    'done_at',
    'account_id',
    'operation_done_by',
    'uuid',
    'fees',
    'transaction_status',
    'sync_status',
    'phone',
    'adresse'
    ];
}
