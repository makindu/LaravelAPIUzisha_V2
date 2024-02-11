<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class details_request extends Model
{
    use HasFactory;
    protected $fillable = [
        'qte',
        'detail_description',
        'pu',
        'tot',
        'request_id'
    ];
}
