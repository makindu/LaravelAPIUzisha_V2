<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class owners extends Model
{
    use HasFactory;

    protected $fillable=[
       'name',
       'mail',
       'email_verified_at',
       'phone',
       'password',
       'avatar'
    ];
}
