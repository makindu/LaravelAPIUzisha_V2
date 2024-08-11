<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoicesStatus extends Model
{
    use HasFactory;
    protected $fillable=[
        'invoice_id',
        'status_id',
        'from',
        'enterprise_id',
        'to',
        'user_id'
    ];
}
