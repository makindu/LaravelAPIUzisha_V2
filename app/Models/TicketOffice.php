<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketOffice extends Model
{
    use HasFactory;
    protected $fillable=[
       'pos_id',
       'user_id',
       'created_by_id',
       'name',
       'description',
       'available_amount',
       'sale_type',
       'enterprise_id'
    ];
}
