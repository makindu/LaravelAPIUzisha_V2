<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class licences extends Model
{
    use HasFactory;
    protected $fillable=[
        'vehicule_id',			
        'updated_by',
        'uuid',
        'status',
        'to',
        'from',
        'created_by_id',
        'enterprise_id'
    ];
}
