<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class positions extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description',
        'salary_percentage',
        'salary_amount',
        'method_of_calculation',
        'done_by',
        'enterprise_id',
        'participation_rate'
    ];
}
