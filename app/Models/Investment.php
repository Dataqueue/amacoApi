<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function PaymentAccount()
    {
        return $this->belongTo(PaymentAccount::class);
    }
    
}
