<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriesCustomerController extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'description',
        'discount_applicable',
        'enterprise_id',
        'user_id',
        'parent_id',
        'discount_applicable'
    ];
}
