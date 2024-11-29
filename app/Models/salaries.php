<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class salaries extends Model
{
    use HasFactory;
    protected $fillable =[
       'position_id',
       'agent_id',
       'affected_by', 
       'enterprise_id',
       'description'
    ];
}
