<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class images extends Model
{
    use HasFactory;
    protected $fillable=[
        'image',
        'description',
        'service_id',
        'user_id',
        'servant_id',
        'provider_id',
    ];
}
