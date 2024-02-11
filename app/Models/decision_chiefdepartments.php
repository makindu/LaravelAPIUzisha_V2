<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class decision_chiefdepartments extends Model
{
    use HasFactory;
    protected $fillable = [
        'response',
        'user_id',
        'request_id'
    ];
}
