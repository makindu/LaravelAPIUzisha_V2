<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class department extends Model
{
    use HasFactory;
    
    protected $table ='departments';

    protected $fillable = [
        'department_name',
        'description',
        'header_depart',
        'user_id',
        'enterprise_id'
    ];

    // public function users(){
    //     return $this->hasMany(User::class);
    // }
    public function createdby(){
        return $this->belongsTo(user::class);
    }

    public function requests(){
        return $this->hasMany(requests::class);
    }
}
