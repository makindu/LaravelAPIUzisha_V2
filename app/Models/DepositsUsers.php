<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositsUsers extends Model
{
    use HasFactory;
    protected $fillable=[
        'deposit_id',
        'user_id',
        'level'
    ];
}
