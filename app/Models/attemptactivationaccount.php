<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class attemptactivationaccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'code',
        'user_id'
    ];
}
