<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class libraries extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description',
        'done_at',
        'size',
        'uuid',
        'type',
        'path',
        'enterprise_id',
        'user_id',
        'extension',
        'visibility'
    ];
}
