<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDivision extends Model
{
    use HasFactory;
    protected $guarded = [];
   
    public function user()
    {
        return $this->hasMany('App\Models\User');
    }
    
    public function userdivision()
    {
        return $this->hasMany(UserDivision::class,'id','u_id');
    }
    

    public function division()
    {
        return $this->hasMany('App\Models\division');
    }
    public function div_category()
    {
        return $this->belongsTo(Division::class);
    }
}
