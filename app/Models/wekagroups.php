<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wekagroups extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description',
        'avatar',
        'done_by', 
        'enterprise_id'
    ];
}
