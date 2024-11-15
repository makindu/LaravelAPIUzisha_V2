<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wekagroupmembers extends Model
{
    use HasFactory;
    protected $fillable=[
       'level',
       'done_by_id',
       'member_id',
       'group_id'
    ];
}
