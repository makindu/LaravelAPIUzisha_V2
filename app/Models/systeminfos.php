<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class systeminfos extends Model
{
    use HasFactory;
    protected $fillable = [
        'names',
        'sigle',
        'phone',
        'email',
    ];
}

