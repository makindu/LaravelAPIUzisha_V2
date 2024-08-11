<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersExpendituresLimits extends Model
{
    use HasFactory;
    protected $fillable=[
        'limit_id',    
        'user_id'
    ];
}
