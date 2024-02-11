<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerController extends Model
{
    use HasFactory;
    protected $fillable=[
        'pos_id',
        'created_by_id',
        'category_id',
        'customerName',
        'marital_status',
        'other_contact',
        'adress',
        'phone',
        'mail',
        'employer',
        'type',
        'sex',
        'enterprise_id',
        'uuid'  
    ];
}
