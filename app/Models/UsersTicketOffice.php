<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersTicketOffice extends Model
{
    use HasFactory;
    protected $fillable=[
        'tickeoffice_id',
        'user_id',
        'done_by'
    ];
}
