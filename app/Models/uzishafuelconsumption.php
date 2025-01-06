<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class uzishafuelconsumption extends Model
{
    use HasFactory;
    protected $fillable=[
        'quantity',
        'price',
        'pump',
        'num',
        'station',
        'done_at',
        'enterprise_id'
    ];
}
