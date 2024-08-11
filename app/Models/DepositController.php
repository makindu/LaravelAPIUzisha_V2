<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositController extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'name',
        'description',
        'type',
        'withdrawing_method',
        'enterprise_id'
    ];
}
