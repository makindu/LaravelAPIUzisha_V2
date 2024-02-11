<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicesController extends Model
{
    use HasFactory;

    protected $fillable =[
       'uom_id',	
       'user_id',
       'category_id',
       'name',
       'description',
       'type',
       'codebar',
       'code_manuel',
       'photo',
       'point',
       'nbrgros',
       'bonus_applicable',
       'has_vat',
       'enterprise_id'	
    ];
}
