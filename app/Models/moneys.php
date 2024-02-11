<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class moneys extends Model
{
    use HasFactory;
    protected $fillable = [
        'abreviation',
        'principal',
        'money_name',
        'enterprise_id'
    ];
}
