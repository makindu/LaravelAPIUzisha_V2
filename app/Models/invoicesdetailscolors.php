<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoicesdetailscolors extends Model
{
    use HasFactory;
    protected $fillable=[
        'detail_id',
        'color_id',
        'quantity',
        'observation'
    ];
}
