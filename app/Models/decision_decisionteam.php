<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class decision_decisionteam extends Model
{
    use HasFactory;
    protected $fillable = [
        'response',
        'user_id',
        'request_id'
    ];
    
    public function user(){

        return $this->belongsTo(User::class);
    }
}
