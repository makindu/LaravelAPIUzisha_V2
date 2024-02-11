<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderController extends Model
{
    use HasFactory;
    protected $fillable=[
        'created_by_id',
        'providerName',
        'adress',
        'phone',
        'photo',
        'type',
        'mail',
        'enterprise_id'
    ];
}
