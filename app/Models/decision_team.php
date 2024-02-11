<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class decision_team extends Model
{
    use HasFactory;

    protected $fillable = [
        'access',
        'user_id',
        'enterprise_id'
    ];
    
    public function user(){

        return $this->belongsTo(User::class);
    }
}
