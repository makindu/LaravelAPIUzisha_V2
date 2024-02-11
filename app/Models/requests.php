<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class requests extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'type',
        'description',
        'total',
        'request_money',
        'rate',
        'converted_amount',
        'status',
        'user_id',
        'department_id',
        'conversion_id',
        'validatechiefdepart',
        'validatedecisionteam',
        'enterprise_id'
    ];
}
