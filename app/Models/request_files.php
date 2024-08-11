<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class request_files extends Model
{
    use HasFactory;
    protected $fillable = [
        'filename',
        'extension',
        'description',
        'request_id'
    ];
}
