<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codeVerification','email', 'password','api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function etudiant(){
        return $this->hasOne(Etudiant::class);
    }

    public function professeur(){
        return $this->hasOne(Professeur::class);
    }

    public function admin(){
        return $this->hasOne(Admin::class);
    }

    public function notifications(){
        return $this->hasMany(Notification::class);
    }
}
