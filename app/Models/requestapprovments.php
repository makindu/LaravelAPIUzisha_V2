<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class requestapprovments extends Model
{
    use HasFactory;
    protected $fillable=[
        'deposit_sender_id',
        'deposit_receiver_id',
        'quantity_sent',
        'quantity_received',
        'note',
        'comment',
        'reference',
        'sender_id',
        'receiver_id',
        'enterprise_id',
        'status',
        'service_id'
    ];
}
