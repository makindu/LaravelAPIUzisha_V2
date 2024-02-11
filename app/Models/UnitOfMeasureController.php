<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasureController extends Model
{
    use HasFactory;
    
    protected $fillable=[
        'name',
        'symbol',
        'enterprise_id'
    ];
}
