<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class passwordreset extends Model
{
    use HasFactory;
    protected $table="resetpassword";

    protected $fillable=[
        'email',
        'token',
        'created_at',
    ];
}
