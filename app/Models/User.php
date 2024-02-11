<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use Laravel\Sanctum\HasApiTokens;
//use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use  HasFactory, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_name',
        'user_mail',
        'email_verified_at',
        'user_phone',
        'user_password',
        'user_type',
        'status',
        'permissions',
        'note',
        'avatar',
        'full_name'
    ];

    public function requests(){
        return $this->hasMany(requests::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    //     'email_verified_at',
    //     'created_at',
    //     'updated_at',
    //     'laravel_through_key',
    // ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
