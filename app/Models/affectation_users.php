<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class affectation_users extends Model
{
    use HasFactory;
    protected $fillable = [
        'level',
        'user_id',
        'department_id'
    ];
}
