<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoicesdetailsSpots extends Model
{
    use HasFactory;
    protected $fillable=[
        'detail_id',
        'spot_id',
        'quantity',
        'observation'
    ];
}
