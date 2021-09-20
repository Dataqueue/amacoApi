<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class party_division extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function party()
    {
        return $this->belongsTo(party::class);
    }
    
    
}
