<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailsInvoicesStatus extends Model
{
    use HasFactory;
    protected $fillable=[
        'detail_id',
        'status_id',
        'from',
        'enterprise_id',
        'to',
        'user_id'
    ];
}
