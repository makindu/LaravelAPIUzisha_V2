<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoicesdetailsmaterials extends Model
{
    use HasFactory;
    protected $fillable=[
        'detail_id',
        'material_id',
        'quantity',
        'observation'
    ];
}
