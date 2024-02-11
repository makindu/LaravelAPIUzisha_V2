<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositServices extends Model
{
    use HasFactory;
    protected $fillable=[
        'deposit_id',
        'service_id',
        'available_qte'
    ];
}
