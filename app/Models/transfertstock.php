<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transfertstock extends Model
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
        'service_id',
        'validate_by',
        'validate',
        'validate_at',
        'received_at',
        'uuid',
    ];
}
