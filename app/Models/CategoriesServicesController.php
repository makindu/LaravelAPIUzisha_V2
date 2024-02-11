<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriesServicesController extends Model
{
    use HasFactory;
    protected $fillable=[
        'parent_id',
        'name',
        'user_id',
        'description',
        'type_conservation',
        'has_vat',
        'enterprise_id'
    ];
}
