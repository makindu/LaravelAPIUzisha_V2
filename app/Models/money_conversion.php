<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class money_conversion extends Model
{
    use HasFactory;
    protected $fillable = [
        'money_id1',
        'money_id2',
        'rate',
        'operator',
        'enterprise_id',
        'user_id'
    ];
}
