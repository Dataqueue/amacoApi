<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'payment_accounts';

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
    public function advanceAccount()
    {
        return $this->belongsTo(AdvancePayment::class);
    }
    public function users_payment()
    {
        return $this->belongsTo(User::class);
    }
    public function investment()
    {
        return $this->hasOne(Investment::class,'payment_account_id','id');
    }
    public function investment_details()
    {
        return $this->hasOne(InvestmentsDetails::class,'payment_account_id','payment_account_id');
    }
}
