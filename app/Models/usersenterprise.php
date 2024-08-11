<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class usersenterprise extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'enterprise_id',
    ];
}
