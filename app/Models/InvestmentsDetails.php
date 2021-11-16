<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentsDetails extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(Users::class);
    }
    public function paymentAccount()
    {
        return $this->belongsTo(paymentAccount::class);
    }
    

}
