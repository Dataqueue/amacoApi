<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
   
    
    public function division()
    {
        return $this->hasMany('App\Models\division');
    }
    public function users()
    {
        return $this->belongsTo(UserDivision::class);
    }
    public function investment()
    {
        return $this->hasOne(Investment::class);
    }
    public function PaymentAccount()
    {
        return $this->hasOne(PaymentAccount::class,'user_id','id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function userProfile()
    {
        $path = $this->profile;
        if (File::exists(public_path($this->profile))) {
            return url($path);
        }
        return "No file Uploaded";

    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function userDivision()
    {
        return $this->belongsTo(userDivisions::class);
    }

}
