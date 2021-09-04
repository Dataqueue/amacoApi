<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDivision extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;
    public function user()
    {
        return $this->hasMany('App\Models\User');
    }

    public function division()
    {
        return $this->hasMany('App\Models\division');
    }
}
