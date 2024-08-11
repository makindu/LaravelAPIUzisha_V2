<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class expendituresLimits extends Model
{
    use HasFactory;
    protected $fillable=[
        'minimum',
        'maximum',
        'money_id',
        'description',
        'enterprise_id',
        'user_id'
    ];
}
