<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'type',	
        'description',
        'uuid',
        'sync_status',
        'user_id',
        'enterprise_id'
    ];
}
