<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class colors extends Model
{
    use HasFactory;
   protected $fillable=[
    'name',
    'code',
    'showonquickbar',
    'user_id',
    'enterprise_id'
   ];
}
