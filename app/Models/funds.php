<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class funds extends Model
{
    use HasFactory;
    protected $fillable = [
        'sold',
        'description',
        'money_id',
        'user_id',
        'enterprise_id'
    ];
}
