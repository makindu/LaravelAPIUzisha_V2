<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class servants extends Model
{
    use HasFactory;
    protected $fillable=[
       'name',
       'description',
       'photo',
       'phone',
       'email',
       'address',
       'user_id',
       'enterprise_id', 
    ];
}
