<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class posdeposits extends Model
{
    use HasFactory;
    protected $fillable=[
        'deposit_id',
        'user_id',
        'pos_id'
    ];
}
