<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class self_references extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference_text',
        'request_id'
    ];
}
